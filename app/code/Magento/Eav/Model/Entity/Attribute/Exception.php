<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Attribute;

/**
 * EAV entity attribute exception
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Exception extends \Exception
{
    /**
     * Eav entity attribute
     *
     * @var string
     */
    protected $_attributeCode;

    /**
     * Eav entity attribute part
     * attribute|backend|frontend|source
     *
     * @var string
     */
    protected $_part;

    /**
     * Set Eav entity attribute
     *
     * @param string $attribute
     * @return $this
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
     */
    public function getAttributeCode()
    {
        return $this->_attributeCode;
    }

    /**
     * Retrieve Eav entity attribute part
     *
     * @return string
     */
    public function getPart()
    {
        return $this->_part;
    }
}
