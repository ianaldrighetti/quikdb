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

`[column 1 type]` The type is indicated by 5 characters, types available are laid out below.

`[column 1 size]` The size of the type, if the type does not support a size this will always be 0. The maximum size is 2<sup>16</sup> -1 (65,535, unsigned short).

`[column 1 nullable]` Whether the column is nullable, which is a byte indicating 1 for nullable, 0 for not.

`[column 1 default]` The default value of the column, which has two pieces: a length (unsigned short, 65,535 max) and then the default value itself.

`[column 1 auto increment]` A single byte indicating whether this column is auto incremented (1 for true, 0 for false). Each table can only have one column that is auto incremented. No index has to be defined for the column to be auto incremented. More information about implementation is available below.

###### Column Types

**Numbers**

The following integer types are supported:

Type Name | Type Identifier | Min | Max
:--- | :---: | :---: | :---:
`SMALLINT` | `SMINT` | -32,768 | 32,767
`UNSIGNED SMALLINT` | `USINT` | 0 | 65,535
`INTEGER` | `INTGR` | -2,147,483,648 | 2,147,483,647
`UNSIGNED INTEGER` | `UNINT` | 0 | 4,294,967,295
`BIGINT` | `BGINT` | -9,223,372,036,854,775,808 | 9,223,372,036,854,775,807
`UNSIGNED BIGINT` | `UBINT` | 0 | 18,446,744,073,709,551,615
`FLOAT` | `FLOAT` | *see below* | *see below*

All types are stored in a binary format that is system dependent, the only requirement is that the underlying type chosen must support the range specified - however, if the underlying storage type is able to store a larger value than the range for that type it must enforce that range manually.

The same applies to the `FLOAT` type, as the underlying storage used is dependent of implementation. This means the range itself may vary as well.

Because number types are stored in a system dependent manner, the data files may not be transferrable from one system to another due to possible data corruption. This means if data would need to be transferred to another machine, it would be best to export it all as text then import it through the database layer.

**Text**

Type Name | Type Identifier | Max Size (bytes) | Allows Default?
:--- | :---: | :---: | :---:
`VARCHAR(*length*)` | `VCHAR` | 255 | *Yes*
`TEXT` | `RTEXT` | 65,535 | *No*
`MEDIUMTEXT` | `MTEXT` | 16,777,215 | *No*
`LARGETEXT` | `LTEXT` | 4,294,967,295 | *No*

All text types are stored as-is within the file. They are all preceded within the data file with the length of the value.

**Other Types**

QuikDB does not support any other types, such as date/time or booleans. These can be stored in text or integer types anyways.

###### Auto Increment Columns

When a column is defined to auto increment, the value will be retrieved from a file containing the last used value. This value is stored within a file with the name of the table with the extension ".qai".

Note that this value stored is the *last* used value, so once the value is retrieved from the file it is incremented and used for the row then stored into the file.

##### Table Indexes

The table index information is stored within a file with the name of the table with the extension ".qti". This contains information about all the indexes within the table -- but not the indexes themselves.

*This section is not yet complete.*

##### Table Data

All table data is stored within a single file, it has the same name as the table with the extension ".qtd".

The following is an example of a single row within the table data file.

```
[row 1 length][column 1 data][column 2 data]...[column n data]
```

The setup is very simple, each row simply has a row length in front of it (this length excludes the length itself), followed by the column data itself.

`[row 1 length]` As stated above, this is the length of all the row's column data, not including the row length itself. The exact data type used to store this information is not defined strictly, it all depends on each implementation. However, the maximum size this length type can be is the limit on how much each row can store.

`[column 1...n data]` The data stored after depends entirely on the type of each individual column. The column name and type should be retrieved from the table information and not stored here. Some types may require that part of the data include the length (for variable length types, e.g. text), others may not (number types).
