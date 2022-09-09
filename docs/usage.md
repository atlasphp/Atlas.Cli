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

> **Tip:**
>
> If you happen to have a generic config file for other purposes, you can nest
> the Atlas configuration values inside that array. For example:
>
> ```php
> // /path/to/settings.php
> return [
>     'foo' => [
>         'bar' => [
>             'atlas' => [
>                 'pdo' => [
>                     'mysql:dbname=testdb;host=localhost',
>                     'username',
>                     'password',
>                 ],
>                 'namespace' => 'App\\DataSource',
>                 'directory' => './src/App/DataSource',
>             ],
>         ],
>     ],
> ];
> ```

## Generate Classes

You can then invoke the skeleton generator using that config file:

```bash
php ./vendor/bin/atlas-skeleton.php /path/to/skeleton-config.php
```

> **Tip:**
>
> If you nested the Atlas keys inside the config file, pass the dot-separated
> names of the array elements leading to the Atlas configuration array as an
> argument immediately after the file path.
>
> For example, given the above array of `['foo']['bar']['atlas']`:
>
> ```bash
> php ./vendor/bin/atlas-skeleton.php /path/to/settings.php foo.bar.atlas
> ```

Doing so will read every table in the database and create one DataSource
directory for each of them, each with several top-level and  classes:

```
App
└── DataSource
    └── Thread                          # concrete classes
        ├── Thread.php                  # mapper
        ├── ThreadEvents.php            # mapper events
        ├── ThreadRecord.php            # mapper record
        ├── ThreadRecordSet.php         # mapper recordset
        ├── ThreadRelated.php           # related records and recordsets
        ├── ThreadRow.php               # table row
        ├── ThreadSelect.php            # mapper select
        ├── ThreadTable.php             # table
        ├── ThreadTableEvents.php       # table events
        ├── ThreadTableSelect.php       # table select
        └── _                           # abstract classes
            ├── ThreadEvents_.php
            ├── ThreadRecordSet_.php
            ├── ThreadRecord_.php
            ├── ThreadRelated_.php
            ├── ThreadRow_.php
            ├── ThreadSelect_.php
            ├── ThreadTableEvents_.php
            ├── ThreadTableSelect_.php
            ├── ThreadTable_.php
            └── Thread_.php
```

All of the concrete classes will be empty, and extend their abstract classes
from the `_` directory. Edit these top-level classes as you see fit; they will
never be overwritten by the skeleton generator.

However, the `_/*_.php` generated abstract classes *will* be overwritten each
time you run the skeleton generator. This helps with keeping the classes
up-to-date with the database schema.


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

## Custom Templates

You can override the templates used by the skeleton generator and provide your
own instead. This lets you customize the code generation; for example, to add
your own common methods or to extend intercessory classes.

First, take a look at the default templates in the Atlas.Skeleton
`resources/templates/` directory. For each persistence model type name, the
word "Type" in the filename will be replaced with the type; `.tpl` infix will
be removed. For example, a `threads` table will become a `Thread` type, so the
resulting files will be `Thread.php`, `ThreadEvents.php`, and so on.

To override a default template, create a custom template file of the same name
in a directory of your own choosing. Then, in the skeleton config file, set
a 'templates' key to that directory:

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
    'templates' => '/path/to/custom-templates-dir'
];
```

When you run the skeleton command, it will look there first for each template,
and then use the default template only if there is not a custom one available.

The skeleton command provides these variables for templates to use:

- `$COLUMNS` => The table column descriptions.
- `$DRIVER` => The database driver type.
- `$NAMESPACE` => The namespace value from the config file.
- `$RELATED` => The related Record and RecordSet property names and types.
- `$SEQUENCE` => The name of the auotincrement sequence, if any.
- `$TABLE` => The table name.
- `$TYPE` => The persistence model type.

The templates are written in plain PHP, minus the opening PHP tags themselves.
