<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Utility;

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
        $document->schemaValidate($schema);
        $validationResult = libxml_get_errors();
        libxml_use_internal_errors(false);
        $result = [];
        foreach ($validationResult as $error) {
            $result[] = trim($error->message);
        }
        return $result;
    }
}
