<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\TestFramework\Unit\Utility;

class XsdValidator
{
    /**
     * @param string $schema
     * @param string $xml
     * @return array
     */
    public function validate($schema, $xml)
    {
        $document = new \DOMDocument();
        $document->loadXML($xml);

        libxml_use_internal_errors(true);
        $errors = \Magento\Framework\Config\Dom::validateDomDocument($document, $schema);
        libxml_use_internal_errors(false);

        return $errors;
    }
}
