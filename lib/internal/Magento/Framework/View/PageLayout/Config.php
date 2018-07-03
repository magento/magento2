<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\PageLayout;

/**
 * Page layouts configuration
 */
class Config extends \Magento\Framework\Config\AbstractXml
{
    /**
     * @var \Magento\Framework\Config\Dom\UrnResolver
     */
    protected $urnResolver;

    /**
     * Instantiate with the list of files to merge
     *
     * @param array $configFiles
     * @param \Magento\Framework\Config\DomFactory $domFactory
     * @param \Magento\Framework\Config\Dom\UrnResolver $urnResolver
     * @throws \InvalidArgumentException
     */
    public function __construct(
        $configFiles,
        \Magento\Framework\Config\DomFactory $domFactory,
        \Magento\Framework\Config\Dom\UrnResolver $urnResolver
    ) {
        $this->urnResolver = $urnResolver;
        parent::__construct($configFiles, $domFactory);
    }

    /**
     * Get absolute path to the XML-schema file
     *
     * @return string
     */
    public function getSchemaFile()
    {
        return $this->urnResolver->getRealPath('urn:magento:framework:View/PageLayout/etc/layouts.xsd');
    }

    /**
     * Get page layout that contains declared in system
     *
     * @return string[][]
     */
    public function getPageLayouts()
    {
        return $this->_data;
    }

    /**
     * Checks that the page layout declared in configuration
     *
     * @param string $pageLayout
     * @return bool
     */
    public function hasPageLayout($pageLayout)
    {
        return isset($this->_data[$pageLayout]);
    }

    /**
     * Retrieve page layout options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->getPageLayouts();
    }

    /**
     * @param bool $withEmpty
     * @return array
     */
    public function toOptionArray($withEmpty = false)
    {
        $options = [];
        foreach ($this->getPageLayouts() as $value => $label) {
            $options[] = ['label' => $label, 'value' => $value];
        }

        if ($withEmpty) {
            array_unshift($options, [
                'value' => '',
                'label' => (string)new \Magento\Framework\Phrase('-- Please Select --')
            ]);
        }
        return $options;
    }

    /**
     * Extract configuration data from the DOM structure
     *
     * @param \DOMDocument $dom
     * @return array
     */
    protected function _extractData(\DOMDocument $dom)
    {
        $result = [];

        /** @var \DOMElement $layout */
        foreach ($dom->getElementsByTagName('layout') as $layout) {
            $result[$layout->getAttribute('id')] = trim($layout->nodeValue);
        }
        return $result;
    }

    /**
     * Get XML-contents, initial for merging
     *
     * @return string
     */
    protected function _getInitialXml()
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'
            . '<page_layouts xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"></page_layouts>';
    }

    /**
     * Get list of paths to identifiable nodes
     *
     * @return array
     */
    protected function _getIdAttributes()
    {
        return [
            '/page_layouts/layout' => 'id'
        ];
    }
}
