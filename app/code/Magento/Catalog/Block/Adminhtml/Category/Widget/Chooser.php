<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Category chooser for Wysiwyg CMS widget
 */
namespace Magento\Catalog\Block\Adminhtml\Category\Widget;

class Chooser extends \Magento\Catalog\Block\Adminhtml\Category\Tree
{
    /**
     * @var array
     */
    protected $_selectedCategories = [];

    /**
     * Block construction
     * Defines tree template and init tree params
     *
     * @var string
     */
    protected $_template = 'Magento_Catalog::catalog/category/widget/tree.phtml';

    /**
     * Initialise the block
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_withProductCount = false;
    }

    /**
     * Setter
     *
     * @param array $selectedCategories
     * @return $this
     */
    public function setSelectedCategories($selectedCategories)
    {
        $this->_selectedCategories = $selectedCategories;
        return $this;
    }

    /**
     * Getter
     *
     * @return array
     */
    public function getSelectedCategories()
    {
        return $this->_selectedCategories;
    }

    /**
     * Prepare chooser element HTML
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element Form Element
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function prepareElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $uniqId = $this->mathRandom->getUniqueHash($element->getId());
        $sourceUrl = $this->getUrl(
            'catalog/category_widget/chooser',
            ['uniq_id' => $uniqId, 'use_massaction' => false]
        );

        $chooser = $this->getLayout()->createBlock(
            \Magento\Widget\Block\Adminhtml\Widget\Chooser::class
        )->setElement(
            $element
        )->setConfig(
            $this->getConfig()
        )->setFieldsetId(
            $this->getFieldsetId()
        )->setSourceUrl(
            $sourceUrl
        )->setUniqId(
            $uniqId
        );

        if ($element->getValue()) {
            $value = explode('/', $element->getValue());
            $categoryId = false;
            if (isset($value[0]) && isset($value[1]) && $value[0] == 'category') {
                $categoryId = $value[1];
            }
            if ($categoryId) {
                $label = $this->_categoryFactory->create()->load($categoryId)->getName();
                $chooser->setLabel($label);
            }
        }

        $element->setData('after_element_html', $chooser->toHtml());
        return $element;
    }

    /**
     * Category Tree node onClick listener js function
     *
     * @return string
     */
    public function getNodeClickListener()
    {
        if ($this->getData('node_click_listener')) {
            return $this->getData('node_click_listener');
        }
        if ($this->getUseMassaction()) {
            $js = '
                function (node, e) {
                    if (node.ui.toggleCheck) {
                        node.ui.toggleCheck(true);
                    }
                }
            ';
        } else {
            $chooserJsObject = $this->escapeJs($this->getId());
            $js = '
                function (node, e) {
                    ' .
                $chooserJsObject .
                '.setElementValue("category/" + node.attributes.id);
                    ' .
                $chooserJsObject .
                '.setElementLabel(node.text);
                    ' .
                $chooserJsObject .
                '.close();
                }
            ';
        }
        return $js;
    }

    /**
     * Get JSON of a tree node or an associative array
     *
     * @param \Magento\Framework\Data\Tree\Node|array $node
     * @param int $level
     * @return array
     */
    protected function _getNodeJson($node, $level = 0)
    {
        $item = parent::_getNodeJson($node, $level);
        if (in_array($node->getId(), $this->getSelectedCategories())) {
            $item['checked'] = true;
        }
        $item['is_anchor'] = $node->getIsAnchor() !== null ? (int) $node->getIsAnchor() : 1;
        $item['url_key'] = $node->getData('url_key');
        return $item;
    }

    /**
     * Adds some extra params to categories collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    public function getCategoryCollection()
    {
        return parent::getCategoryCollection()->addAttributeToSelect('url_key')->addAttributeToSelect('is_anchor');
    }

    /**
     * Tree JSON source URL
     *
     * @param bool|null $expanded
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getLoadTreeUrl($expanded = null)
    {
        return $this->getUrl(
            'catalog/category_widget/categoriesJson',
            ['_current' => true, 'uniq_id' => $this->getId(), 'use_massaction' => $this->getUseMassaction()]
        );
    }
}
