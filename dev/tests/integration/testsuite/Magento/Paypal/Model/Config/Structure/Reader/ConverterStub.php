<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Config\Structure\Reader;

/**
 * Class ConverterStub
 */
class ConverterStub extends \Magento\Config\Model\Config\Structure\Converter
{
    /**
     * @param \DOMDocument $document
     * @return array|null
     */
    public function getArrayData(\DOMDocument $document)
    {
        return $this->_convertDOMDocument($document);
    }

    /**
     * Convert dom document
     *
     * @param \DOMNode $source
     * @return array
     */
    public function convert($source)
    {
        return $this->_convertDOMDocument($source);
    }
}
