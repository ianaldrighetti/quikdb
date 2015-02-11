<?php
namespace QuikDb\Objects;

use QuikDb\Exception\QuikDbException;
use QuikDb\Storage\DataReader;
use QuikDb\Storage\DataWriter;

class QuikTableStructure
{
    /**
     * @var string $root
     */
    public $root;

    /**
     * @var array $columns
     */
    public $columns;

    public function __construct($root)
    {
        $this->root = $root;
        $this->columns = array();
    }

    public function setColumns(array $columns)
    {

    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function read($name)
    {
        $reader = new DataReader($this->root. DIRECTORY_SEPARATOR. $name. ".qts");

        $count = $reader->readUShort();
        $this->columns = array();
        for ($i = 0; $i < $count; $i++)
        {
            $columnName = $reader->readString();

            $this->columns[$columnName] = array(
                'type' => $reader->readString(),
                'size' => $reader->readUShort(),
                'nullable' => $reader->readByte() == 1,
                'default' => $reader->readString(),
                'auto_increment', $reader->readByte() == 1,
            );
        }
    }

    public function store($name)
    {
        if (!is_array($this->columns))
        {
            throw new QuikDbException("The columns set must be an array.");
        }

        // .qts
        $writer = new DataWriter($this->root. DIRECTORY_SEPARATOR. $name. ".qts");

        $writer->writeUShort(count($this->columns));

        foreach ($this->columns as $columnName => $column)
        {
            // TODO validate uniqueness of name
            $writer->writeString($columnName);

            // TODO map type to it's code.
            $writer->writeString($column['type']);
            $writer->writeUShort($column['size']);
            $writer->writeByte((int) $column['nullable']);
            $writer->writeString(array_key_exists('default', $column) ? $column['default'] : '');
            // TODO validate that no others use this
            $writer->writeByte($column['auto_increment']);
        }

        $writer->flush();
        $writer->close();
    }
}