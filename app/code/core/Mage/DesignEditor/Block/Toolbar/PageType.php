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
 * @category    Mage
 * @package     Mage_DesignEditor
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Page types navigation control
 */
class Mage_DesignEditor_Block_Toolbar_PageType extends Mage_Core_Block_Template
{
    /**
     * @var string|false
     */
    protected $_selectedPageType;

    /**
     * Recursively render each level of the page types hierarchy as an HTML list
     *
     * @param array $pageTypes
     * @return string
     */
    protected function _renderPageTypes(array $pageTypes)
    {
        if (!$pageTypes) {
            return '';
        }
        $result = '<ul>';
        foreach ($pageTypes as $name => $info) {
            $result .= '<li rel="' . $name . '">';
            $result .= '<a href="' . $this->getUrl('design/editor/page', array('page_type' => $name)) . '">';
            $result .= $this->escapeHtml($info['label']);
            $result .= '</a>';
            $result .= $this->_renderPageTypes($info['children']);
            $result .= '</li>';
        }
        $result .= '</ul>';
        return $result;
    }

    /**
     * Render page types hierarchy as an HTML list
     *
     * @return string
     */
    public function renderPageTypes()
    {
        return $this->_renderPageTypes($this->getLayout()->getUpdate()->getPageTypesHierarchy());
    }

    /**
     * Retrieve the name of the currently selected page type
     *
     * @return string|false
     */
    public function getSelectedPageType()
    {
        if ($this->_selectedPageType === null) {
            $pageHandles = $this->getLayout()->getUpdate()->getPageHandles();
            $this->_selectedPageType = end($pageHandles);
        }
        return $this->_selectedPageType;
    }

    /**
     * Set the name of the currently selected page type
     *
     * @param string $name Page type name
     */
    public function setSelectedPageType($name)
    {
        $this->_selectedPageType = $name;
    }
}
