<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\LayeredNavigation\Block;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Block\Product\ProductList\Toolbar;

/**
 * Catalog layered navigation view block
 *
 * @api
 * @since 100.0.2
 */
class Navigation extends \Magento\Framework\View\Element\Template
{
    /**
     * Product listing toolbar block name
     */
    private const PRODUCT_LISTING_TOOLBAR_BLOCK = 'product_list_toolbar';

    /**
     * Catalog layer
     *
     * @var \Magento\Catalog\Model\Layer
     */
    protected $_catalogLayer;

    /**
     * @var \Magento\Catalog\Model\Layer\FilterList
     */
    protected $filterList;

    /**
     * @var \Magento\Catalog\Model\Layer\AvailabilityFlagInterface
     */
    protected $visibilityFlag;

    /**
     * @param Template\Context $context
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\Catalog\Model\Layer\FilterList $filterList
     * @param \Magento\Catalog\Model\Layer\AvailabilityFlagInterface $visibilityFlag
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Catalog\Model\Layer\FilterList $filterList,
        \Magento\Catalog\Model\Layer\AvailabilityFlagInterface $visibilityFlag,
        array $data = []
    ) {
        $this->_catalogLayer = $layerResolver->get();
        $this->filterList = $filterList;
        $this->visibilityFlag = $visibilityFlag;
        parent::__construct($context, $data);
    }

    /**
     * Apply layer
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        foreach ($this->filterList->getFilters($this->_catalogLayer) as $filter) {
            $filter->apply($this->getRequest());
        }
        $this->getLayer()->apply();

        return parent::_prepareLayout();
    }

    /**
     * @inheritdoc
     * @since 100.3.4
     */
    protected function _beforeToHtml()
    {
        $this->configureToolbarBlock();

        return parent::_beforeToHtml();
    }

    /**
     * Get layer object
     *
     * @return \Magento\Catalog\Model\Layer
     */
    public function getLayer()
    {
        return $this->_catalogLayer;
    }

    /**
     * Get layered navigation state html
     *
     * @return string
     */
    public function getStateHtml()
    {
        return $this->getChildHtml('state');
    }

    /**
     * Get all layer filters
     *
     * @return array
     */
    public function getFilters()
    {
        return $this->filterList->getFilters($this->_catalogLayer);
    }

    /**
     * Check availability display layer block
     *
     * @return bool
     */
    public function canShowBlock()
    {
        return $this->getLayer()->getCurrentCategory()->getDisplayMode() !== \Magento\Catalog\Model\Category::DM_PAGE
            && $this->visibilityFlag->isEnabled($this->getLayer(), $this->getFilters());
    }

    /**
     * Get url for 'Clear All' link
     *
     * @return string
     */
    public function getClearUrl()
    {
        return $this->getChildBlock('state')->getClearUrl();
    }

    /**
     * Configures the Toolbar block
     *
     * @return void
     */
    private function configureToolbarBlock(): void
    {
        /** @var Toolbar $toolbarBlock */
        $toolbarBlock = $this->getLayout()->getBlock(self::PRODUCT_LISTING_TOOLBAR_BLOCK);
        if ($toolbarBlock) {
            /** @var Collection $collection */
            $collection = $this->getLayer()->getProductCollection();
            $toolbarBlock->setCollection($collection);
        }
    }
}
