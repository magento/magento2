<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Model\Import\Source\FileParser;

class UnsupportedPathException extends \RuntimeException
{
    private $path;

    public function __construct($path = '', $message = '')
    {
        parent::__construct($message ?: sprintf('Path "%s" is not supported', $path));

        $this->path = $path;
    }

    public function getPath()
    {
        return $this->path;
    }
}
