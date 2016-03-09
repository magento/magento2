<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\Operation\Write;

use Magento\Framework\Model\Operation\WriteInterface;
use Magento\Framework\Model\Entity\Action\CreateMain;
use Magento\Framework\Model\Entity\Action\CreateExtension;
use Magento\Framework\Model\Entity\Action\CreateRelation;

/**
 * Class Create
 */
class Create implements WriteInterface
{
    /**
     * @var CreateMain
     */
    protected $createMain;

    /**
     * @var CreateExtension
     */
    protected $createExtension;

    /**
     * @var CreateRelation
     */
    protected $createRelation;

    /**
     * @param CreateMain $createMain
     * @param CreateExtension $createExtension
     * @param CreateRelation $createRelation
     */
    public function __construct(
        CreateMain $createMain,
        CreateExtension $createExtension,
        CreateRelation $createRelation
    ) {
        $this->createMain = $createMain;
        $this->createExtension = $createExtension;
        $this->createRelation = $createRelation;
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @return object
     * @throws \Exception
     */
    public function execute($entityType, $entity)
    {
        $entity = $this->createMain->execute($entityType, $entity);
        $entity = $this->createExtension->execute($entityType, $entity);
        $entity = $this->createRelation->execute($entityType, $entity);
        return $entity;
    }
}
