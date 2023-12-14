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

## Custom Templates

You can override the templates used by the skeleton generator and provide your
own instead. This lets you customize the code generation; for example, to add
your own common methods or to extend intercessory classes.

First, take a look at the default templates in the Atlas.Cli `resources/templates/`
directory:

- Type.tpl
- TypeEvents.tpl
- TypeFields.tpl
- TypeRecord.tpl
- TypeRecordSet.tpl
- TypeRelationships.tpl
- TypeRow.tpl
- TypeSelect.tpl
- TypeTable.tpl
- TypeTableEvents.tpl
- TypeTableSelect.tpl

For each persistence model type name, the word "Type" in the filename will be
replaced with the type; `.tpl` will be replaced with `.php`. For example, a
`threads` table will become a `Thread` type, so the resulting files will be
`Thread.php`, `ThreadEvents.php`, and so on.

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

The skeleton file will replace these tokens in the template file with these
values:

- `{NAMESPACE}` => The namespace value from the config file.
- `{TYPE}` => The persistence model type.
- `{DRIVER}` => The database driver type.
- `{NAME}` => The table name.
- `{COLUMN_NAMES}` => An array of column names from the table.
- `{COLUMN_DEFAULTS}` => An add of column default values from the table.
- `{AUTOINC_COLUMN}` => The name of the autoincrement column, if any.
- `{PRIMARY_KEY}` => An array of primary key column names.
- `{COLUMNS}` => An array of the full column descriptions.
- `{AUTOINC_SEQUENCE}` => The name of the auotincrement sequence, if any.
- `{PROPERTIES}` => A partial docblock of properties for a Row.
- `{FIELDS}` => A partial docblock of field names for a Record.

## Use of PostgreSQL Schemas

The use of a schema other than `public` in a PostgreSQL database can be done via a [custom `PDO` instance](https://atlasphp.io/cassini/pdo/connection.html#2-6-1-2).
The following example sets the schema to `custom_schema`:

```php
$pdo = new PDO(
    'pgsql:dbname=testdb;host=localhost',
    'username',
    'password',
);
$pdo->exec('SET search_path TO custom_schema');

return [
    'pdo'       => [$pdo],
    'namespace' => 'App\\DataSource',
    'directory' => './src/App/DataSource',
];
```

## Generate Only Specific Tables

To generate only a specific table or tables, the [custom transformation](#custom-transformations) can be used.
A callable can be specified that returns the table name with model type name if it should be generated, or `null` if it should be skipped.
The following example generates only the models for the tables `page` and `comments`:

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
    'transform' => static function (string $table) : ?string {
        return [
            // table name => model type name
            'page'     => 'Page',
            'comments' => 'Comment',
        ][$table] ?? null;
    },
];
```
