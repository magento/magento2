<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Model\Import\Source\FileParser;

class CorruptedFileException extends \RuntimeException
{
    private $fileName;

    public function __construct($fileName = '', $message = '')
    {
        $this->fileName = $fileName;
        parent::__construct($message ?: sprintf('File "%s" is corrupted', $fileName));
    }


    public function getFileName()
    {
        return $this->fileName;
    }
}
