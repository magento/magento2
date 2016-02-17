<?php

namespace League\CLImate\TerminalObject\Router;

interface RouterInterface
{
    /**
     * @return string
     */
    public function path($class);

    /**
     * @return boolean
     */
    public function exists($class);

    /**
     * @return null|\League\CLImate\TerminalObject\Dynamic
     */
    public function execute($obj);

}
