<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel;

/**
 * Class Metadata
 * @since 2.0.0
 */
class Metadata
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $resourceClassName;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $modelClassName;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $resourceClassName
     * @param string $modelClassName
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $resourceClassName,
        $modelClassName
    ) {
        $this->objectManager = $objectManager;
        $this->resourceClassName = $resourceClassName;
        $this->modelClassName = $modelClassName;
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     * @since 2.0.0
     */
    public function getMapper()
    {
        return $this->objectManager->get($this->resourceClassName);
    }

    /**
     * @return \Magento\Framework\Api\ExtensibleDataInterface
     * @since 2.0.0
     */
    public function getNewInstance()
    {
        return $this->objectManager->create($this->modelClassName);
    }
}
