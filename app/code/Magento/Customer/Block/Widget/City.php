<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Customer\Block\Widget;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Helper\Address as AddressHelper;
use Magento\Customer\Model\Options;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template\Context;

/**
 * Widget for showing customer city.
 *
 * @method CustomerInterface getObject()
 * @method Name setObject(CustomerInterface $customer)
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class City extends AbstractWidget
{

    /**
     * the attribute code
     */
    public const ATTRIBUTE_CODE = 'city';

    /**
     * @var AddressMetadataInterface
     */
    private $addressMetadata;

    /**
     * @var Options
     */
    private $options;

    /**
     * @param Context $context
     * @param AddressHelper $addressHelper
     * @param CustomerMetadataInterface $customerMetadata
     * @param Options $options
     * @param AddressMetadataInterface $addressMetadata
     * @param array $data
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
     * @inheritdoc
     */
    public function _construct()
    {
        parent::_construct();

        // default city location
        $this->setTemplate('Magento_Customer::widget/city.phtml');
    }

    /**
     * Can show prefix
     *
     * @return bool
     */
    public function showCity()
    {
        return $this->isAttributeVisible(self::ATTRIBUTE_CODE);
    }

    /**
     * Retrieve customer attribute instance
     *
     * @param string $attributeCode
     *
     * @return AttributeMetadataInterface|null
     * @throws LocalizedException
     */
    //@codingStandardsIgnoreStart
    protected function _getAttribute($attributeCode)
    //@codingStandardsIgnoreEnd
    {
        if ($this->getForceUseCustomerAttributes() || $this->getObject() instanceof CustomerInterface) {
            return parent::_getAttribute($attributeCode);
        }

        try {
            $attribute = $this->addressMetadata->getAttributeMetadata($attributeCode);
        } catch (NoSuchEntityException $e) {
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
     */
    public function getStoreLabel(string $attributeCode)
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
     * @throws LocalizedException
     */
    public function getAttributeValidationClass(string $attributeCode)
    {
        return $this->_addressHelper->getAttributeValidationClass($attributeCode);
    }

    /**
     * Checks is attribute visible
     *
     * @param string $attributeCode
     *
     * @return bool
     */
    private function isAttributeVisible(string $attributeCode)
    {
        $attributeMetadata = $this->_getAttribute($attributeCode);
        return $attributeMetadata ? (bool)$attributeMetadata->isVisible() : false;
    }

    /**
     * Check if city attribute enabled in system
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->_getAttribute(self::ATTRIBUTE_CODE)
            ? (bool)$this->_getAttribute(self::ATTRIBUTE_CODE)->isVisible() : false;
    }

    /**
     * Check if city attribute marked as required
     *
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->_getAttribute(self::ATTRIBUTE_CODE)
            ? (bool)$this->_getAttribute(self::ATTRIBUTE_CODE)->isRequired() : false;
    }
}
