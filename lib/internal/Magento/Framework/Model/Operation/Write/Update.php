<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\Operation\Write;

use Magento\Framework\Model\Operation\WriteInterface;
use Magento\Framework\Model\Entity\MetadataPool;
use Magento\Framework\Model\Entity\Action\UpdateMain;
use Magento\Framework\Model\Entity\Action\UpdateExtension;
use Magento\Framework\Model\Entity\Action\UpdateRelation;

/**
 * Class Update
 */
class Update implements WriteInterface
{
    /**
     * @var UpdateMain
     */
    protected $updateMain;

    /**
     * @var UpdateExtension
     */
    protected $updateExtension;

    /**
     * @var UpdateRelation
     */
    protected $updateRelation;

    /**
     * @param UpdateMain $updateMain
     * @param UpdateExtension $updateExtension
     * @param UpdateRelation $updateRelation
     */
    public function __construct(
        UpdateMain $updateMain,
        UpdateExtension $updateExtension,
        UpdateRelation $updateRelation
    ) {
        $this->updateMain = $updateMain;
        $this->updateExtension = $updateExtension;
        $this->updateRelation = $updateRelation;
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @return object
     * @throws \Exception
     */
    public function execute($entityType, $entity)
    {
        $entity = $this->updateMain->execute($entityType, $entity);
        $entity = $this->updateExtension->execute($entityType, $entity);
        $entity = $this->updateRelation->execute($entityType, $entity);
        return $entity;
    }
}
