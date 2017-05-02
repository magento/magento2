<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Test\Unit\Model\Import\Source\FileParser;

use Magento\ImportExport\Model\Import\Source\FileParser\ParserFactoryInterface;
use Magento\ImportExport\Model\Import\Source\FileParser\UnsupportedPathException;

class FakeParserFactory implements ParserFactoryInterface
{
    private $parser;

    public function __construct($parser = [])
    {
        $this->parser = $parser;
    }

    public function create($path, array $options = [])
    {
        if ($this->isParserMap()) {
            return $this->findParserInMap($path);
        }

        return $this->parser;
    }

    private function findParserInMap($path)
    {
        $path = $this->trimTemporaryFilePrefix($path);

        if (!isset($this->parser[$path])) {
            throw new UnsupportedPathException($path);
        }

        return $this->parser[$path];
    }

    private function isParserMap()
    {
        return is_array($this->parser);
    }

    private function trimTemporaryFilePrefix($path)
    {
        if (strpos($path, 'tmp-') === 0) {
            $path = preg_replace('/tmp-[a-z0-9]+-/i', '', $path);
        }

        return $path;
    }
}
