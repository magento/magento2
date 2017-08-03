<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Attribute;

/**
 * EAV entity attribute exception
 *
 * @api
 * @since 2.0.0
 */
class Exception extends \Magento\Framework\Exception\LocalizedException
{
    /**
     * Eav entity attribute
     *
     * @var string
     * @since 2.0.0
     */
    protected $_attributeCode;

    /**
     * Eav entity attribute part
     * attribute|backend|frontend|source
     *
     * @var string
     * @since 2.0.0
     */
    protected $_part;

    /**
     * Set Eav entity attribute
     *
     * @param string $attribute
     * @return $this
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function setAttributeCode($attribute)
    {
        $this->_attributeCode = $attribute;
        return $this;
    }

    /**
     * Set Eav entity attribute type
     *
     * @param string $part
     * @return $this
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function setPart($part)
    {
        $this->_part = $part;
        return $this;
    }

    /**
     * Retrieve Eav entity attribute
     *
     * @return string
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getAttributeCode()
    {
        return $this->_attributeCode;
    }

    /**
     * Retrieve Eav entity attribute part
     *
     * @return string
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getPart()
    {
        return $this->_part;
    }
}
