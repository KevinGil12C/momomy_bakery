<?php

namespace App\Models;

use PDO;
use PDOException;

require_once __DIR__ . '/../config/database.php';

class Database
{
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;

    private $dbh;
    private $stmt;
    private $error;

    public function __construct()
    {
        // Establecer DSN
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname;
        $options = array(
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        );

        // Crear instancia de PDO
        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            echo $this->error;
        }
    }

    // Preparar sentencia con la consulta
    public function query($sql)
    {
        $this->stmt = $this->dbh->prepare($sql);
    }

    // Vincular valores
    public function bind($param, $value, $type = null)
    {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    // Ejecutar la sentencia preparada
    public function execute()
    {
        return $this->stmt->execute();
    }

    // Obtener el conjunto de resultados como un array de objetos
    public function resultSet()
    {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Obtener un solo registro como objeto
    public function single()
    {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_OBJ);
    }

    // Obtener el conteo de filas del último statement
    public function rowCount()
    {
        return $this->stmt ? $this->stmt->rowCount() : 0;
    }

    /**
     * Obtener el total de filas de una tabla
     */
    public function countAll($table)
    {
        $this->query("SELECT COUNT(*) as total FROM {$table}");
        $row = $this->single();
        return $row ? (int)$row->total : 0;
    }

    // Obtener el último ID insertado
    public function lastInsertId()
    {
        return $this->dbh->lastInsertId();
    }

    public function insert($table, $data)
    {
        $keys = array_keys($data);
        $fields = implode(', ', $keys);
        $placeholders = ':' . implode(', :', $keys);

        $sql = "INSERT INTO {$table} ({$fields}) VALUES ({$placeholders})";

        $this->query($sql);

        foreach ($data as $key => $value) {
            $this->bind(':' . $key, $value);
        }

        return $this->execute();
    }

    public function update($table, $data, $where)
    {
        $setParts = [];
        foreach ($data as $key => $value) {
            $setParts[] = "{$key} = :val_{$key}";
        }
        $setStr = implode(', ', $setParts);

        $whereParts = [];
        foreach ($where as $key => $value) {
            $cleanKey = str_replace([' ', '!', '=', '<', '>', '.'], '', $key);
            if (strpos($key, ' ') !== false) {
                $whereParts[] = "{$key} :where_{$cleanKey}";
            } else {
                $whereParts[] = "{$key} = :where_{$cleanKey}";
            }
        }
        $whereStr = implode(' AND ', $whereParts);

        $sql = "UPDATE {$table} SET {$setStr} WHERE {$whereStr}";

        $this->query($sql);

        foreach ($data as $key => $value) {
            $this->bind(':val_' . $key, $value);
        }

        foreach ($where as $key => $value) {
            $cleanKey = str_replace([' ', '!', '=', '<', '>', '.'], '', $key);
            $this->bind(':where_' . $cleanKey, $value);
        }

        return $this->execute();
    }

    public function delete($table, $where)
    {
        $whereParts = [];
        foreach ($where as $key => $value) {
            $cleanKey = str_replace([' ', '!', '=', '<', '>', '.'], '', $key);
            if (strpos($key, ' ') !== false) {
                $whereParts[] = "{$key} :where_{$cleanKey}";
            } else {
                $whereParts[] = "{$key} = :where_{$cleanKey}";
            }
        }
        $whereStr = implode(' AND ', $whereParts);

        $sql = "DELETE FROM {$table} WHERE {$whereStr}";

        $this->query($sql);

        foreach ($where as $key => $value) {
            $cleanKey = str_replace([' ', '!', '=', '<', '>', '.'], '', $key);
            $this->bind(':where_' . $cleanKey, $value);
        }

        return $this->execute();
    }

    public function select($table, $where = [], $limit = null)
    {
        $sql = "SELECT * FROM {$table}";

        if (!empty($where)) {
            $whereParts = [];
            foreach ($where as $key => $value) {
                $cleanKey = str_replace([' ', '!', '=', '<', '>', '.'], '', $key);
                if (strpos($key, ' ') !== false) {
                    $whereParts[] = "{$key} :where_{$cleanKey}";
                } else {
                    $whereParts[] = "{$key} = :where_{$cleanKey}";
                }
            }
            $sql .= " WHERE " . implode(' AND ', $whereParts);
        }

        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }

        $this->query($sql);

        foreach ($where as $key => $value) {
            $cleanKey = str_replace([' ', '!', '=', '<', '>', '.'], '', $key);
            $this->bind(':where_' . $cleanKey, $value);
        }

        return $this->resultSet();
    }

    public function getOne($table, $where = [])
    {
        $result = $this->getAll($table, $where, null, 1);
        return $result ? $result[0] : null;
    }

    public function getAll($table, $where = [], $order = null, $limit = null, $offset = null)
    {
        $sql = "SELECT * FROM {$table}";

        if (!empty($where)) {
            $whereParts = [];
            foreach ($where as $key => $value) {
                if (strpos($key, ' ') !== false) {
                    $cleanKey = str_replace([' ', '!', '=', '<', '>'], '', $key);
                    $whereParts[] = "{$key} :where_{$cleanKey}";
                } else {
                    $whereParts[] = "{$key} = :where_{$key}";
                }
            }
            $sql .= " WHERE " . implode(' AND ', $whereParts);
        }

        if ($order) {
            $sql .= " ORDER BY {$order}";
        }

        if ($limit) {
            $sql .= " LIMIT {$limit}";
            if ($offset) {
                $sql .= " OFFSET {$offset}";
            }
        }

        $this->query($sql);

        foreach ($where as $key => $value) {
            $cleanKey = str_replace([' ', '!', '=', '<', '>'], '', $key);
            $this->bind(':where_' . $cleanKey, $value);
        }

        return $this->resultSet();
    }

    public function getJoin($table, $columns, $joins, $where = [], $order = null, $limit = null, $offset = null)
    {
        $sql = "SELECT {$columns} FROM {$table}";

        foreach ($joins as $join) {
            $type = isset($join['type']) ? $join['type'] : 'LEFT';
            $sql .= " {$type} JOIN {$join['table']} ON {$join['on']}";
        }

        if (!empty($where)) {
            $whereParts = [];
            foreach ($where as $key => $value) {
                $cleanKey = str_replace([' ', '!', '=', '<', '>', '.'], '', $key);
                if (strpos($key, ' ') !== false) {
                    $whereParts[] = "{$key} :where_{$cleanKey}";
                } else {
                    $whereParts[] = "{$key} = :where_{$cleanKey}";
                }
            }
            $sql .= " WHERE " . implode(' AND ', $whereParts);
        }

        if ($order) {
            $sql .= " ORDER BY {$order}";
        }

        if ($limit) {
            $sql .= " LIMIT {$limit}";
            if ($offset) {
                $sql .= " OFFSET {$offset}";
            }
        }

        $this->query($sql);

        foreach ($where as $key => $value) {
            $cleanKey = str_replace([' ', '!', '=', '<', '>', '.'], '', $key);
            $this->bind(':where_' . $cleanKey, $value);
        }

        return $this->resultSet();
    }
}
