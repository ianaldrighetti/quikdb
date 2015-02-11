<?php
namespace QuikDb;

use QuikDb\Exception\QuikDbException;
use QuikDb\Objects\QuikTableStructure;

class QuikTable
{
    public $db;

    public function __construct(QuikDb $db)
    {
        $this->db = $db;
    }

    public function listTables()
    {
        // TODO
        return array();
    }

    public function exists($name)
    {
        // TODO
        return realpath($this->db->root(). DIRECTORY_SEPARATOR. $name. ".qts");
    }

    public function createTable($name, $columns, $indexes = array())
    {
        if (!Util::isNameAllowed($name))
        {
            throw new QuikDbException("The table name is not allowed: ". $name);
        }
        else if ($this->exists($name))
        {
            throw new QuikDbException("The table already exists: ". $name);
        }
        else if (!is_array($columns))
        {
            throw new QuikDbException("The column information was expected to be an array.");
        }
        else if (count($columns) == 0)
        {
            throw new QuikDbException("At least one column is required.");
        }

        $defaults = array(
            'nullable' => true,
            'size' => null,
            'auto_increment' => false,
        );

//name,type, size, nullable, size, auto increment
        $auto_increment = false;
        foreach ($columns as $columnName => $column)
        {
            if (!Util::isNameAllowed($columnName))
            {
                throw new QuikDbException("The column name is not allowed: " . $columnName);
            }
            else if (!array_key_exists('type', $column))
            {
                throw new QuikDbException("The column did not specify a type: ". $columnName);
            }
            // TODO check type/size

            // Make sure everything is defined, if it isn't use the default.
            $column = array_merge($defaults, $column);
            $columns[$columnName] = $column;

            if ($auto_increment && $column['auto_increment'])
            {
                throw new QuikDbException("There can only be one auto increment per table.");
            }

            $auto_increment = !empty($column['auto_increment']);
        }

        // Everything is good. So we can create the table.
        $structure = new QuikTableStructure($this->db->root());
        $structure->setColumns($columns);
        $structure->store($name);
    }

    public function getTableStructure($name)
    {
        if (!$this->exists($name))
        {
            throw new QuikDbException("Table doesn't exist: ". $name);
        }

        $structure = new QuikTableStructure($this->db->root());
        $structure->read($name);

        return $structure->getColumns();
    }
}