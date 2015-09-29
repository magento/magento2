<?php
/**
 * A helper to validate items such as xml against xsd
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Utility;

class Validator
{
    /**
     * @param \DOMDocument $dom
     * @param $schemaFileName
     * @return array
     */
    public static function validateXml(\DOMDocument $dom, $schemaFileName)
    {
        libxml_use_internal_errors(true);
        $errors = \Magento\Framework\Config\Dom::validateDomDocument($dom, $schemaFileName);
        libxml_use_internal_errors(false);

        return $errors;
    }
}
