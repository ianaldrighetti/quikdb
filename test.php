<?php
namespace Test;

// Just some informal tests.
require(dirname(__FILE__). '/vendor/autoload.php');

use QuikDb\QuikDb;

date_default_timezone_set('America/Los_Angeles');

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!defined('PHP_ENDL'))
{
    define ('PHP_ENDL', "\r\n");
}

define ('DB_NAME', 'test');

if (!QuikDb::exists(DB_NAME))
{
    $created = QuikDb::create(DB_NAME);

    echo 'Database Created? '. ($created ? 'Yes' : 'No'), PHP_ENDL;
}
else
{
    echo 'Database already exists...', PHP_ENDL;
}

$columns = array(
    'id' => array(
        'type' => 'INT',
        'size' => 8,
        'nullable' => false,
        'default' => 0,
        'auto_increment' => true,
    ),
    'name' => array(
        'type' => 'VARCHAR',
        'size' => 40,
        'nullable' => false,
        'default' => null,

    ),
);

$db = new QuikDb(DB_NAME);
try
{

    $db->table()->createTable('test', $columns);
    echo 'Table should have been created.', PHP_ENDL;
}
catch(\Exception $e)
{
    echo 'Uh oh: '. $e->getMessage();
}

echo 'Table structure:', PHP_ENDL;
print_r($db->table()->getTableStructure('test'));