<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\Data;

/**
 * Customer attribute metadata class.
 */
class AttributeMetadata extends \Magento\Framework\Api\AbstractExtensibleObject implements
    \Magento\Customer\Api\Data\AttributeMetadataInterface
{
    /**
     * {@inheritdoc}
     */
    public function getAttributeCode()
    {
        return $this->_get(self::ATTRIBUTE_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function getFrontendInput()
    {
        return $this->_get(self::FRONTEND_INPUT);
    }

    /**
     * {@inheritdoc}
     */
    public function getInputFilter()
    {
        return $this->_get(self::INPUT_FILTER);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreLabel()
    {
        return $this->_get(self::STORE_LABEL);
    }

    /**
     * {@inheritdoc}
     */
    public function getValidationRules()
    {
        return $this->_get(self::VALIDATION_RULES);
    }

    /**
     * {@inheritdoc}
     */
    public function getMultilineCount()
    {
        return $this->_get(self::MULTILINE_COUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function isVisible()
    {
        return $this->_get(self::VISIBLE);
    }

    /**
     * {@inheritdoc}
     */
    public function isRequired()
    {
        return $this->_get(self::REQUIRED);
    }

    /**
     * {@inheritdoc}
     */
    public function getDataModel()
    {
        return $this->_get(self::DATA_MODEL);
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return $this->_get(self::OPTIONS);
    }

    /**
     * {@inheritdoc}
     */
    public function getFrontendClass()
    {
        return $this->_get(self::FRONTEND_CLASS);
    }

    /**
     * {@inheritdoc}
     */
    public function isUserDefined()
    {
        return $this->_get(self::USER_DEFINED);
    }

    /**
     * {@inheritdoc}
     */
    public function getSortOrder()
    {
        return $this->_get(self::SORT_ORDER);
    }

    /**
     * {@inheritdoc}
     */
    public function getFrontendLabel()
    {
        return $this->_get(self::FRONTEND_LABEL);
    }

    /**
     * {@inheritdoc}
     */
    public function getNote()
    {
        return $this->_get(self::NOTE);
    }

    /**
     * {@inheritdoc}
     */
    public function isSystem()
    {
        return $this->_get(self::SYSTEM);
    }

    /**
     * {@inheritdoc}
     */
    public function getBackendType()
    {
        return $this->_get(self::BACKEND_TYPE);
    }
}
