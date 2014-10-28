<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\View\PageLayout;

/**
 * Page layouts configuration
 */
class Config extends \Magento\Framework\Config\AbstractXml
{
    /**
     * Get absolute path to the XML-schema file
     *
     * @return string
     */
    public function getSchemaFile()
    {
        return __DIR__ . '/etc/layouts.xsd';
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
            $options[] = array('label' => $label, 'value' => $value);
        }

        if ($withEmpty) {
            array_unshift($options, array('value' => '', 'label' => __('-- Please Select --')));
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
            .'<page_layouts xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"></page_layouts>';
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
