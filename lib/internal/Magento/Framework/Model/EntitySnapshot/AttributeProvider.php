<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\EntitySnapshot;

use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\ObjectManagerInterface as ObjectManager;

/**
 * Class EntitySnapshot
 * @since 2.1.0
 */
class AttributeProvider implements AttributeProviderInterface
{
    /**
     * @var AttributeProviderInterface[]
     * @since 2.1.0
     */
    protected $providers;

    /**
     * @var MetadataPool
     * @since 2.1.0
     */
    protected $metadataPool;

    /**
     * @var ObjectManager
     * @since 2.1.0
     */
    protected $objectManager;

    /**
     * @var array
     * @since 2.1.0
     */
    protected $registry;

    /**
     * @param MetadataPool $metadataPool
     * @param ObjectManager $objectManager
     * @param array $providers
     * @since 2.1.0
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
     * @return array
     * @throws \Exception
     * @since 2.1.0
     */
    public function getAttributes($entityType)
    {
        if (!isset($this->registry[$entityType])) {
            $metadata = $this->metadataPool->getMetadata($entityType);
            $this->registry[$entityType] = $metadata->getEntityConnection()->describeTable($metadata->getEntityTable());
            if ($metadata->getLinkField() != $metadata->getIdentifierField()) {
                unset($this->registry[$entityType][$metadata->getLinkField()]);
            }
            $providers = [];
            if (isset($this->providers[$entityType])) {
                $providers = $this->providers[$entityType];
            } elseif (isset($this->providers['default'])) {
                $providers = $this->providers['default'];
            }
            foreach ($providers as $providerClass) {
                $provider = $this->objectManager->get($providerClass);
                $this->registry[$entityType] = array_merge(
                    $this->registry[$entityType],
                    $provider->getAttributes($entityType)
                );
            }
        }
        return $this->registry[$entityType];
    }
}
