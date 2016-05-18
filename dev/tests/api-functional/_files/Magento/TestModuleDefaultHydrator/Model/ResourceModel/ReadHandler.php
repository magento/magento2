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
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerExtensionFactory;
use Magento\TestModuleDefaultHydrator\Api\Data\ExtensionAttributeInterface;
use Magento\TestModuleDefaultHydrator\Api\Data\ExtensionAttributeInterfaceFactory as ExtensionAttributeFactory;

class ReadHandler implements ExtensionInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var ExtensionAttributeFactory
     */
    private $extensionAttributeFactory;

    /**
     * @var CustomerExtensionFactory
     */
    private $customerExtensionFactory;

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
     * @param ExtensionAttributeFactory $extensionAttributeFactory
     * @param CustomerExtensionFactory $customerExtensionFactory
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        EntityManager $entityManager,
        ExtensionAttributeFactory $extensionAttributeFactory,
        CustomerExtensionFactory $customerExtensionFactory,
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection
    ) {
        $this->entityManager = $entityManager;
        $this->extensionAttributeFactory = $extensionAttributeFactory;
        $this->customerExtensionFactory = $customerExtensionFactory;
        $this->metadataPool = $metadataPool;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param CustomerInterface $entity
     * @param array $arguments
     * @return CustomerInterface
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $metadata = $this->metadataPool->getMetadata(ExtensionAttributeInterface::class);
        $connection = $this->resourceConnection->getConnectionByName(
            $metadata->getEntityConnectionName()
        );
        $id = $connection->fetchOne(
            $connection->select()
                ->from($metadata->getEntityTable(), [$metadata->getIdentifierField()])
                ->where('customer_id = ?', $entity->getId())
                ->limit(1)
        );
        $extensionAttribute = $this->extensionAttributeFactory->create();
        $extensionAttribute = $this->entityManager->load($extensionAttribute, $id);
        $customerExtension = $this->customerExtensionFactory->create(
            [
                'data' => ['extension_attribute' => $extensionAttribute]
            ]
        );
        $entity->setExtensionAttributes($customerExtension);
        return $entity;
    }
}
