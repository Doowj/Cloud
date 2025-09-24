<?php
// tools/convert_paths.php
// Simple tool to convert common relative paths to more robust absolute paths
// Usage: php tools/convert_paths.php --sample

$root = realpath(__DIR__ . '/..');
$backupDir = __DIR__ . '/backup/' . date('Ymd_His');

// Collect files recursively (php, html, htm, js), excluding backup dir
$filesToProcess = [];
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
foreach ($rii as $file) {
    if ($file->isDir()) continue;
    $path = $file->getPathname();
    // Skip backup directory
    if (strpos($path, DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR) !== false) continue;
    // Skip vendor-like dirs if any
    if (strpos($path, DIRECTORY_SEPARATOR . 'node_modules' . DIRECTORY_SEPARATOR) !== false) continue;
    if (preg_match('/\.(php|html?|js)$/i', $path)) {
        $filesToProcess[] = $path;
    }
}

if (!is_dir(dirname($backupDir))) {
    mkdir(dirname($backupDir), 0755, true);
}

mkdir($backupDir, 0755, true);

function backup($file, $backupDir) {
    $dest = $backupDir . str_replace(':', '_', $file);
    $destDir = dirname($dest);
    if (!is_dir($destDir)) mkdir($destDir, 0755, true);
    copy($file, $dest);
}

function replace_in_php_includes($content) {
    // Replace patterns like: include 'components/_base.php';
    // Strategy: if path starts with '.' or does not start with '/' or 'http', use __DIR__ or DOCUMENT_ROOT

    // No reusable closure needed here — we'll build replacements directly below

    $pattern = '/\b(include|require|include_once|require_once)\s*(\(?)(["\'])([^"\']+)(\3)\s*\)?\s*;/i';

    return preg_replace_callback($pattern, function($m) {
        // $m[1] = func, $m[3] = quote, $m[4] = path
        $func = $m[1];
        $quote = $m[3];
        $path = $m[4];

        // Skip URLs
        if (preg_match('#^https?://#i', $path)) return $m[0];

        // Already absolute server path
        if (str_starts_with($path, '/') || preg_match('#^[A-Za-z]:\\\\#', $path)) return $m[0];

        // If path starts with './' or '../'
        if (str_starts_with($path, './') || str_starts_with($path, '../')) {
            return $func . ' __DIR__ . ' . $quote . '/' . $path . $quote . ';';
        }

        // If path looks like 'components/...'
    return $func . ' $_SERVER[\'DOCUMENT_ROOT\'] . ' . $quote . '/' . $path . $quote . ';';

    }, $content);
}

function replace_in_html_assets($content) {
    // Replace href/src like href="css/style.css" -> href="/css/style.css"
    // Only change if it doesn't start with '/', 'http', or 'data:'
    $content = preg_replace_callback('/(href|src)=("|\')([^"\']+)("|\')/i', function($m){
        $attr = $m[1];
        $quote = $m[2];
        $url = $m[3];
        if (preg_match('#^(https?:)?//#i', $url)) return $m[0];
        if (str_starts_with($url, '/') || str_starts_with($url, 'data:') || str_starts_with($url, 'mailto:')) return $m[0];
        // Convert to root-relative
        return "$attr=$quote/{$url}$quote";
    }, $content);

    return $content;
}

$report = [];
foreach ($filesToProcess as $file) {
    if (!file_exists($file)) {
        $report[] = [ 'file' => $file, 'status' => 'missing'];
        continue;
    }
    backup($file, $backupDir);
    $orig = file_get_contents($file);
    $modified = $orig;

    $modified = replace_in_php_includes($modified);
    $modified = replace_in_html_assets($modified);

    if ($modified !== $orig) {
        file_put_contents($file, $modified);
        $report[] = [ 'file' => $file, 'status' => 'modified'];
    }
    else {
        $report[] = [ 'file' => $file, 'status' => 'unchanged'];
    }
}

echo "Backup directory: $backupDir\n\n";
foreach ($report as $r) {
    echo $r['file'] . ' => ' . $r['status'] . "\n";
}

echo "\nDone.\n";
