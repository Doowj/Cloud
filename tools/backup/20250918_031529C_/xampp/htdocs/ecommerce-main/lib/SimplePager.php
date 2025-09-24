<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

class SimplePager {
    public $limit;      // Page size
    public $page;       // Current page
    public $item_count; // Total item count
    public $page_count; // Total page count
    public $result;     // Result set (array of records)
    public $count;      // Item count on the current page

    public function __construct($query, $params, $limit, $page) {
        global $_db;

        // Set [limit] and [page]
        $this->limit = (int)ctype_digit((string)$limit) ? max($limit, 1) : 10;
        $this->page = (int)ctype_digit((string)$page) ? max($page, 1) : 1;

        // Set [item count] - using a custom query for count
        // Only include parameters if they exist
        $countQuery = "SELECT COUNT(o.id) 
                       FROM orders o 
                       INNER JOIN user u ON o.member_id = u.id 
                       LEFT JOIN payment p ON o.id = p.orders_id";

        if (!empty($params)) {
            $countQuery .= " WHERE o.id LIKE :search_query OR u.name LIKE :search_query";
        }

        $stm = $_db->prepare($countQuery);
        
        if (!empty($params)) {
            $stm->execute($params);  // Execute only if params exist
        } else {
            $stm->execute();  // Execute without params for normal count
        }

        $this->item_count = $stm->fetchColumn();

        // Set [page count]
        $this->page_count = ceil($this->item_count / $this->limit);

        // Calculate offset
        $offset = ($this->page - 1) * $this->limit;

        // Set [result] - keeping sorting in the main query
        $stm = $_db->prepare($query . " LIMIT $offset, $this->limit");
        $stm->execute($params);  // Execute with params
        $this->result = $stm->fetchAll();

        // Set [count]
        $this->count = count($this->result);
    }

    public function html($href = '', $attr = '') {
        if (!$this->result) return;

        // Generate pager (html)
        $prev = max($this->page - 1, 1);
        $next = min($this->page + 1, $this->page_count);

        echo "<nav class='pager' $attr>";
        echo "<a href='?page=1&$href'>First</a>";
        echo "<a href='?page=$prev&$href'>Previous</a>";

        for ($p = 1; $p <= $this->page_count; $p++) {
            $c = $p == $this->page ? 'active' : '';
            echo "<a href='?page=$p&$href' class='$c'>$p</a>";
        }

        echo "<a href='?page=$next&$href'>Next</a>";
        echo "<a href='?page=$this->page_count&$href'>Last</a>";
        echo "</nav>";
    }
}
