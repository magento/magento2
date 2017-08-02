<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Block\Widget;

use Magento\Customer\Api\CustomerMetadataInterface;

/**
 * Class \Magento\Customer\Block\Widget\AbstractWidget
 *
 * @since 2.0.0
 */
class AbstractWidget extends \Magento\Framework\View\Element\Template
{
    /**
     * @var CustomerMetadataInterface
     * @since 2.0.0
     */
    protected $customerMetadata;

    /**
     * @var \Magento\Customer\Helper\Address
     * @since 2.0.0
     */
    protected $_addressHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Helper\Address $addressHelper
     * @param CustomerMetadataInterface $customerMetadata
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Helper\Address $addressHelper,
        CustomerMetadataInterface $customerMetadata,
        array $data = []
    ) {
        $this->_addressHelper = $addressHelper;
        $this->customerMetadata = $customerMetadata;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * @param string $key
     * @return null|string
     * @since 2.0.0
     */
    public function getConfig($key)
    {
        return $this->_addressHelper->getConfig($key);
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getFieldIdFormat()
    {
        if (!$this->hasData('field_id_format')) {
            $this->setData('field_id_format', '%s');
        }
        return $this->getData('field_id_format');
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getFieldNameFormat()
    {
        if (!$this->hasData('field_name_format')) {
            $this->setData('field_name_format', '%s');
        }
        return $this->getData('field_name_format');
    }

    /**
     * @param string $field
     * @return string
     * @since 2.0.0
     */
    public function getFieldId($field)
    {
        return sprintf($this->getFieldIdFormat(), $field);
    }

    /**
     * @param string $field
     * @return string
     * @since 2.0.0
     */
    public function getFieldName($field)
    {
        return sprintf($this->getFieldNameFormat(), $field);
    }

    /**
     * Retrieve customer attribute instance
     *
     * @param string $attributeCode
     * @return \Magento\Customer\Api\Data\AttributeMetadataInterface|null
     * @since 2.0.0
     */
    protected function _getAttribute($attributeCode)
    {
        try {
            return $this->customerMetadata->getAttributeMetadata($attributeCode);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }
}
