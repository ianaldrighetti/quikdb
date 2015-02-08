<?php
namespace QuikDb\Storage;
use QuikDb\Util;

/**
 * Class DataWriter
 *
 * Writes data to files. This is an extension of the <FileStream> class, but adds helpful methods on top for writing
 * integers, floats, strings, etc.
 *
 * @package QuikDb\Storage
 */
class DataWriter extends FileStream
{
    /**
     * @param string $filename
     */
    public function __construct($filename)
    {
        $this->open($filename, false);
    }

    /**
     * Flushes the data to the file.
     */
    public function flush()
    {
        parent::flush();
    }

    /**
     * Writes a string to the file stream. This will include the length of the string before the string itself.
     *
     * @param string $str
     */
    public function writeString($str)
    {
        $length = Util::strlen($str);

        $this->writeUBigInt($length);
        $this->write($str);
    }

    /**
     * Writes a signed short to the file stream.
     *
     * @param int $value
     */
    public function writeShort($value)
    {
        $this->write(pack('s', $value));
    }

    /**
     * Writes an unsigned short to the file stream.
     *
     * @param int $value
     */
    public function writeUShort($value)
    {
        $this->write(pack('v', $value));
    }

    /**
     * Writes a signed integer to the file stream.
     *
     * @param int $value
     */
    public function writeInt($value)
    {
        $this->write(pack('l', $value));
    }

    /**
     * Writes an unsigned integer to the file stream.
     *
     * @param int $value
     */
    public function writeUInt($value)
    {
        $this->write(pack('V', $value));
    }

    /**
     * Writes a signed long long to the file stream. However, if the PHP version does not support unsigned long long
     * as a pack formatter (anything below PHP 5.6.3), then the value is written as an signed integer.
     *
     * @param int $value
     */
    public function writeBigInt($value)
    {
        if (version_compare(phpversion(), '5.6.3', '<'))
        {
            $this->writeInt($value);

            return;
        }
        
        $this->write(pack('q', $value));
    }

    /**
     * Writes an unsigned long long to the file stream. However, if the PHP version does not support unsigned long long
     * as a pack formatter (anything below PHP 5.6.3), then the value is written as an unsigned integer.
     *
     * @param int $value
     */
    public function writeUBigInt($value)
    {
        if (version_compare(phpversion(), '5.6.3', '<'))
        {
            $this->writeUInt($value);

            return;
        }

        $this->write(pack('P', $value));
    }

    /**
     * Writes a float to the file stream.
     *
     * @param float $value
     */
    public function writeFloat($value)
    {
        $this->write(pack('f', $value));
    }
}