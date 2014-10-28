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
namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Price\Group;

use Magento\Backend\Block\Widget;
use Magento\Customer\Service\V1\CustomerGroupServiceInterface;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

/**
 * Adminhtml group price item abstract renderer
 */
abstract class AbstractGroup extends Widget implements RendererInterface
{
    /**
     * Form element instance
     *
     * @var \Magento\Framework\Data\Form\Element\AbstractElement
     */
    protected $_element;

    /**
     * Customer groups cache
     *
     * @var array
     */
    protected $_customerGroups;

    /**
     * Websites cache
     *
     * @var array
     */
    protected $_websites;

    /**
     * Catalog data
     *
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogData = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $_directoryHelper;

    /**
     * @var CustomerGroupServiceInterface
     */
    protected $_groupService;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param CustomerGroupServiceInterface $groupService
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        CustomerGroupServiceInterface $groupService,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\Registry $registry,
        array $data = array()
    ) {
        $this->_groupService = $groupService;
        $this->_directoryHelper = $directoryHelper;
        $this->_catalogData = $catalogData;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve current product instance
     *
     * @return \Magento\Catalog\Model\Product
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
     */
    public function getElement()
    {
        return $this->_element;
    }

    /**
     * Prepare group price values
     *
     * @return array
     */
    public function getValues()
    {
        $values = array();
        $data = $this->getElement()->getValue();

        if (is_array($data)) {
            $values = $this->_sortValues($data);
        }

        foreach ($values as &$value) {
            $value['readonly'] = $value['website_id'] == 0 &&
                $this->isShowWebsiteColumn() &&
                !$this->isAllowChangeWebsite();
        }

        return $values;
    }

    /**
     * Sort values
     *
     * @param array $data
     * @return array
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
     */
    public function getCustomerGroups($groupId = null)
    {
        if ($this->_customerGroups === null) {
            if (!$this->_catalogData->isModuleEnabled('Magento_Customer')) {
                return array();
            }
            $groups = $this->_groupService->getGroups();
            $this->_customerGroups = $this->_getInitialCustomerGroups();

            foreach ($groups as $group) {
                $this->_customerGroups[$group->getId()] = $group->getCode();
            }
        }

        if ($groupId !== null) {
            return isset($this->_customerGroups[$groupId]) ? $this->_customerGroups[$groupId] : array();
        }

        return $this->_customerGroups;
    }

    /**
     * Retrieve list of initial customer groups
     *
     * @return array
     */
    protected function _getInitialCustomerGroups()
    {
        return array();
    }

    /**
     * Retrieve number of websites
     *
     * @return int
     */
    public function getWebsiteCount()
    {
        return count($this->getWebsites());
    }

    /**
     * Show website column and switcher for group price table
     *
     * @return bool
     */
    public function isMultiWebsites()
    {
        return !$this->_storeManager->isSingleStoreMode();
    }

    /**
     * Retrieve allowed for edit websites
     *
     * @return array
     */
    public function getWebsites()
    {
        if (!is_null($this->_websites)) {
            return $this->_websites;
        }

        $this->_websites = array(
            0 => array('name' => __('All Websites'), 'currency' => $this->_directoryHelper->getBaseCurrencyCode())
        );

        if (!$this->isScopeGlobal() && $this->getProduct()->getStoreId()) {
            /** @var $website \Magento\Store\Model\Website */
            $website = $this->_storeManager->getStore($this->getProduct()->getStoreId())->getWebsite();

            $this->_websites[$website->getId()] = array(
                'name' => $website->getName(),
                'currency' => $website->getBaseCurrencyCode()
            );
        } elseif (!$this->isScopeGlobal()) {
            $websites = $this->_storeManager->getWebsites(false);
            $productWebsiteIds = $this->getProduct()->getWebsiteIds();
            foreach ($websites as $website) {
                /** @var $website \Magento\Store\Model\Website */
                if (!in_array($website->getId(), $productWebsiteIds)) {
                    continue;
                }
                $this->_websites[$website->getId()] = array(
                    'name' => $website->getName(),
                    'currency' => $website->getBaseCurrencyCode()
                );
            }
        }

        return $this->_websites;
    }

    /**
     * Retrieve default value for customer group
     *
     * @return int
     */
    public function getDefaultCustomerGroup()
    {
        return CustomerGroupServiceInterface::CUST_GROUP_ALL;
    }

    /**
     * Retrieve default value for website
     *
     * @return int
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
     * @return \Magento\Catalog\Model\Resource\Eav\Attribute
     */
    public function getAttribute()
    {
        return $this->getElement()->getEntityAttribute();
    }

    /**
     * Check group price attribute scope is global
     *
     * @return bool
     */
    public function isScopeGlobal()
    {
        return $this->getAttribute()->isScopeGlobal();
    }

    /**
     * Show group prices grid website column
     *
     * @return bool
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
     */
    public function isAllowChangeWebsite()
    {
        if (!$this->isShowWebsiteColumn() || $this->getProduct()->getStoreId()) {
            return false;
        }
        return true;
    }
}
