<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Language;

use Magento\Framework\Config\Dom;

/**
 * Language pack configuration file
 */
class Config
{
    /**
     * Data extracted from the configuration file
     *
     * @var array
     */
    protected $_data;

    /**
     * Constructor
     *
     * @param string $source
     * @throws \Magento\Framework\Exception
     */
    public function __construct($source)
    {
        $config = new \DOMDocument();
        $config->loadXML($source);
        $errors = Dom::validateDomDocument($config, $this->getSchemaFile());
        if (!empty($errors)) {
            throw new \Magento\Framework\Exception("Invalid Document: \n" . implode("\n", $errors));
        }
        $this->_data = $this->_extractData($config);
    }

    /**
     * Get absolute path to validation scheme for language.xml
     *
     * @return string
     */
    protected function getSchemaFile()
    {
        return __DIR__ . '/package.xsd';
    }

    /**
     * Extract configuration data from the DOM structure
     *
     * @param \DOMDocument $dom
     * @return array
     */
    protected function _extractData(\DOMDocument $dom)
    {
        /** @var $languageNode \DOMElement */
        $languageNode = $dom->getElementsByTagName('language')->item(0);
        /** @var $codeNode \DOMElement */
        $codeNode = $languageNode->getElementsByTagName('code')->item(0);
        /** @var $vendorNode \DOMElement */
        $vendorNode = $languageNode->getElementsByTagName('vendor')->item(0);
        /** @var $packageNode \DOMElement */
        $packageNode = $languageNode->getElementsByTagName('package')->item(0);
        /** @var $sortOrderNode \DOMElement */
        $sortOrderNode = $languageNode->getElementsByTagName('sort_order')->item(0);
        $use = [];
        /** @var $useNode \DOMElement */
        foreach ($languageNode->getElementsByTagName('use') as $useNode) {
            $use[] = [
                'vendor'  => $useNode->getAttribute('vendor'),
                'package' => $useNode->getAttribute('package'),
            ];
        }
        return [
            'code'       => $codeNode->nodeValue,
            'vendor'     => $vendorNode->nodeValue,
            'package'    => $packageNode->nodeValue,
            'sort_order' => $sortOrderNode ? $sortOrderNode->nodeValue : 0,
            'use'        => $use
        ];
    }

    /**
     * Language code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->_data['code'];
    }

    /**
     * Language vendor
     *
     * @return string
     */
    public function getVendor()
    {
        return $this->_data['vendor'];
    }

    /**
     * Language package
     *
     * @return string
     */
    public function getPackage()
    {
        return $this->_data['package'];
    }

    /**
     * Sort order
     *
     * @return null|int
     */
    public function getSortOrder()
    {
        return $this->_data['sort_order'];
    }

    /**
     * Declaration of Inheritances
     *
     * @return string[][]
     */
    public function getUses()
    {
        return $this->_data['use'];
    }
}
