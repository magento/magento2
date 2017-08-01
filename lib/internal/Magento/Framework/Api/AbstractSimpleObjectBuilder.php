<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api;

/**
 * Base Builder Class for simple data Objects
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @since 2.0.0
 */
abstract class AbstractSimpleObjectBuilder implements SimpleBuilderInterface
{
    /**
     * @var array
     * @since 2.0.0
     */
    protected $data;

    /**
     * @var ObjectFactory
     * @since 2.0.0
     */
    protected $objectFactory;

    /**
     * @param ObjectFactory $objectFactory
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function create()
    {
        $dataObjectType = $this->_getDataObjectType();
        $dataObject = $this->objectFactory->create($dataObjectType, ['data' => $this->data]);
        $this->data = [];
        return $dataObject;
    }

    /**
     * @param string $key
     * @param mixed $value
     *
     * @return $this
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function _getDataObjectType()
    {
        $currentClass = get_class($this);
        $builderSuffix = 'Builder';
        $dataObjectType = substr($currentClass, 0, -strlen($builderSuffix));
        return $dataObjectType;
    }

    /**
     * Return data Object data.
     *
     * @return array
     * @since 2.0.0
     */
    public function getData()
    {
        return $this->data;
    }
}
