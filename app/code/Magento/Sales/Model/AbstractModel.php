<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Sales abstract model
 * Provide date processing functionality
 * @method string getEntityType() getEntityType()
 */
abstract class AbstractModel extends AbstractExtensibleModel
{
    /**
     * Raw object data
     *
     * @var array
     */
    protected $rawData = [];

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = []
    ) {
        $this->rawData = $data;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            []
        );
    }

    /**
     * Returns _eventPrefix
     *
     * @return string
     */
    public function getEventPrefix()
    {
        return $this->_eventPrefix;
    }

    /**
     * Returns _eventObject
     *
     * @return string
     */
    public function getEventObject()
    {
        return $this->_eventObject;
    }

    /**
     * Set data by key
     *
     * @param $key
     * @param $value
     */
    protected function _setDataByKey($key, $value)
    {
        if ($this->_idFieldName === $key) {
            $this->setId($value);
            return;
        }
        if (isset($this->_data[$key]) && $this->_data[$key] != $value) {
            $this->rawData[$key] = $value;
            return;
        } else if (!isset($this->_data[$key])) {
            $this->rawData[$key] = $value;
            return;
        }
    }

    /**
     * Set data by key
     *
     * @param array|string $key
     * @param null $value
     * @return $this
     */
    public function setData($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $fieldKey => $fieldValue) {
                $this->_setDataByKey($fieldKey, $fieldValue);
            }
        } else {
            $this->_setDataByKey($key, $value);
        }
        return $this;
    }

    /**
     * Unset data by key
     *
     * @param null $key
     * @return $this
     */
    public function unsetData($key = null)
    {
        if (!isset($this->_data[$key]) || $this->_data[$key] !== null) {
            $this->rawData[$key] = null;
        }
        return $this;
    }

    /**
     * Returns data by key
     *
     * @param string $key
     * @param null $index
     * @return array|null
     */
    public function getData($key = '', $index = null)
    {
        if ('' === $key) {
            return array_merge($this->_data, $this->rawData);
        } else {
            return isset($this->rawData[$key]) ? $this->rawData[$key] :
                (isset($this->_data[$key]) ? $this->_data[$key] : null);
        }
    }

    /**
     * Returns entity Id
     *
     * @return int|null
     */
    public function getId()
    {
        return isset($this->_data[$this->_idFieldName]) ? $this->_data[$this->_idFieldName] : null;
    }

    public function setId($value)
    {
        return $this->_data[$this->_idFieldName] = $value;
    }

    public function hasData($key = '')
    {
        if (empty($key) || !is_string($key)) {
            $data = $this->getData();
            return !empty($data);
        }
        return ($this->getData($key) !== null);
    }

    public function flushDataIntoModel()
    {
        $this->_data = array_merge($this->_data, $this->rawData);
        $this->rawData = [];
    }

    public function hasDataChanges()
    {
        return !empty($this->rawData);
    }
}
