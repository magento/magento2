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
namespace Magento\Customer\Block\Adminhtml\Edit\Renderer\Attribute;

use Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element;
use Magento\Customer\Controller\RegistryConstants;

/**
 * Renderer for customer group ID
 *
 * @method \Magento\Customer\Service\V1\Data\Eav\AttributeMetadata getDisableAutoGroupChangeAttribute()
 * @method mixed getDisableAutoGroupChangeAttributeValue()
 */
class Group extends Element
{
    /**
     * @var string
     */
    protected $_template = 'edit/tab/account/form/renderer/group.phtml';

    /**
     * Customer address
     *
     * @var \Magento\Customer\Helper\Address
     */
    protected $_addressHelper = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Customer\Helper\Address $customerAddress
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Customer\Helper\Address $customerAddress,
        \Magento\Framework\Registry $registry,
        array $data = array()
    ) {
        $this->_coreRegistry = $registry;
        $this->_addressHelper = $customerAddress;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve disable auto group change element HTML ID
     *
     * @return string
     */
    protected function _getDisableAutoGroupChangeElementHtmlId()
    {
        return $this->getDisableAutoGroupChangeAttribute()->getAttributeCode();
    }

    /**
     * Retrieve disable auto group change checkbox label text
     *
     * @return string
     */
    public function getDisableAutoGroupChangeCheckboxLabel()
    {
        return __($this->getDisableAutoGroupChangeAttribute()->getFrontendLabel());
    }

    /**
     * Retrieve disable auto group change checkbox state
     *
     * @return string
     */
    public function getDisableAutoGroupChangeCheckboxState()
    {
        $customerId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        $checkedByDefault = $customerId ? false : $this->_addressHelper->isDisableAutoGroupAssignDefaultValue();

        $value = $this->getDisableAutoGroupChangeAttributeValue();
        $state = '';
        if (!empty($value) || $checkedByDefault) {
            $state = 'checked';
        }
        return $state;
    }

    /**
     * Retrieve disable auto group change checkbox element HTML NAME
     *
     * @return string
     */
    public function getDisableAutoGroupChangeCheckboxElementName()
    {
        return $this->getElement()->getForm()->getFieldNameSuffix() .
            '[' .
            $this->_getDisableAutoGroupChangeElementHtmlId() .
            ']';
    }

    /**
     * Retrieve disable auto group change checkbox element HTML ID
     *
     * @return string
     */
    public function getDisableAutoGroupChangeCheckboxElementId()
    {
        return $this->_getDisableAutoGroupChangeElementHtmlId();
    }
}
