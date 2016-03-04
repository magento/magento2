<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\Operation;

use Magento\Framework\Model\Entity\MetadataPool;
use Magento\Framework\Model\Entity\Action\ReadMain;
use Magento\Framework\Model\Entity\Action\ReadExtension;
use Magento\Framework\Model\Entity\Action\ReadRelation;

/**
 * Class Read
 */
class Read implements ReadInterface
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var ReadMain
     */
    protected $readMain;

    /**
     * @var ReadExtension
     */
    protected $readExtension;

    /**
     * @var ReadRelation
     */
    protected $readRelation;

    /**
     * @param MetadataPool $metadataPool
     * @param ReadMain $readMain
     * @param ReadExtension $readExtension
     * @param ReadRelation $readRelation
     */
    public function __construct(
        MetadataPool $metadataPool,
        ReadMain $readMain,
        ReadExtension $readExtension,
        ReadRelation $readRelation
    ) {
        $this->metadataPool = $metadataPool;
        $this->readMain = $readMain;
        $this->readExtension = $readExtension;
        $this->readRelation = $readRelation;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($entityType, $entity, $identifier)
    {
        $metadata = $this->metadataPool->getMetadata($entityType);

        $hydrator = $this->metadataPool->getHydrator($entityType);
        $entity = $this->readMain->execute($entityType, $entity, $identifier);

        $entityData = $hydrator->extract($entity);
        if (isset($entityData[$metadata->getLinkField()])) {
            $entity = $this->readExtension->execute($entityType, $entity);
            $entity = $this->readRelation->execute($entityType, $entity);
        }

        return $entity;
    }
}
