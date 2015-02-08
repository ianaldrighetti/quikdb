<?php
namespace QuikDb\Storage;

use QuikDb\Util;

/**
 * Class DataReader
 *
 * Reads data from a file, with additional helper methods for reading strings, integers, etc.
 *
 * @package QuikDb\Storage
 */
class DataReader extends FileStream
{
    /**
     * @param string $filename
     */
    public function __construct($filename)
    {
        parent::__construct($filename, false);
    }

    /**
     * Reads a string from the file stream. This expects the string is preceded by an integer indicating it's length.
     *
     * @returns string
     */
    public function readString()
    {
        $length = $this->readUBigInt();

        return $this->read($length);
    }

    /**
     * Reads a signed short from the file stream.
     *
     * @returns int
     */
    public function readShort()
    {
        return $this->unpack('s', 2);
    }

    /**
     * Reads an unsigned short from the file stream.
     *
     * @returns int
     */
    public function readUShort()
    {
        return $this->unpack('v', 2);
    }

    /**
     * Reads a signed integer from the file stream.
     *
     * @returns int
     */
    public function readInt()
    {
        return $this->unpack('l', 4);
    }

    /**
     * Reads an unsigned integer from the file stream.
     *
     * @returns int
     */
    public function readUInt()
    {
        return $this->unpack('V', 4);
    }

    /**
     * Reads a signed long long from the file stream. However, if the PHP version does not support unsigned long long
     * as a pack formatter (anything below PHP 5.6.3), then the value is written as an signed integer.
     *
     * @returns int
     */
    public function readBigInt()
    {
        if (version_compare(phpversion(), '5.6.3', '<'))
        {
            return $this->readInt();
        }

        return $this->unpack('q', 8);
    }

    /**
     * Reads an unsigned long long from the file stream. However, if the PHP version does not support unsigned long long
     * as an unpack formatter (anything below PHP 5.6.3), then the value is read as an unsigned integer.
     *
     * @returns int
     */
    public function readUBigInt()
    {
        if (version_compare(phpversion(), '5.6.3', '<'))
        {
            return $this->readUInt();
        }

        return $this->unpack('P', 8);
    }

    /**
     * Reads a float from the file stream.
     *
     * @return float
     */
    public function readFloat()
    {
        return $this->unpack('f', Util::getFloatSize());
    }

    /**
     * @param $format
     * @param $length
     * @return mixed
     */
    private function unpack($format, $length)
    {
        $data = $this->read($length);

        $unpacked = unpack($format. 'data', $data);
        return $unpacked['data'];
    }
}