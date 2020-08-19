<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Price;

use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Json\Helper\Data as JsonHelper;

/**
 * Adminhtml tier price item renderer
 *
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
class Tier extends Group\AbstractGroup
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Catalog::catalog/product/edit/price/tier.phtml';

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param GroupRepositoryInterface $groupRepository
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Framework\Registry $registry
     * @param GroupManagementInterface $groupManagement
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param array $data
     * @param JsonHelper|null $jsonHelper
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        GroupRepositoryInterface $groupRepository,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\Registry $registry,
        GroupManagementInterface $groupManagement,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        array $data = [],
        ?JsonHelper $jsonHelper = null
    ) {
        $data['jsonHelper'] = $jsonHelper ?? ObjectManager::getInstance()->get(JsonHelper::class);
        parent::__construct(
            $context,
            $groupRepository,
            $directoryHelper,
            $moduleManager,
            $registry,
            $groupManagement,
            $searchCriteriaBuilder,
            $localeCurrency,
            $data
        );
    }

    /**
     * Retrieve list of initial customer groups
     *
     * @return array
     */
    protected function _getInitialCustomerGroups()
    {
        return [$this->_groupManagement->getAllCustomersGroup()->getId() => __('ALL GROUPS')];
    }

    /**
     * Sort values
     *
     * @param array $data
     * @return array
     */
    protected function _sortValues($data)
    {
        usort($data, [$this, '_sortTierPrices']);
        return $data;
    }

    /**
     * Sort tier price values callback method
     *
     * @param array $a
     * @param array $b
     * @return int
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _sortTierPrices($a, $b)
    {
        if ($a['website_id'] != $b['website_id']) {
            return $a['website_id'] < $b['website_id'] ? -1 : 1;
        }
        if ($a['cust_group'] != $b['cust_group']) {
            return $this->getCustomerGroups($a['cust_group']) < $this->getCustomerGroups($b['cust_group']) ? -1 : 1;
        }
        if ($a['price_qty'] != $b['price_qty']) {
            return $a['price_qty'] < $b['price_qty'] ? -1 : 1;
        }

        return 0;
    }

    /**
     * Prepare global layout
     *
     * Add "Add tier" button to layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $button = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            ['label' => __('Add Price'), 'onclick' => 'return tierPriceControl.addItem()', 'class' => 'add']
        );
        $button->setName('add_tier_price_item_button');

        $this->setChild('add_button', $button);
        return parent::_prepareLayout();
    }
}
