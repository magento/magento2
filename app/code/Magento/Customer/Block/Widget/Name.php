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
 * @category    Magento
 * @package     Magento_Customer
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Customer\Block\Widget;

use Magento\Customer\Service\V1\Dto\Customer;

class Name extends \Magento\Customer\Block\Widget\AbstractWidget
{
    /**
     * @var \Magento\Customer\Helper\Data
     */
    protected $_customerHelper;

    /**
     * @param \Magento\View\Element\Template\Context $context
     * @param \Magento\Customer\Helper\Address $addressHelper
     * @param \Magento\Customer\Service\V1\CustomerMetadataServiceInterface $attributeMetadata,
     * @param \Magento\Customer\Helper\Data $customerHelper
     * @param array $data
     */
    public function __construct(
        \Magento\View\Element\Template\Context $context,
        \Magento\Customer\Helper\Address $addressHelper,
        \Magento\Customer\Service\V1\CustomerMetadataServiceInterface $attributeMetadata,
        \Magento\Customer\Helper\Data $customerHelper,
        array $data = array()
    ) {
        $this->_customerHelper = $customerHelper;
        parent::__construct($context, $addressHelper, $attributeMetadata, $data);
        $this->_isScopePrivate = true;
    }

    public function _construct()
    {
        parent::_construct();

        // default template location
        $this->setTemplate('widget/name.phtml');
    }

    /**
     * Can show config value
     *
     * @param string $key
     * @return bool
     */
    protected function _showConfig($key)
    {
        return (bool)$this->getConfig($key);
    }

    /**
     * Can show prefix
     *
     * @return bool
     */
    public function showPrefix()
    {
        return $this->_isAttributeVisible('prefix');
    }

    /**
     * Define if prefix attribute is required
     *
     * @return bool
     */
    public function isPrefixRequired()
    {
        return $this->_isAttributeRequired('prefix');
    }

    /**
     * Retrieve name prefix drop-down options
     *
     * @return array|bool
     */
    public function getPrefixOptions()
    {
        $prefixOptions = $this->_customerHelper->getNamePrefixOptions();

        if ($this->getObject() && !empty($prefixOptions)) {
            $oldPrefix = $this->escapeHtml(trim($this->getObject()->getPrefix()));
            $prefixOptions[$oldPrefix] = $oldPrefix;
        }
        return $prefixOptions;
    }

    /**
     * Define if middle name attribute can be shown
     *
     * @return bool
     */
    public function showMiddlename()
    {
        return $this->_isAttributeVisible('middlename');
    }

    /**
     * Define if middlename attribute is required
     *
     * @return bool
     */
    public function isMiddlenameRequired()
    {
        return $this->_isAttributeRequired('middlename');
    }

    /**
     * Define if suffix attribute can be shown
     *
     * @return bool
     */
    public function showSuffix()
    {
        return $this->_isAttributeVisible('suffix');
    }

    /**
     * Define if suffix attribute is required
     *
     * @return bool
     */
    public function isSuffixRequired()
    {
        return $this->_isAttributeRequired('suffix');
    }

    /**
     * Retrieve name suffix drop-down options
     *
     * @return array|bool
     */
    public function getSuffixOptions()
    {
        $suffixOptions = $this->_customerHelper->getNameSuffixOptions();
        if ($this->getObject() && !empty($suffixOptions)) {
            $oldSuffix = $this->escapeHtml(trim($this->getObject()->getSuffix()));
            $suffixOptions[$oldSuffix] = $oldSuffix;
        }
        return $suffixOptions;
    }

    /**
     * Class name getter
     *
     * @return string
     */
    public function getClassName()
    {
        if (!$this->hasData('class_name')) {
            $this->setData('class_name', 'customer-name');
        }
        return $this->getData('class_name');
    }

    /**
     * Container class name getter
     *
     * @return string
     */
    public function getContainerClassName()
    {
        $class = $this->getClassName();
        $class .= $this->showPrefix() ? '-prefix' : '';
        $class .= $this->showMiddlename() ? '-middlename' : '';
        $class .= $this->showSuffix() ? '-suffix' : '';
        return $class;
    }

    /**
     * Retrieve customer or customer address attribute instance
     *
     * @param string $attributeCode
     * @return \Magento\Customer\Service\V1\Dto\Eav\AttributeMetadata|null
     */
    protected function _getAttribute($attributeCode)
    {
        if ($this->getForceUseCustomerAttributes()
            || $this->getObject() instanceof \Magento\Customer\Model\Customer
            || $this->getObject() instanceof Customer) {
            return parent::_getAttribute($attributeCode);
        }

        try {
            $attribute = $this->_attributeMetadata->getAttributeMetadata('customer_address', $attributeCode);
        } catch (\Magento\Exception\NoSuchEntityException $e) {
            return null;
        }

        if ($this->getForceUseCustomerRequiredAttributes() && $attribute && !$attribute->isRequired()) {
            $customerAttribute = parent::_getAttribute($attributeCode);
            if ($customerAttribute && $customerAttribute->isRequired()) {
                $attribute = $customerAttribute;
            }
        }

        return $attribute;
    }

    /**
     * Retrieve store attribute label
     *
     * @param string $attributeCode
     * @return string
     */
    public function getStoreLabel($attributeCode)
    {
        $attribute = $this->_getAttribute($attributeCode);
        return $attribute ? __($attribute->getStoreLabel()) : '';
    }

    /**
     * @param string $attributeCode
     * @return bool
     */
    private function _isAttributeRequired($attributeCode)
    {
        $attributeMetadata = $this->_getAttribute($attributeCode);
        return $attributeMetadata ? (bool)$attributeMetadata->isRequired() : false;
    }

    /**
     * @param string $attributeCode
     * @return bool
     */
    private function _isAttributeVisible($attributeCode)
    {
        $attributeMetadata = $this->_getAttribute($attributeCode);
        return $attributeMetadata ? (bool)$attributeMetadata->isVisible() : false;
    }
}
