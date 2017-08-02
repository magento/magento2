<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Price\Group;

use Magento\Backend\Block\Widget;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

/**
 * Adminhtml group price item abstract renderer
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
abstract class AbstractGroup extends Widget implements RendererInterface
{
    /**
     * Form element instance
     *
     * @var \Magento\Framework\Data\Form\Element\AbstractElement
     * @since 2.0.0
     */
    protected $_element;

    /**
     * Customer groups cache
     *
     * @var array
     * @since 2.0.0
     */
    protected $_customerGroups;

    /**
     * Websites cache
     *
     * @var array
     * @since 2.0.0
     */
    protected $_websites;

    /**
     * Catalog data
     *
     * @var \Magento\Framework\Module\Manager
     * @since 2.0.0
     */
    protected $moduleManager;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Directory\Helper\Data
     * @since 2.0.0
     */
    protected $_directoryHelper;

    /**
     * @var GroupRepositoryInterface
     * @since 2.0.0
     */
    protected $_groupRepository;

    /**
     * @var GroupManagementInterface
     * @since 2.0.0
     */
    protected $_groupManagement;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     * @since 2.0.0
     */
    protected $_searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     * @since 2.0.0
     */
    protected $_localeCurrency;

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
     * @since 2.0.0
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
        array $data = []
    ) {
        $this->_groupRepository = $groupRepository;
        $this->_directoryHelper = $directoryHelper;
        $this->moduleManager = $moduleManager;
        $this->_coreRegistry = $registry;
        $this->_groupManagement = $groupManagement;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_localeCurrency = $localeCurrency;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve current product instance
     *
     * @return \Magento\Catalog\Model\Product
     * @since 2.0.0
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('product');
    }

    /**
     * Render HTML
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @since 2.0.0
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

    /**
     * Set form element instance
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Price\Group\AbstractGroup
     * @since 2.0.0
     */
    public function setElement(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->_element = $element;
        return $this;
    }

    /**
     * Retrieve form element instance
     *
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     * @since 2.0.0
     */
    public function getElement()
    {
        return $this->_element;
    }

    /**
     * Prepare group price values
     *
     * @return array
     * @since 2.0.0
     */
    public function getValues()
    {
        $values = [];
        $data = $this->getElement()->getValue();

        if (is_array($data)) {
            $values = $this->_sortValues($data);
        }

        $currency = $this->_localeCurrency->getCurrency($this->_directoryHelper->getBaseCurrencyCode());

        foreach ($values as &$value) {
            $value['readonly'] = $value['website_id'] == 0 &&
                $this->isShowWebsiteColumn() &&
                !$this->isAllowChangeWebsite();
            $value['price'] =
                $currency->toCurrency($value['price'], ['display' => \Magento\Framework\Currency::NO_SYMBOL]);
        }

        return $values;
    }

    /**
     * Sort values
     *
     * @param array $data
     * @return array
     * @since 2.0.0
     */
    protected function _sortValues($data)
    {
        return $data;
    }

    /**
     * Retrieve allowed customer groups
     *
     * @param int|null $groupId  return name by customer group id
     * @return array|string
     * @since 2.0.0
     */
    public function getCustomerGroups($groupId = null)
    {
        if ($this->_customerGroups === null) {
            if (!$this->moduleManager->isEnabled('Magento_Customer')) {
                return [];
            }
            $this->_customerGroups = $this->_getInitialCustomerGroups();
            /** @var \Magento\Customer\Api\Data\GroupInterface[] $groups */
            $groups = $this->_groupRepository->getList($this->_searchCriteriaBuilder->create());
            foreach ($groups->getItems() as $group) {
                $this->_customerGroups[$group->getId()] = $group->getCode();
            }
        }

        if ($groupId !== null) {
            return isset($this->_customerGroups[$groupId]) ? $this->_customerGroups[$groupId] : [];
        }

        return $this->_customerGroups;
    }

    /**
     * Retrieve list of initial customer groups
     *
     * @return array
     * @since 2.0.0
     */
    protected function _getInitialCustomerGroups()
    {
        return [];
    }

    /**
     * Retrieve number of websites
     *
     * @return int
     * @since 2.0.0
     */
    public function getWebsiteCount()
    {
        return count($this->getWebsites());
    }

    /**
     * Show website column and switcher for group price table
     *
     * @return bool
     * @since 2.0.0
     */
    public function isMultiWebsites()
    {
        return !$this->_storeManager->isSingleStoreMode();
    }

    /**
     * Retrieve allowed for edit websites
     *
     * @return array
     * @since 2.0.0
     */
    public function getWebsites()
    {
        if ($this->_websites !== null) {
            return $this->_websites;
        }

        $this->_websites = [
            0 => ['name' => __('All Websites'), 'currency' => $this->_directoryHelper->getBaseCurrencyCode()]
        ];

        if (!$this->isScopeGlobal() && $this->getProduct()->getStoreId()) {
            /** @var $website \Magento\Store\Model\Website */
            $website = $this->_storeManager->getStore($this->getProduct()->getStoreId())->getWebsite();

            $this->_websites[$website->getId()] = [
                'name' => $website->getName(),
                'currency' => $website->getBaseCurrencyCode()
            ];
        } elseif (!$this->isScopeGlobal()) {
            $websites = $this->_storeManager->getWebsites();
            $productWebsiteIds = $this->getProduct()->getWebsiteIds();
            foreach ($websites as $website) {
                /** @var $website \Magento\Store\Model\Website */
                if (!in_array($website->getId(), $productWebsiteIds)) {
                    continue;
                }
                $this->_websites[$website->getId()] = [
                    'name' => $website->getName(),
                    'currency' => $website->getBaseCurrencyCode()
                ];
            }
        }

        return $this->_websites;
    }

    /**
     * Retrieve default value for customer group
     *
     * @return int
     * @since 2.0.0
     */
    public function getDefaultCustomerGroup()
    {
        return $this->_groupManagement->getAllCustomersGroup()->getId();
    }

    /**
     * Retrieve default value for website
     *
     * @return int
     * @since 2.0.0
     */
    public function getDefaultWebsite()
    {
        if ($this->isShowWebsiteColumn() && !$this->isAllowChangeWebsite()) {
            return $this->_storeManager->getStore($this->getProduct()->getStoreId())->getWebsiteId();
        }
        return 0;
    }

    /**
     * Retrieve 'add group price item' button HTML
     *
     * @return string
     * @since 2.0.0
     */
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    /**
     * Retrieve customized price column header
     *
     * @param string $default
     * @return string
     * @since 2.0.0
     */
    public function getPriceColumnHeader($default)
    {
        if ($this->hasData('price_column_header')) {
            return $this->getData('price_column_header');
        } else {
            return $default;
        }
    }

    /**
     * Retrieve customized price column header
     *
     * @param string $default
     * @return string
     * @since 2.0.0
     */
    public function getPriceValidation($default)
    {
        if ($this->hasData('price_validation')) {
            return $this->getData('price_validation');
        } else {
            return $default;
        }
    }

    /**
     * Retrieve Group Price entity attribute
     *
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     * @since 2.0.0
     */
    public function getAttribute()
    {
        return $this->getElement()->getEntityAttribute();
    }

    /**
     * Check group price attribute scope is global
     *
     * @return bool
     * @since 2.0.0
     */
    public function isScopeGlobal()
    {
        return $this->getAttribute()->isScopeGlobal();
    }

    /**
     * Show group prices grid website column
     *
     * @return bool
     * @since 2.0.0
     */
    public function isShowWebsiteColumn()
    {
        if ($this->isScopeGlobal() || $this->_storeManager->isSingleStoreMode()) {
            return false;
        }
        return true;
    }

    /**
     * Check is allow change website value for combination
     *
     * @return bool
     * @since 2.0.0
     */
    public function isAllowChangeWebsite()
    {
        if (!$this->isShowWebsiteColumn() || $this->getProduct()->getStoreId()) {
            return false;
        }
        return true;
    }
}
