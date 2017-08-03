<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog layered navigation view block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\LayeredNavigation\Block;

use Magento\Framework\View\Element\Template;

/**
 * @api
 * @since 2.0.0
 */
class Navigation extends \Magento\Framework\View\Element\Template
{
    /**
     * Catalog layer
     *
     * @var \Magento\Catalog\Model\Layer
     * @since 2.0.0
     */
    protected $_catalogLayer;

    /**
     * @var \Magento\Catalog\Model\Layer\FilterList
     * @since 2.0.0
     */
    protected $filterList;

    /**
     * @var \Magento\Catalog\Model\Layer\AvailabilityFlagInterface
     * @since 2.0.0
     */
    protected $visibilityFlag;

    /**
     * @param Template\Context $context
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\Catalog\Model\Layer\FilterList $filterList
     * @param \Magento\Catalog\Model\Layer\AvailabilityFlagInterface $visibilityFlag
     * @param array $data
     * @since 2.0.0
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
     * @since 2.0.0
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
     * Get layer object
     *
     * @return \Magento\Catalog\Model\Layer
     * @since 2.0.0
     */
    public function getLayer()
    {
        return $this->_catalogLayer;
    }

    /**
     * Get layered navigation state html
     *
     * @return string
     * @since 2.0.0
     */
    public function getStateHtml()
    {
        return $this->getChildHtml('state');
    }

    /**
     * Get all layer filters
     *
     * @return array
     * @since 2.0.0
     */
    public function getFilters()
    {
        return $this->filterList->getFilters($this->_catalogLayer);
    }

    /**
     * Check availability display layer block
     *
     * @return bool
     * @since 2.0.0
     */
    public function canShowBlock()
    {
        return $this->visibilityFlag->isEnabled($this->getLayer(), $this->getFilters());
    }

    /**
     * Get url for 'Clear All' link
     *
     * @return string
     * @since 2.0.0
     */
    public function getClearUrl()
    {
        return $this->getChildBlock('state')->getClearUrl();
    }
}
