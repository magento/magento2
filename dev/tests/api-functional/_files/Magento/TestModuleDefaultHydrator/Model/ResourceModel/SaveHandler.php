<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModuleDefaultHydrator\Model\ResourceModel;

use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\TestModuleDefaultHydrator\Api\Data\ExtensionAttributeInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\EntityManager\EntityManager;

class SaveHandler implements ExtensionInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(
        EntityManager $entityManager
    ) {
        $this->entityManager = $entityManager;
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
        $extensionAttribute = $entity->getExtensionAttributes()->getExtensionAttribute();
        $extensionAttribute->setCustomerId($entity->getId());
        $extensionAttribute = $this->entityManager->save($extensionAttribute);
        $entity->getExtensionAttributes()->setExtensionAttribute($extensionAttribute);
        return $entity;
    }
}
