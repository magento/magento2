<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Xml;

use DOMDocument;

/**
 * The XML Security feature
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
        return strpos((string)$xmlContent, '<!ENTITY') === false;
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings("unused")
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

        $useInternalXmlErrors = libxml_use_internal_errors(true);

        /**
         * Load XML with network access disabled (LIBXML_NONET)
         * error disabled with @ for PHP-FPM scenario
         * Works for PHP < 8
         */
        set_error_handler(
            function ($errno, $errstr) {
                if (substr_count($errstr, 'DOMDocument::loadXML()') > 0) {
                    return true; // ignore default php error handler, $document->loadXML return false
                }
                return false;
            },
            E_WARNING
        );

        try {
            $result = (bool)$document->loadXML($xmlContent, LIBXML_NONET);
        } catch (\ValueError $exception) {
            // In PHP 8, $document->loadXML with an empty content will generate a ValueError.
            // This check emulates the previous (php 7) behaviour.
            if (substr_count($exception->getMessage(), 'DOMDocument::loadXML()') > 0) {
                $result = false;
            } else {
                throw $exception;
            }
        }
        restore_error_handler();
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
