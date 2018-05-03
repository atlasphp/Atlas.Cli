# Atlas.Cli 2.x

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
        "atlas/cli": "~2.0"
    }
}
```

## Generating Skeleton Classes

You can write your persistence model classes by hand, but it's going to be
tedious to do so. Instead, use the `atlas-skeleton` command to read the table
information from the database and generate them for you.

First, create a PHP file to return an array of configuration parameters for
skeleton generation. Provide an array of PDO connection arguments, a string for
the namespace prefix, and a directory to write the classes to:

```php
<?php
// /path/to/skeleton-config.php
return [
    'pdo' => [
        'mysql:dbname=testdb;host=localhost',
        'username',
        'password',
    ],
    'namespace' => 'App\\DataSource',
    'directory' => './src/App/DataSource',
];
```

You can then invoke the skeleton generator using that config file.

```bash
php ./vendor/bin/atlas-skeleton.php /path/to/skeleton-config.php
```

That will read every table in the database and create one DataSource directory
for each of them, each with several classes:

```
└── Thread
    ├── Thread.php                  # App\DataSource\Thread\Thread
    ├── ThreadEvents.php            # App\DataSource\Thread\ThreadEvents
    ├── ThreadFields.php            # App\DataSource\Thread\ThreadFields
    ├── ThreadRecord.php            # App\DataSource\Thread\ThreadRecord
    ├── ThreadRecordSet.php         # App\DataSource\Thread\ThreadRecordSet
    ├── ThreadRelationships.php     # App\DataSource\Thread\ThreadRelationships
    ├── ThreadRow.php               # App\DataSource\Thread\ThreadRow
    ├── ThreadSelect.php            # App\DataSource\Thread\ThreadSelect
    ├── ThreadTable.php             # App\DataSource\Thread\ThreadTable
    ├── ThreadTableEvents.php       # App\DataSource\Thread\ThreadTableEvents
    ├── ThreadTableSelect.php       # App\DataSource\Thread\ThreadTableSelect
```

Most of these classes will be empty, and are provided so you can extend their
behavior if you wish. They also serve to assist IDEs with autocompletion of
return typehints.

The following classes will be overwritten if you run the skeleton generator
again:

- {TYPE}Fields.php
- {TYPE}Row.php
- {TYPE}Table.php

## Cutom Table Name Transformations

If you are unsatisfied with how the skeleton generator transforms table names to
persistence model type names, provide a callable of your own in the config file
under the key 'transform':

```php
<?php
// /path/to/skeleton-config.php
return [
    'pdo' => [
        'mysql:dbname=testdb;host=localhost',
        'username',
        'password',
    ],
    'namespace' => 'App\\DataSource',
    'directory' => './src/App/DataSource',
    'transform' => function (string $table) : ?string {
        // return the $table name after transforming it into
        // a persistence model type name, or return null to
        // skip the table entirely
    },
];
```

(Cf. the _Transform_ class for example behaviors.)
