<?php

namespace League\CLImate\Util\Writer;

class StdOut implements WriterInterface
{
    /**
     * Write the content to the stream
     *
     * @param  string $content
     */
    public function write($content)
    {
        fwrite(STDOUT, $content);
    }

}
