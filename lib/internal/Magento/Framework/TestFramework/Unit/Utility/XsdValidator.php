<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
