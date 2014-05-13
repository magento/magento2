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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog layered navigation view block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\LayeredNavigation\Block;

use Magento\Framework\View\Element\Template;

class Navigation extends \Magento\Framework\View\Element\Template
{
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
     * @param \Magento\Catalog\Model\Layer $catalogLayer
     * @param \Magento\Catalog\Model\Layer\FilterList $filterList
     * @param \Magento\Catalog\Model\Layer\AvailabilityFlagInterface $visibilityFlag
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Layer $catalogLayer,
        \Magento\Catalog\Model\Layer\FilterList $filterList,
        \Magento\Catalog\Model\Layer\AvailabilityFlagInterface $visibilityFlag,
        array $data = array()
    ) {
        $this->_catalogLayer = $catalogLayer;
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
        $this->renderer = $this->getChildBlock('renderer');
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
        return $this->visibilityFlag->isEnabled($this->getLayer(), $this->getFilters());
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
}
