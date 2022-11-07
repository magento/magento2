<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api;

/**
 * Base Builder Class for simple data Objects
 * @deprecated 103.0.0 Every builder should have their own implementation of \Magento\Framework\Api\SimpleBuilderInterface
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class AbstractSimpleObjectBuilder implements SimpleBuilderInterface
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @var ObjectFactory
     */
    protected $objectFactory;

    /**
     * @param ObjectFactory $objectFactory
     */
    public function __construct(ObjectFactory $objectFactory)
    {
        $this->data = [];
        $this->objectFactory = $objectFactory;
    }

    /**
     * Builds the Data Object
     *
     * @return AbstractSimpleObject
     */
    public function create()
    {
        $dataObjectType = $this->_getDataObjectType();
        $dataObject = $this->objectFactory->create($dataObjectType, ['data' => $this->data]);
        $this->data = [];
        return $dataObject;
    }

    /**
     * Overwrite data in Object.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return $this
     */
    protected function _set($key, $value)
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * Return the Data type class name
     *
     * @return string
     */
    protected function _getDataObjectType()
    {
        $dataObjectType = '';
        $pattern = '/(?<data_object>.*?)Builder(\\\\Interceptor)?/';
        if (preg_match($pattern, get_class($this), $match)) {
            $dataObjectType = $match['data_object'];
        }

        return $dataObjectType;
    }

    /**
     * Return data Object data.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}
