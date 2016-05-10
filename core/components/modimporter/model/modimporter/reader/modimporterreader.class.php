<?php

interface modImporterReaderInterface
{
    public function read(array $provider, $callback = null);
}

abstract class modImporterReader extends modProcessor implements modImporterReaderInterface
{
    public $modx;
    protected $processor;
    protected $reader;
    protected $initialized = false;

    # public function __construct(& $modx){

    #    $this->modx = & $modx;

    #}

    public function initialize(modProcessor &$processor)
    {
        $this->processor = &$processor;
        $ok = $this->processProperties();

        if ($ok == true) {
            $this->initialized = true;
        }

        return $ok;
    }

    protected function processProperties()
    {
        return !$this->processor->hasErrors();
    }

    public function initialized()
    {
        return $this->initialized;
    }

    public function process()
    {
    }

    # abstract function read;
}
