<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Block\Widget;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Helper\Address as AddressHelper;
use Magento\Customer\Model\Options;
use Magento\Framework\View\Element\Template\Context;

/**
 * Widget for showing customer company.
 *
 * @method CustomerInterface getObject()
 * @method Name setObject(CustomerInterface $customer)
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @since 2.2.0
 */
class Fax extends AbstractWidget
{

    /**
     * the attribute code
     */
    const ATTRIBUTE_CODE = 'fax';

    /**
     * @var AddressMetadataInterface
     * @since 2.2.0
     */
    protected $addressMetadata;

    /**
     * @var Options
     * @since 2.2.0
     */
    protected $options;

    /**
     * @param Context                   $context
     * @param AddressHelper             $addressHelper
     * @param CustomerMetadataInterface $customerMetadata
     * @param Options                   $options
     * @param AddressMetadataInterface  $addressMetadata
     * @param array                     $data
     * @since 2.2.0
     */
    public function __construct(
        Context $context,
        AddressHelper $addressHelper,
        CustomerMetadataInterface $customerMetadata,
        Options $options,
        AddressMetadataInterface $addressMetadata,
        array $data = []
    ) {
        $this->options = $options;
        parent::__construct($context, $addressHelper, $customerMetadata, $data);
        $this->addressMetadata = $addressMetadata;
        $this->_isScopePrivate = true;
    }

    /**
     * @return void
     * @since 2.2.0
     */
    public function _construct()
    {
        parent::_construct();

        // default template location
        $this->setTemplate('widget/fax.phtml');
    }

    /**
     * Can show config value
     *
     * @param string $key
     *
     * @return bool
     * @since 2.2.0
     */
    protected function _showConfig($key)
    {
        return (bool)$this->getConfig($key);
    }

    /**
     * Can show prefix
     *
     * @return bool
     * @since 2.2.0
     */
    public function showFax()
    {
        return $this->_isAttributeVisible(self::ATTRIBUTE_CODE);
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    protected function _getAttribute($attributeCode)
    {
        if ($this->getForceUseCustomerAttributes() || $this->getObject() instanceof CustomerInterface) {
            return parent::_getAttribute($attributeCode);
        }

        try {
            $attribute = $this->addressMetadata->getAttributeMetadata($attributeCode);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
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
     *
     * @return string
     * @since 2.2.0
     */
    public function getStoreLabel($attributeCode)
    {
        $attribute = $this->_getAttribute($attributeCode);
        return $attribute ? __($attribute->getStoreLabel()) : '';
    }

    /**
     * Get string with frontend validation classes for attribute
     *
     * @param string $attributeCode
     *
     * @return string
     * @since 2.2.0
     */
    public function getAttributeValidationClass($attributeCode)
    {
        return $this->_addressHelper->getAttributeValidationClass($attributeCode);
    }

    /**
     * @param string $attributeCode
     *
     * @return bool
     * @since 2.2.0
     */
    private function _isAttributeVisible($attributeCode)
    {
        $attributeMetadata = $this->_getAttribute($attributeCode);
        return $attributeMetadata ? (bool)$attributeMetadata->isVisible() : false;
    }

    /**
     * Check if company attribute enabled in system
     *
     * @return bool
     * @since 2.2.0
     */
    public function isEnabled()
    {
        return $this->_getAttribute(self::ATTRIBUTE_CODE) ? (bool)$this->_getAttribute(self::ATTRIBUTE_CODE)->isVisible(
        ) : false;
    }

    /**
     * Check if company attribute marked as required
     *
     * @return bool
     * @since 2.2.0
     */
    public function isRequired()
    {
        return $this->_getAttribute(self::ATTRIBUTE_CODE) ? (bool)$this->_getAttribute(self::ATTRIBUTE_CODE)
            ->isRequired() : false;
    }
}
