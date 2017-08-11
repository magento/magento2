<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Model\Import\Source;

use Magento\ImportExport\Model\Import\AbstractSource;

class File extends AbstractSource
{
    private $parser;

    public function __construct(FileParser\ParserInterface $parser)
    {
        $this->parser = $parser;
        parent::__construct($this->parser->getColumnNames());
    }

    protected function _getNextRow()
    {
        return $this->parser->fetchRow();
    }

    public function rewind()
    {
        $this->parser->reset();
        parent::rewind();
    }
}
