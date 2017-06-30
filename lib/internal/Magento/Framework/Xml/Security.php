<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Xml;

use DOMDocument;
use Magento\Framework\Phrase;

/**
 * Class Security
 */
class Security
{
    /**
     * Heuristic scan to detect entity in XML
     *
     * @param string $xmlContent
     * @return bool
     */
    private function heuristicScan($xmlContent)
    {
        return strpos($xmlContent, '<!ENTITY') === false;
    }

    /**
     * Return true if PHP is running with PHP-FPM
     *
     * @return bool
     */
    private function isPhpFpm()
    {
        return substr(php_sapi_name(), 0, 3) === 'fpm';
    }

    /**
     * Security check loaded XML document
     *
     * @param string $xmlContent
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function scan($xmlContent)
    {
        /**
         * If running with PHP-FPM we perform an heuristic scan
         * We cannot use libxml_disable_entity_loader because of this bug
         * @see https://bugs.php.net/bug.php?id=64938
         */
        if ($this->isPhpFpm()) {
            return $this->heuristicScan($xmlContent);
        }

        $document = new DOMDocument();

        $loadEntities = libxml_disable_entity_loader(true);
        $useInternalXmlErrors = libxml_use_internal_errors(true);

        /**
         * Load XML with network access disabled (LIBXML_NONET)
         * error disabled with @ for PHP-FPM scenario
         */
        set_error_handler(
            function ($errno, $errstr) {
                if (substr_count($errstr, 'DOMDocument::loadXML()') > 0) {
                    return true;
                }
                return false;
            },
            E_WARNING
        );

        $result = (bool)$document->loadXml($xmlContent, LIBXML_NONET);
        restore_error_handler();
        // Entity load to previous setting
        libxml_disable_entity_loader($loadEntities);
        libxml_use_internal_errors($useInternalXmlErrors);

        if (!$result) {
            return false;
        }

        foreach ($document->childNodes as $child) {
            if ($child->nodeType === XML_DOCUMENT_TYPE_NODE) {
                if ($child->entities->length > 0) {
                    return false;
                }
            }
        }

        return true;
    }
}
