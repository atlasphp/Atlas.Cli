# Atlas.Cli

This is the command-line interface package for Atlas.  It is for development use.

## Installation

This package is installable and autoloadable via [Composer](https://getcomposer.org/) as [atlas/cli](https://packagist.org/packages/atlas/cli). Make sure your project it set up to [autoload Composer-installed packages](https://getcomposer.org/doc/00-intro.md#autoloading).


You can add [atlas/cli](https://packagist.org/packages/atlas/cli)
in the `require-dev` section of `composer.json` in the root of project to
install the `atlas-skeleton` command-line tool when installing for development.

```json
{
    "require": {
        "atlas/orm": "@dev"
    },
    "require-dev": {
        "atlas/cli": "@dev"
    }
}
```

Because Atlas itself is still in development, the API is likely to break. You may wish to lock your `composer.json` to the [specific release](https://github.com/atlasphp/Atlas.Cli/releases); for example:

```json
{
    "require": {
        "atlas/orm": "0.3.*@alpha"
    },
    "require-dev": {
        "atlas/cli": "0.3.*@alpha"
    }
}
```

## Basic Usage

> This section is sorely incomplete.

### Creating Classes

You can create your data source classes by hand, but it's going to be tedious to do so. Instead, use the `atlas-skeleton` command to read the table information from the database.

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

You can then invoke the skeleton generator using that connection. Specify a target directory for the skeleton files if you like, and pass the namespace name for the data source classes. Pass an explicit table name to keep the generator from trying to guess the name.

```bash
./vendor/bin/atlas-skeleton.php \
    --conn=/path/to/conn.php \
    --dir=src/App/DataSource \
    --table=threads App\\DataSource\\Thread
```

That will create this directory and two classes in `src/App/DataSource/`:

    └── Thread
        ├── ThreadMapper.php
        └── ThreadTable.php

The Mapper class is empty, and the Table class is a description of the specified `--table`.

Do that once for each SQL table in your database.

You can add relationships by editing the _Mapper_ class:

```php
<?php
namespace App\DataSource\Thread;

use App\DataSource\Author\AuthorMapper;
use App\DataSource\Summary\SummaryMapper;
use App\DataSource\Reply\ReplyMapper;
use App\DataSource\Tagging\TaggingMapper;
use App\DataSource\Tag\TagMapper;
use Atlas\Orm\Mapper\AbstractMapper;

class ThreadMapper extends AbstractMapper
{
    protected function setRelations()
    {
        // aka "belongs to"
        $this->manyToOne('author', AuthorMapper::CLASS);

        // aka "has one"
        $this->oneToOne('summary', SummaryMapper::CLASS);

        // aka "has many"
        $this->oneToMany('replies', ReplyMapper::CLASS);
        $this->oneToMany('taggings', TaggingMapper::CLASS);

        // aks "has many through"
        $this->manyToMany('tags', TagMapper::CLASS, 'taggings');
    }
}
?>
```

If you pass `--full` to `atlas-skeleton`, it will additionally generate empty
`MapperEvents`, `Record`, `RecordSet`, and `TableEvents` classes. (These are
useful only if you want to add custom behaviors.)
