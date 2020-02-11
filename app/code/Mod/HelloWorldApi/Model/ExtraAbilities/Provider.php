<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Mod\HelloWorldApi\Model\ExtraAbilities;

use Magento\Framework\EntityManager\MetadataPool;
use Mod\HelloWorldApi\Api\Data\ExtraAbilitiesInterface;
use Mod\HelloWorldApi\Api\ExtraAbilitiesProviderInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\EntityManager;
use Mod\HelloWorldApi\Model\ExtraAbilityFactory;

/**
 * Extra abilities provider class.
 */
class Provider implements ExtraAbilitiesProviderInterface
{
    /** @var  EntityManager */
    private $entityManager;


    /** @var MetadataPool */
    private $metadataPool;

    /** @var  ResourceConnection\ */
    private $resourceConnection;

    /** @var  ExtraAbilityFactory */
    private $extraAbilityFactory;

    /**
     * Provider constructor.
     * @param EntityManager $entityManager
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     * @param ExtraAbilityFactory $extraAbilityFactory
     */
    public function __construct(
        EntityManager $entityManager,
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection,
        ExtraAbilityFactory $extraAbilityFactory
    ) {
        $this->entityManager = $entityManager;
        $this->metadataPool = $metadataPool;
        $this->resourceConnection = $resourceConnection;
        $this->extraAbilityFactory = $extraAbilityFactory;
    }

    /**
     * @inheritdoc
     */
    public function getExtraAbilities(int $customerId): array
    {
        $extraAbilities = [];
        $metadata = $this->metadataPool->getMetadata(ExtraAbilitiesInterface::class);
        $connection = $this->resourceConnection->getConnection();

        $select = $connection
            ->select()
            ->from($metadata->getEntityTable(), ExtraAbilitiesInterface::ABILITY_ID)
            ->where(ExtraAbilitiesInterface::CUSTOMER_ID . ' = ?', (int)$customerId);

        $ids = $connection->fetchCol($select);

        foreach ($ids as $id) {
            $extraAbility = $this->extraAbilityFactory->create();
            $extraAbilities[] = $this->entityManager->load($extraAbility, $id);
        }
        return $extraAbilities;
    }
}
