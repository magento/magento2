<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\ResourceModel;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Eav\Api\AttributeRepositoryInterface as AttributeRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Model\Entity\ScopeResolver;
use Magento\Framework\EntityManager\Operation\AttributeInterface;

/**
 * Class UpdateHandler
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.1.0
 */
class UpdateHandler implements AttributeInterface
{
    /**
     * @var AttributeRepository
     * @since 2.1.0
     */
    private $attributeRepository;

    /**
     * @var MetadataPool
     * @since 2.1.0
     */
    private $metadataPool;

    /**
     * @var SearchCriteriaBuilder
     * @since 2.1.0
     */
    private $searchCriteriaBuilder;

    /**
     * @var AttributePersistor
     * @since 2.1.0
     */
    private $attributePersistor;

    /**
     * @var ReadSnapshot
     * @since 2.1.0
     */
    private $readSnapshot;

    /**
     * @var ScopeResolver
     * @since 2.1.0
     */
    private $scopeResolver;

    /**
     * @var ReadHandler
     * @since 2.1.0
     */
    private $readHandler;

    /**
     * @var AttributeLoader
     * @since 2.1.3
     */
    private $attributeLoader;

    /**
     * UpdateHandler constructor.
     * @param AttributeRepository $attributeRepository
     * @param MetadataPool $metadataPool
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AttributePersistor $attributePersistor
     * @param ReadSnapshot $readSnapshot
     * @param ScopeResolver $scopeResolver
     * @param AttributeLoader $attributeLoader
     * @since 2.1.0
     */
    public function __construct(
        AttributeRepository $attributeRepository,
        MetadataPool $metadataPool,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AttributePersistor $attributePersistor,
        ReadSnapshot $readSnapshot,
        ScopeResolver $scopeResolver,
        AttributeLoader $attributeLoader = null
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->metadataPool = $metadataPool;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributePersistor = $attributePersistor;
        $this->readSnapshot = $readSnapshot;
        $this->scopeResolver = $scopeResolver;
        $this->attributeLoader = $attributeLoader ?: ObjectManager::getInstance()->get(AttributeLoader::class);
    }

    /**
     * @param string $entityType
     * @param int $attributeSetId
     * @return \Magento\Eav\Api\Data\AttributeInterface[]
     * @since 2.1.0
     */
    protected function getAttributes($entityType, $attributeSetId = null)
    {
        return $this->attributeLoader->getAttributes($entityType, $attributeSetId);
    }

    /**
     * @param string $entityType
     * @param array $entityData
     * @param array $arguments
     * @return array
     * @throws \Exception
     * @throws \Magento\Framework\Exception\ConfigurationMismatchException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.1.0
     */
    public function execute($entityType, $entityData, $arguments = [])
    {
        /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute */
        $metadata = $this->metadataPool->getMetadata($entityType);
        if ($metadata->getEavEntityType()) {
            $context = $this->scopeResolver->getEntityContext($entityType, $entityData);
            $entityDataForSnapshot = [$metadata->getLinkField() => $entityData[$metadata->getLinkField()]];
            foreach ($context as $scope) {
                if (isset($entityData[$scope->getIdentifier()])) {
                    $entityDataForSnapshot[$scope->getIdentifier()] = $entityData[$scope->getIdentifier()];
                }
            }
            $attributeSetId = isset($entityData[AttributeLoader::ATTRIBUTE_SET_ID])
                ? $entityData[AttributeLoader::ATTRIBUTE_SET_ID]
                : null; // @todo verify is it normal to not have attributer_set_id
            $snapshot = $this->readSnapshot->execute($entityType, $entityDataForSnapshot);
            foreach ($this->getAttributes($entityType, $attributeSetId) as $attribute) {
                $code = $attribute->getAttributeCode();
                $isAllowedValueType = array_key_exists($code, $entityData)
                    && (is_scalar($entityData[$code]) || $entityData[$code] === null);

                if ($attribute->isStatic() || !$isAllowedValueType) {
                    continue;
                }

                $newValue = $entityData[$code];
                $isValueEmpty = $attribute->isValueEmpty($newValue);
                $isAllowedEmptyStringValue = $attribute->isAllowedEmptyTextValue($newValue);

                if (array_key_exists($code, $snapshot)) {
                    $snapshotValue = $snapshot[$code];
                    /**
                     * 'FALSE' value for attributes can't be update or delete
                     */
                    if ($snapshotValue === false) {
                        continue;
                    }

                    if (!$isValueEmpty || $isAllowedEmptyStringValue) {
                        /**
                         * NOT Updated value for attributes not need to update
                         */
                        if ($snapshotValue === $newValue) {
                            continue;
                        }

                        $this->attributePersistor->registerUpdate(
                            $entityType,
                            $entityData[$metadata->getLinkField()],
                            $code,
                            $newValue
                        );
                    } else {
                        $this->attributePersistor->registerDelete(
                            $entityType,
                            $entityData[$metadata->getLinkField()],
                            $code
                        );
                    }
                } else {
                    /**
                     * Only not empty value of attribute is insertable
                     */
                    if (!$isValueEmpty || $isAllowedEmptyStringValue) {
                        $this->attributePersistor->registerInsert(
                            $entityType,
                            $entityData[$metadata->getLinkField()],
                            $code,
                            $newValue
                        );
                    }
                }
            }
            $this->attributePersistor->flush($entityType, $context);
        }

        return $this->getReadHandler()->execute($entityType, $entityData, $arguments);
    }

    /**
     * Get read handler
     *
     * @deprecated 2.1.0
     *
     * @return ReadHandler
     * @since 2.1.0
     */
    protected function getReadHandler()
    {
        if (!$this->readHandler) {
            $this->readHandler = ObjectManager::getInstance()->get(ReadHandler::class);
        }

        return $this->readHandler;
    }
}
