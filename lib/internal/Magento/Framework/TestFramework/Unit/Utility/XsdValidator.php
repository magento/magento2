<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\TestFramework\Unit\Utility;

/**
 * Class \Magento\Framework\TestFramework\Unit\Utility\XsdValidator
 *
 * @since 2.0.0
 */
class XsdValidator
{
    /**
     * @param string $schema
     * @param string $xml
     * @return array
     * @since 2.0.0
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
