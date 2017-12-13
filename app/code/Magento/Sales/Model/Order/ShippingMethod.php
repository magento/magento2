<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

/**
 * Value object for shipping_method order attribute
 */
class ShippingMethod extends \Magento\Framework\DataObject
{
    /**
     * @var
     */
    private $carrierCode;
    /**
     * @var
     */
    private $method;

    public function __construct(string $carrierCode, string $method)
    {
        $this->carrierCode = $carrierCode;
        $this->method = $method;
    }

    /**
     * Shipping method as in shipping_method order attribute
     *
     * @param string $fullShippingMethod
     * @return ShippingMethod
     */
    public static function fromFullShippingMethodCode(string $fullShippingMethod) : ShippingMethod
    {
        list($carrierCode, $method) = explode('_', $fullShippingMethod, 2);
        return new self($carrierCode, $method);
    }

    /**
     * Shipping method as in shipping_method order attribute
     *
     * @return string
     */
    public function __toString() : string
    {
        return "{$this->carrierCode}_{$this->method}";
    }

    /**
     * Returns carrier code
     *
     * @return string
     */
    public function getCarrierCode() : string
    {
        return $this->carrierCode;
    }

    /**
     * Returns shipping method code without carrier
     *
     * @return string
     */
    public function getMethod() : string
    {
        return $this->method;
    }

    /**
     * Changes carrier code
     *
     * @deprecated The value object should be immutable.
     * @param string $code
     * @return ShippingMethod
     */
    public function setCarrierCode(string $code) : ShippingMethod
    {
        $this->carrierCode = $code;
        return $this;
    }

    /**
     * Changes method code
     *
     * @deprecated The value object should be immutable.
     * @param string $medhod
     * @return ShippingMethod
     */
    public function setMethod(string $medhod) : ShippingMethod
    {
        $this->method = $medhod;
        return $this;
    }

    /**
     * @inheritDoc
     * @deprecated DataObject inheritance will be removed
     */
    public function addData(array $arr)
    {
        return parent::addData($arr);
    }

    /**
     * @inheritDoc
     * @deprecated DataObject inheritance will be removed
     */
    public function setData($key, $value = null)
    {
        return parent::setData($key, $value);
    }

    /**
     * @inheritDoc
     * @deprecated DataObject inheritance will be removed
     */
    public function unsetData($key = null)
    {
        return parent::unsetData($key);
    }

    /**
     * @inheritDoc
     * @deprecated DataObject inheritance will be removed
     */
    public function getData($key = '', $index = null)
    {
        return parent::getData($key, $index);
    }

    /**
     * @inheritDoc
     * @deprecated DataObject inheritance will be removed
     */
    public function getDataByPath($path)
    {
        return parent::getDataByPath($path);
    }

    /**
     * @inheritDoc
     * @deprecated DataObject inheritance will be removed
     */
    public function getDataByKey($key)
    {
        return parent::getDataByKey($key);
    }

    /**
     * @inheritDoc
     * @deprecated DataObject inheritance will be removed
     */
    public function setDataUsingMethod($key, $args = [])
    {
        return parent::setDataUsingMethod($key, $args);
    }

    /**
     * @inheritDoc
     * @deprecated DataObject inheritance will be removed
     */
    public function getDataUsingMethod($key, $args = null)
    {
        return parent::getDataUsingMethod($key, $args);
    }

    /**
     * @inheritDoc
     * @deprecated DataObject inheritance will be removed
     */
    public function hasData($key = '')
    {
        return parent::hasData($key);
    }

    /**
     * @inheritDoc
     * @deprecated DataObject inheritance will be removed
     */
    public function toArray(array $keys = [])
    {
        return parent::toArray($keys);
    }

    /**
     * @inheritDoc
     * @deprecated DataObject inheritance will be removed
     */
    public function convertToArray(array $keys = [])
    {
        return parent::convertToArray($keys);
    }

    /**
     * @inheritDoc
     * @deprecated DataObject inheritance will be removed
     */
    public function toXml(array $keys = [], $rootName = 'item', $addOpenTag = false, $addCdata = true)
    {
        return parent::toXml($keys, $rootName, $addOpenTag, $addCdata);
    }

    /**
     * @inheritDoc
     * @deprecated DataObject inheritance will be removed
     */
    public function convertToXml(
        array $arrAttributes = [],
        $rootName = 'item',
        $addOpenTag = false,
        $addCdata = true
    ) {
        return parent::convertToXml(
            $arrAttributes,
            $rootName,
            $addOpenTag,
            $addCdata
        );
    }

    /**
     * @inheritDoc
     * @deprecated DataObject inheritance will be removed
     */
    public function toJson(array $keys = [])
    {
        return parent::toJson($keys);
    }

    /**
     * @inheritDoc
     * @deprecated DataObject inheritance will be removed
     */
    public function convertToJson(array $keys = [])
    {
        return parent::convertToJson($keys);
    }

    /**
     * @inheritDoc
     * @deprecated DataObject inheritance will be removed
     */
    public function toString($format = '')
    {
        return parent::toString($format);
    }

    /**
     * @inheritDoc
     * @deprecated DataObject inheritance will be removed
     */
    public function __call($method, $args)
    {
        return parent::__call($method, $args);
    }

    /**
     * @inheritDoc
     * @deprecated DataObject inheritance will be removed
     */
    public function isEmpty()
    {
        return parent::isEmpty();
    }

    /**
     * @inheritDoc
     * @deprecated DataObject inheritance will be removed
     */
    public function serialize($keys = [], $valueSeparator = '=', $fieldSeparator = ' ', $quote = '"')
    {
        return parent::serialize(
            $keys,
            $valueSeparator,
            $fieldSeparator,
            $quote
        );
    }

    /**
     * @inheritDoc
     * @deprecated DataObject inheritance will be removed
     */
    public function debug($data = null, &$objects = [])
    {
        return parent::debug($data, $objects);
    }

    /**
     * @inheritDoc
     * @deprecated DataObject inheritance will be removed
     */
    public function offsetSet($offset, $value)
    {
        parent::offsetSet($offset, $value);
    }

    /**
     * @inheritDoc
     * @deprecated DataObject inheritance will be removed
     */
    public function offsetExists($offset)
    {
        return parent::offsetExists($offset);
    }

    /**
     * @inheritDoc
     * @deprecated DataObject inheritance will be removed
     */
    public function offsetUnset($offset)
    {
        parent::offsetUnset($offset);
    }

    /**
     * @inheritDoc
     * @deprecated DataObject inheritance will be removed
     */
    public function offsetGet($offset)
    {
        return parent::offsetGet($offset);
    }


}
