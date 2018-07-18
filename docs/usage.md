# Usage

You can write your persistence model classes by hand, but it's going to be
tedious to do so. Instead, use the `./vendor/bin/atlas-skeleton` command to read
the table information from the database and generate them for you.

## Configure Connection

First, create a PHP file to return an array of configuration parameters for
skeleton generation. Provide an array of PDO connection arguments, a string for
the namespace prefix, and a directory to write the classes to:

```php
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

## Generate Classes

You can then invoke the skeleton generator using that config file:

```bash
php ./vendor/bin/atlas-skeleton.php /path/to/skeleton-config.php
```

Doing so will read every table in the database and create one DataSource
directory for each of them, each with several classes:

```
App
└── DataSource
    └── Thread
        ├── Thread.php                  # mapper
        ├── ThreadEvents.php            # mapper-level events
        ├── ThreadFields.php            # trait with property names
        ├── ThreadRecord.php            # single record
        ├── ThreadRecordSet.php         # record collection
        ├── ThreadRelationships.php     # relationship definitions
        ├── ThreadRow.php               # table row
        ├── ThreadSelect.php            # mapper-level query object
        ├── ThreadTable.php             # table defintion and interactions
        ├── ThreadTableEvents.php       # table-level events
        ├── ThreadTableSelect.php       # table-level query object
```

Most of these classes will be empty, and are provided so you can extend their
behavior if you wish. They also serve to assist IDEs with autocompletion of
return typehints.

> **Warning:**
>
> If you run the skeleton generator more than once, the following classes will
> be OVERWRITTEN and you will lose any changes to them:
>
> - {TYPE}Fields.php
> - {TYPE}Row.php
> - {TYPE}Table.php
>
> The remaining classes will remain untouched.

## Custom Transformations

If you are unsatisfied with how the skeleton generator transforms table names to
persistence model type names, you can instantiate the `Transform` class in the
config file under the `transform` key, and pass an array of table-to-type names
to override the default transformations:

```php
// /path/to/skeleton-config.php
return [
    'pdo' => [
        'mysql:dbname=testdb;host=localhost',
        'username',
        'password',
    ],
    'namespace' => 'App\\DataSource',
    'directory' => './src/App/DataSource',
    'transform' => new \Atlas\Cli\Transform([
        'table_name' => 'TypeName',
        // use a value of null to skip the table entirely
    ]);
];
```

Alternatively, provide a callable (or callable instance) of your own:

```php
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
