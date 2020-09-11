<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Model\Config\Structure\Reader;

use Magento\Config\Model\Config\Structure\Converter;

/**
 * Class ConverterStub used for ReaderTest.
 */
class ConverterStub extends Converter
{
    /**
     * Convert dom document wrapper.
     *
     * @param \DOMDocument $document
     * @return array|null
     */
    public function getArrayData(\DOMDocument $document)
    {
        return $this->_convertDOMDocument($document);
    }

    /**
     * Convert dom document.
     *
     * @param \DOMNode $source
     * @return array
     */
    public function convert($source)
    {
        return $this->_convertDOMDocument($source);
    }
}
