<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel;

/**
 * Class Metadata
 */
class Metadata
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var string
     */
    protected $resourceClassName;

    /**
     * @var string
     */
    protected $modelClassName;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $resourceClassName
     * @param string $modelClassName
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
     */
    public function getMapper()
    {
        return $this->objectManager->get($this->resourceClassName);
    }

    /**
     * @return \Magento\Framework\Api\ExtensibleDataInterface
     */
    public function getNewInstance()
    {
        return $this->objectManager->create($this->modelClassName);
    }
}
