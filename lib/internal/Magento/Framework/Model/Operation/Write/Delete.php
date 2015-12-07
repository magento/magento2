<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\Operation\Write;

use Magento\Framework\Model\Operation\WriteInterface;
use Magento\Framework\Model\Entity\MetadataPool;
use Magento\Framework\Model\Entity\Action\DeleteMain;
use Magento\Framework\Model\Entity\Action\DeleteExtension;
use Magento\Framework\Model\Entity\Action\DeleteRelation;

/**
 * Class Delete
 */
class Delete implements WriteInterface
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var DeleteMain
     */
    protected $deleteMain;

    /**
     * @var DeleteExtension
     */
    protected $deleteExtension;

    /**
     * @var DeleteRelation
     */
    protected $deleteRelation;

    /**
     * @param MetadataPool $metadataPool
     * @param DeleteMain $deleteMain
     * @param DeleteExtension $deleteExtension
     * @param DeleteRelation $deleteRelation
     */
    public function __construct(
        MetadataPool $metadataPool,
        DeleteMain $deleteMain,
        DeleteExtension $deleteExtension,
        DeleteRelation $deleteRelation
    ) {
        $this->metadataPool = $metadataPool;
        $this->deleteMain = $deleteMain;
        $this->deleteExtension = $deleteExtension;
        $this->deleteRelation = $deleteRelation;
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @return true
     * @throws \Exception
     */
    public function execute($entityType, $entity)
    {
        $this->deleteRelation->execute($entity, $entity);
        $this->deleteExtension->execute($entity, $entity);
        $this->deleteMain->execute($entityType, $entity);
        return true;
    }
}
