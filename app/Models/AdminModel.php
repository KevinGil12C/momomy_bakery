<?php

namespace App\Models;

/**
 * Extended Database Model with count helper
 */
class Database extends \App\Models\Database
{
    /**
     * Helper to count rows in a table
     */
    public function rowCountTable($table, $where = [])
    {
        $sql = "SELECT COUNT(*) as total FROM {$table}";
        if (!empty($where)) {
            $whereParts = [];
            foreach ($where as $key => $value) {
                $whereParts[] = "{$key} = :{$key}";
            }
            $sql .= " WHERE " . implode(' AND ', $whereParts);
        }
        $this->query($sql);
        foreach ($where as $key => $value) {
            $this->bind(":{$key}", $value);
        }
        $result = $this->single();
        return $result ? (int)$result->total : 0;
    }
}
