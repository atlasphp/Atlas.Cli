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
 *      Without --table, will auto-determine the table name from the type name.
 *
 *  --table=<value>
 *      Use the specified table name instead of determining from the type name.
 *      Useful only in conjunction with --conn.
 *
 */
class SkeletonCommand
{
    protected $context;
    protected $stdio;
    protected $fsio;
    protected $getopt;
    protected $input;

    public function __construct(
        Context $context,
        Stdio $stdio,
        Fsio $fsio,
        Skeletoninput $input
    ) {
        $this->context= $context;
        $this->stdio = $stdio;
        $this->fsio = $fsio;
        $this->input = $input;
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

        return STATUS::USAGE;
    }

    protected function setinput()
    {
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
        $skeleton = new Skeleton($this->fsio, $this->stdio);
        return $skeleton($this->input);
    }
}
