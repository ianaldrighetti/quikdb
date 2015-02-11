<?php
namespace QuikDb;
use QuikDb\Exception\FileSystemException;
use QuikDb\Exception\QuikDbException;

/**
 * Class QuikDb
 *
 * This is the central class used for interacting with a QuikDb database. A database represents a directory, which
 * contains all the table data.
 *
 * @package QuikDb
 */
class QuikDb
{
    /**
     * The directory that this database represents.
     *
     * @var string $directory
     */
    private $directory;

    /**
     * @param string|null $directory
     * @throws QuikDbException
     */
    public function __construct($directory = null)
    {
        $this->directory = null;

        if (!is_null($directory))
        {
            $this->open($directory);
        }
    }

    /**
     * Opens an existing database.
     *
     * @param string $directory The entire path to the database, this can include the ".qdb" extension in the directory
     *                          name, but is not necessary.
     * @return bool
     * @throws QuikDbException
     */
    public function open($directory)
    {
        $database = basename($directory);

        if (substr($database, -4, 4) == ".qdb")
        {
            $database = substr($database, 0, strlen($database) - 4);
        }

        if (!Util::isNameAllowed($database))
        {
            throw new QuikDbException("The database name is not allowed: ". $database);
        }

        $path = realpath(dirname($directory). DIRECTORY_SEPARATOR. $database. ".qdb");

        if ($path === false)
        {
            throw new QuikDbException("The database directory does not exist: ". $directory);
        }

        // Nothing else to do.
        $this->directory = $path;

        return true;
    }

    /**
     * @param string $directory
     * @return bool
     * @throws QuikDbException
     */
    public static function exists($directory)
    {
        $database = basename($directory);

        if (substr($database, -4, 4) == ".qdb")
        {
            $database = substr($database, 0, strlen($database) - 4);
        }

        if (!Util::isNameAllowed($database))
        {
            throw new QuikDbException("The database name is not allowed: ". $database);
        }

        return realpath(dirname($directory). DIRECTORY_SEPARATOR. $database. ".qdb") !== false;
    }

    /**
     * Creates a new QuikDb within the specified directory.
     *
     * @param string $directory The path to store the database, this must not exist already.
     * @return QuikDb
     * @throws FileSystemException Thrown when a file access error occurs, such as not being able to create the
     *                             directory.
     * @throws QuikDbException Thrown if the database name is invalid.
     */
    public static function create($directory)
    {
        $dirname = dirname($directory);
        $database = basename($directory);

        // If the directory it is contained in doesn't exist, create it.
        if (!file_exists($dirname) && !@mkdir($dirname))
        {
            throw new FileSystemException("The directory did not exist and could not be created: ". $dirname);
        }
        // If it does exist, that's no good.
        else if (file_exists($dirname. DIRECTORY_SEPARATOR. $database. ".qdb"))
        {
            throw new FileSystemException("The database already exists: ". $directory. DIRECTORY_SEPARATOR. $directory. ".qdb");
        }

        // Make sure the name conforms to our requirements.
        if (!Util::isNameAllowed($database))
        {
            throw new QuikDbException("The database name is not allowed: ". $database);
        }

        // It doesn't exist, which is required, so we must create it.
        if (!@mkdir($dirname. DIRECTORY_SEPARATOR. $database. ".qdb"))
        {
            throw new FileSystemException("The database could not be created: ". $directory. DIRECTORY_SEPARATOR. $directory. ".qdb");
        }

        return new QuikDb($dirname. DIRECTORY_SEPARATOR. $database);
    }

    /**
     * Returns an instance of QuikTable, for dealing with tables.
     *
     * @return QuikTable
     */
    public function table()
    {
        return new QuikTable($this);
    }

    public function root()
    {
        return $this->directory;
    }
}