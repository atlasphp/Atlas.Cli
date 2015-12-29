<?php
namespace Atlas\Cli\Skeleton;

use Atlas\Cli\Fsio;
use Aura\Cli\Context;
use Aura\Cli\Status;
use Aura\Cli\Stdio;
use Aura\SqlSchema\ColumnFactory;
use Exception;
use PDO;

/**
 *
 * Description:
 *
 *  Creates skeleton data-source classes.
 *
 * Usage:
 *
 *  atlas-skeleton.php <type-namespace>
 *
 * Options:
 *
 *  --dir=<value>
 *      Write files to this directory instead of the current one.
 *
 *  --full
 *      Additionally create Record, RecordSet, and Plugin classes.
 *
 *  --conn=<value>
 *      Connect to the database and create, or overwrite, a Table class.
 *      Must also pass a --table value.
 *
 *  --table=<value>
 *      Use the specified table name instead of determining from the type name.
 *      Must also pass a --conn value.
 *
 */
class SkeletonCommand
{
    protected $context;
    protected $stdio;
    protected $fsio;
    protected $getopt;
    protected $factory;

    public function __construct(
        Context $context,
        Stdio $stdio,
        Fsio $fsio,
        SkeletonFactory $factory
    ) {
        $this->context= $context;
        $this->stdio = $stdio;
        $this->fsio = $fsio;
        $this->factory = $factory;
    }

    public function __invoke()
    {
        $methods = [
            'setGetopt',
            'setinput',
            'runSkeleton'
        ];

        foreach ($methods as $method) {
            $exit = $this->$method();
            if ($exit) {
                return $exit;
            }
        }

        $this->stdio->outln('Done!');
        return Status::SUCCESS;
    }

    protected function setGetopt()
    {
        $options = [
            'dir:',
            'full',
            'conn:',
            'table:',
        ];
        $this->getopt = $this->context->getopt($options);

        if (! $this->getopt->hasErrors()) {
            return;
        }

        $errors = $this->getopt->getErrors();
        foreach ($errors as $error) {
            $this->stdio->errln($error->getMessage());
        }

        return Status::USAGE;
    }

    protected function setInput()
    {
        $this->input = $this->factory->newSkeletonInput();
        $this->input->dir = $this->getopt->get('--dir', $this->fsio->getCwd());
        $this->input->full = $this->getopt->get('--full', false);
        $this->input->namespace = $this->getopt->get(1);
        $this->input->conn = $this->getConn();
        $this->input->table = $this->getopt->get('--table');
    }

    protected function getConn()
    {
        $file = $this->getopt->get('--conn');
        if (! $file) {
            return;
        }

        if (! $this->fsio->isFile($file)) {
            $this->stdio->errln("Connection config file '$file' does not exist or is not readable.");
            return Status::NOINPUT;
        }

        $require = function ($file) { return require $file; };
        return $require($file);
    }

    protected function runSkeleton()
    {
        $skeleton = $this->factory->newSkeleton();
        return $skeleton($this->input);
    }
}
