<?php

namespace League\CLImate\TerminalObject\Router;

use League\CLImate\Util\OutputImporter;

class DynamicRouter extends BaseRouter implements RouterInterface
{
    use OutputImporter;

    /**
     * Get the full path for a dynamic terminal object class
     *
     * @param  string $class
     *
     * @return string
     */
    public function path($class)
    {
        return $this->getPath('Dynamic\\' . $this->shortName($class));
    }

    /**
     * Execute a dynamic terminal object using given arguments
     *
     * @param \League\CLImate\TerminalObject\Dynamic $obj
     *
     * @return \League\CLImate\TerminalObject\Dynamic
     */
    public function execute($obj)
    {
        $obj->output($this->output);

        return $obj;
    }

}
