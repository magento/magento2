<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModuleDefaultHydrator\Model\ResourceModel;

use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\App\ResourceConnection;

class ReadHandler implements ExtensionInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(
        EntityManager $entityManager,
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection
    ) {
        $this->entityManager = $entityManager;
        $this->metadataPool = $metadataPool;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return array
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $metadata = $this->metadataPool->getMetadata(
            \Magento\TestModuleDefaultHydrator\Api\Data\ExtensionAttributeInterface::class
        );
        $connection = $this->resourceConnection->getConnectionByName(
            $metadata->getEntityConnectionName()
        );

        $id = $connection->fetchOne(
            $connection->select()
                ->from($metadata->getEntityTable(), [$metadata->getIdentifierField()])
                ->where('customer_id = ?', $entity->getId())
                ->limit(1)
        );

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /** @var \Magento\TestModuleDefaultHydrator\Api\Data\ExtensionAttributeInterface $extensionAttribute */
        $extensionAttribute = $objectManager->create(
            \Magento\TestModuleDefaultHydrator\Api\Data\ExtensionAttributeInterface::class
        );
        $extensionAttribute = $this->entityManager->load($extensionAttribute, $id);

        /** @var \Magento\Customer\Api\Data\CustomerExtensionInterface $customerExtension */
        $customerExtension = $objectManager->create(\Magento\Customer\Api\Data\CustomerExtension::class);
        $customerExtension->setExtensionAttribute($extensionAttribute);

        $entity->setExtensionAttributes($customerExtension);
        return $entity;
    }
}
