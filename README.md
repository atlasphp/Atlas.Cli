# Atlas.Cli

This is the command-line interface package for Atlas.  It is for development use.

## Installation

This package is installable and autoloadable via [Composer](https://getcomposer.org/) as [atlas/cli](https://
packagist.org/packages/atlas/cli).
Make sure that you’ve set up your project to [autoload Composer-installed packages](https://getcomposer.org/doc/00-intro.md#autoloading).

## Basic Usage

> This section is sorely incomplete.

### Creating Classes

You can create your data source classes by hand, but it's going to be tedious to do so. Instead, use the skeleton generator command. While you don't need a database connection, it will be convenient to connect to the database and let the generator read from it.

Create a PHP file to return an array of connection parameters suitable for PDO:

```php
<?php
// ./conn.php
return ['mysql:dbname=testdb;host=localhost', 'username', 'password'];
?>
```

You can then invoke the skeleton generator using that connection. Specify a target directory for the skeleton files if you like, and pass the namespace name for the data source classes. Pass an explicit table name to keep the generator from trying to guess the name.

```bash
./bin/atlas-skeleton.php --conn=./conn.php --dir=src/App/DataSource App\\DataSource\\Thread --table=threads
```

That will create this directory and these empty extended classes in `src/App/DataSource/`:

    └── Thread
        ├── ThreadMapper.php
        ├── ThreadRecord.php
        ├── ThreadRecordFactory.php
        ├── ThreadRecordSet.php
        ├── ThreadRelations.php
        ├── ThreadRow.php
        ├── ThreadRowFactory.php
        ├── ThreadRowFilter.php
        ├── ThreadRowIdentity.php
        ├── ThreadRowSet.php
        ├── ThreadTable.php
        └── ThreadTableTrait.php

Do that once for each SQL table in your database.

You can add relationships on a _Record_ by editing its _Relations_ class:

```php
<?php
namespace Atlas\DataSource\Thread;

use App\DataSource\Author\AuthorMapper;
use App\DataSource\Summary\SummaryMapper;
use App\DataSource\Reply\ReplyMapper;
use App\DataSource\Tagging\TaggingMapper;
use App\DataSource\Tag\TagMapper;
use Atlas\Mapper\AbstractRelations;

class ThreadRelations extends AbstractRelations
{
    protected function setRelations()
    {
        $this->manyToOne('author', AuthorMapper::CLASS);
        $this->oneToOne('summary', SummaryMapper::CLASS);
        $this->oneToMany('replies', ReplyMapper::CLASS);
        $this->oneToMany('taggings', TaggingMapper::CLASS);
        $this->manyToMany('tags', TagMapper::CLASS, 'taggings');
    }
}
?>
```
