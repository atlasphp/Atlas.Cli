# Atlas.Cli 1.x

This is the command-line interface package for Atlas. It is intended for use
in your development environments, not your production ones.

## Installation

This package is installable and autoloadable via
[Composer](https://getcomposer.org/) as
[atlas/cli](https://packagist.org/packages/atlas/cli).

Add it to the `require-dev` section of your root-level `composer.json`
to install the `atlas-skeleton` command-line tool.

```json
{
    "require-dev": {
        "atlas/cli": "~1.0"
    }
}
```

## Creating Skeleton Classes

You can create your data source classes by hand, but it's going to be tedious to
do so. Instead, use the `atlas-skeleton` command to read the table information
from the database.

Create a PHP file to return an array of connection parameters suitable for PDO:

```php
<?php
// /path/to/conn.php
return [
    'mysql:dbname=testdb;host=localhost',
    'username',
    'password'
];
?>
```

You can then invoke the skeleton generator using that connection. Specify a
target directory for the skeleton files, and pass the namespace name for the
data source classes. You can pass an explicit table name to keep the generator
from trying to guess the name.

```bash
./vendor/bin/atlas-skeleton.php \
    --conn=/path/to/conn.php \
    --dir=src/App/DataSource \
    --table=threads \
    App\\DataSource\\Thread
```

> N.b.: The backslashes (`\`) at the end of the lines are to allow the command
> to be split across multiple lines in Unix. If you are on Windows, omit the
> trailing backslashes and enter the command on a single line.

That will create this directory and two classes in `src/App/DataSource/`:

```
└── Thread
    ├── ThreadMapper.php
    └── ThreadTable.php
```

The Mapper class will be empty, and the Table class will a description of the
specified `--table`. Note that you should not make changes to the table class,
as they will be overwritten if you regenerate the skeleton.

Do that once for each SQL table in your database.

If you pass `--full` to `atlas-skeleton`, it will additionally generate empty
`MapperEvents`, `Record`, `RecordSet`, and `TableEvents` classes. (These are
useful only if you want to add custom behaviors, and will not be overwritten
if you regenerate the skeleton.)

The `--full` option will also add a `Fields` class with `@property` annotations
for table columns on the Record. This file will be overwritten if the table
class is regenerated, as it is defined from the table columns (which may have
changed since the last skeleton generation).
