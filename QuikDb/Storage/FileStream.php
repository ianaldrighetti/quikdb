<?php
namespace QuikDb\Storage;

use QuikDb\Exception;
use QuikDb\Exception\StreamAlreadyOpenException;
use QuikDb\Exception\StreamIsReadOnlyException;
use QuikDb\Exception\StreamNotOpenException;

/**
 * Class FileStream
 *
 * A base class for the <\QuikDb\Storage\DataWriter> and <\QuikDb\Storage\DataReader> classes. This is pretty much
 * just a wrapper for simple file management.
 *
 * @package QuikDb\Storage
 */
class FileStream
{
    /**
     * The file pointer.
     * @var resource|null $fp
     */
    private $fp;

    /**
     * The mode this file stream was opened with.
     * @var bool $readonly
     */
    private $readonly;

    /**
     * Opens a file stream to the specified file, if any.
     *
     * @param string|null $filename
     * @param bool $readonly
     * @throws Exception\QuikDbException
     */
    public function __construct($filename = null, $readonly = false)
    {
        $this->fp = null;
        $this->readonly = false;

        if (!is_null($filename))
        {
            $opened = $this->open($filename);

            if (!$opened)
            {
                throw new Exception\QuikDbException("I/O error, could not open: ". $filename);
            }
        }
    }

    /**
     * Opens the stream to the specified file. An appropriate lock is obtained on the file as well.
     *
     * @param string $filename The name of the file.
     * @param bool $readonly Whether to open the file in read-only mode, otherwise it is read and write.
     * @return bool
     */
    protected function open($filename, $readonly = false)
    {
        if (!is_null($this->fp))
        {
            throw new StreamAlreadyOpenException();
        }

        if (!file_exists($filename) && empty($readonly))
        {
            $this->fp = @fopen($filename, 'w+');
        }
        else
        {
            $this->fp = @fopen($filename, !empty($readonly) ? 'r' : 'r+');
        }

        if (empty($this->fp))
        {
            return false;
        }

        if (!flock($this->fp, !empty($readonly) ? LOCK_SH : LOCK_EX))
        {
            fclose($this->fp);
            $this->fp = null;

            return false;
        }

        $this->readonly = !empty($readonly);

        return true;
    }

    /**
     * Closes the currently open stream.
     *
     * @return bool
     */
    public function close()
    {
        if (is_null($this->fp))
        {
            throw new StreamNotOpenException();
        }

        if (!$this->readonly)
        {
            fflush($this->fp);
        }

        if (!flock($this->fp, LOCK_UN))
        {
            return false;
        }

        fclose($this->fp);

        $this->fp = null;
        $this->readonly = false;

        return true;
    }

    /**
     * Writes data to the stream.
     *
     * @param mixed $data The data to write to the stream.
     * @return int The number of bytes written to the stream, or false on failure.
     */
    protected function write($data)
    {
        if (is_null($this->fp))
        {
            throw new StreamNotOpenException();
        }
        else if ($this->readonly)
        {
            throw new StreamIsReadOnlyException();
        }

        return fwrite($this->fp, $data);
    }

    /**
     * Reads the specified amount of bytes from the file stream.
     *
     * @param int $length
     * @return string
     */
    protected function read($length)
    {
        if (is_null($this->fp))
        {
            throw new StreamNotOpenException();
        }

        return fread($this->fp, (int) $length);
    }

    /**
     * Advances the position of the pointer.
     *
     * @param int $offset The offset, in bytes.
     * @return bool
     */
    protected function advance($offset)
    {
        if (is_null($this->fp))
        {
            throw new StreamNotOpenException();
        }

        return fseek($this->fp, (int) $offset, SEEK_CUR) === 0;
    }

    /**
     * Moves the position of the pointer in the file to the specified offset.
     *
     * @param int $offset The offset, in bytes.
     * @return bool
     */
    protected function move($offset)
    {
        if (is_null($this->fp))
        {
            throw new StreamNotOpenException();
        }

        return fseek($this->fp, (int) $offset, SEEK_SET) === 0;
    }

    /**
     * Flushes all contents of the buffer to the file. This is automatically called by the close method.
     *
     * @return bool
     */
    protected function flush()
    {
        if (is_null($this->fp))
        {
            throw new StreamNotOpenException();
        }
        else if ($this->readonly)
        {
            throw new StreamIsReadOnlyException();
        }

        return fflush($this->fp);
    }
}