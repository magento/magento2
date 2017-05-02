<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Model\Import\Source\FileParser;

use Magento\Framework\Filesystem;

class ZipParserFactory implements ParserFactoryInterface
{
    private $filesystem;

    public function __construct(Filesystem $filesystem, ParserFactoryInterface $factory)
    {
        $this->filesystem = $filesystem;
    }

    public function create($path, array $options = [])
    {
        if (substr($path, -4) !== '.zip') {
            throw new UnsupportedPathException($path);
        }

        throw new CorruptedFileException($path);
    }
}
