<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Model\EntitySnapshot;

use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\ObjectManagerInterface as ObjectManager;

/**
 * Class EntitySnapshot
 */
class AttributeProvider implements AttributeProviderInterface
{
    /**
     * @var AttributeProviderInterface[]
     */
    protected $providers;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var array
     */
    protected $registry;

    /**
     * @param MetadataPool $metadataPool
     * @param ObjectManager $objectManager
     * @param array $providers
     */
    public function __construct(
        MetadataPool $metadataPool,
        ObjectManager $objectManager,
        $providers = []
    ) {
        $this->metadataPool = $metadataPool;
        $this->objectManager = $objectManager;
        $this->providers = $providers;
    }

    /**
     * Returns array of fields
     *
     * @param string $entityType
     * @return string[]
     * @throws \Exception
     */
    public function getAttributes($entityType)
    {
        if (!isset($this->registry[$entityType])) {
            $metadata = $this->metadataPool->getMetadata($entityType);
            $entityDescription = $metadata->getEntityConnection()->describeTable($metadata->getEntityTable());
            if ($metadata->getLinkField() !== $metadata->getIdentifierField()) {
                unset($entityDescription[$metadata->getLinkField()]);
            }
            $attributes = [];
            $attributes[] = \array_keys($entityDescription);

            $providers = $this->providers[$entityType] ?? $this->providers['default'] ?? [];
            foreach ($providers as $providerClass) {
                $provider = $this->objectManager->get($providerClass);
                $attributes[] = $provider->getAttributes($entityType);
            }

            $this->registry[$entityType] = \array_merge(...$attributes);
        }

        return $this->registry[$entityType];
    }
}
