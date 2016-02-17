<?php

namespace League\CLImate\Util\Writer;

interface WriterInterface
{
    /**
     * @param  string $content
     */
    public function write($content);
}
