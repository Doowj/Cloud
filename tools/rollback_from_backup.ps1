# Rollback script for ecommerce-main
# Usage: Run in PowerShell (Administrator not required). It will list available backups under tools\backup,
# let you pick one, then restore files from that backup into the project root (overwriting existing files).

$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Definition
$backupRoot = Join-Path $scriptDir 'backup'

if (-Not (Test-Path $backupRoot)) {
    Write-Error "No backup directory found at $backupRoot"
    exit 1
}

$backups = Get-ChildItem -Path $backupRoot -Directory | Sort-Object Name -Descending
if ($backups.Count -eq 0) {
    Write-Error "No backup subdirectories found under $backupRoot"
    exit 1
}

Write-Host "Available backups:" -ForegroundColor Cyan
for ($i = 0; $i -lt $backups.Count; $i++) {
    Write-Host "[$i] $($backups[$i].Name)  -  $($backups[$i].FullName)"
}

$choice = Read-Host "Enter the index of the backup to restore (default 0 = latest)"
if ([string]::IsNullOrWhiteSpace($choice)) { $choice = '0' }
if (-Not ($choice -as [int]) -or [int]$choice -lt 0 -or [int]$choice -ge $backups.Count) {
    Write-Host "Invalid choice: $choice" -ForegroundColor Red
    exit 1
}

$selected = $backups[[int]$choice]
Write-Host "Selected backup: $($selected.Name)" -ForegroundColor Green

$confirm = Read-Host "This will overwrite files in the project root with files from the backup. Type RESTORE to proceed"
if ($confirm -ne 'RESTORE') {
    Write-Host "Aborted by user." -ForegroundColor Yellow
    exit 0
}

# Perform copy
$files = Get-ChildItem -Path $selected.FullName -Recurse -File
$total = $files.Count
$copied = 0

foreach ($f in $files) {
    $relPath = $f.FullName.Substring($selected.FullName.Length).TrimStart('\')
    $destination = Join-Path $scriptDir $relPath
    $destDir = Split-Path -Parent $destination
    if (-Not (Test-Path $destDir)) {
        New-Item -ItemType Directory -Path $destDir -Force | Out-Null
    }
    Copy-Item -Path $f.FullName -Destination $destination -Force
    $copied++
}

Write-Host "Restored $copied files from backup '$($selected.Name)'" -ForegroundColor Green
Write-Host "Done." -ForegroundColor Cyan
