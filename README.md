Quik DB
======

This is an exploratory project written in PHP, it's goal is to create a simple database. This database will be structured like any other, with tables, rows and columns. The implementation details are laid out below.

Implementation
=====

### Database

Every database is simply a directory, which may reside anywhere on the file system.

The name of the database must contain only alphanumeric (a-z, A-Z, 0-9) characters, including underscores (_). However, database names may not start with a number. This database directory name must end in ".qdb" which indicates it is a Quik DB -- but this is only at the implementation level, as in when specifying the database to query the ".qdb" must not be included.

Each database includes any number of tables, limited only by the amount of files a directory may contain on a system-per-system basis (which is pretty large in most cases).

### Tables

Each table comprises of of at least two files: a table structure file and a table data file.

Table names are under the same restriction as database names. They can be alphanumeric (a-z, A-Z, 0-9), including underscores (_) and cannot start with a number. As with the database extension, the extensions are hidden at the query level.

##### Table Structure

The table structure file has the same name as the table itself, but ends with an extension of ".qts".

The structure of the file looks like so:
```
[column count][column 1 name][column 1 type][column 1 size][column 1 nullable][column 1 default][column 1 auto increment][...]
```

`[column count]` This indicates the number of columns within the table, which is limited to 2<sup>16</sup> -1 (65,535, unsigned short) columns.

All of the following are repeated as many times as there is columns:

`[column 1 name]` The name of the column, which has two pieces: the length (unsigned byte [unsigned char used in PHP]) and then the name of the column. The names are under the same restrictions as the databases and tables: alphanumeric, including underscores but cannot start with a number.

`[column 1 type]` The type is indicated by 3 characters, types available are laid out below.

`[column 1 size]` The size of the type, if the type does not support a size this will always be 0. The maximum size is 2<sup>16</sup> -1 (65,535, unsigned short).

`[column 1 nullable]` Whether the column is nullable, which is a byte indicating 1 for nullable, 0 for not.

`[column 1 default]` The default value of the column, which has two pieces: a length (unsigned short, 65,535 max) and then the default value itself.

`[column 1 auto increment]` A single byte indicating whether this column is auto incremented (1 for true, 0 for false). Each table can only have one column that is auto incremented. No index has to be defined for the column to be auto incremented. More information about implementation is available below.

###### Column Types

**Numbers**

Types: unsigned/signed short, unsigned/signed integer, unsigned/signed longs.

**Text**

Types: varchar(<LENGTH>), text, mediumtext, largetext.

###### Auto Increment Columns
