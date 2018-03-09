<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Search\EntityMetadata;
use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\Api\AttributeValue;
use Magento\Framework\Api\CustomAttributesDataInterface;
use Magento\Framework\Api\Search\Document;
use Magento\Framework\Api\Search\DocumentInterface;

/**
 * Document Factory
 * @api
 * @since 100.1.0
 */
class DocumentFactory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     * @deprecated 100.1.0
     * @since 100.1.0
     */
    protected $objectManager;

    /**
     * @var EntityMetadata
     */
    private $entityMetadata;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param EntityMetadata $entityMetadata
     */
    public function __construct(ObjectManagerInterface $objectManager, EntityMetadata $entityMetadata)
    {
        $this->objectManager = $objectManager;
        $this->entityMetadata = $entityMetadata;
    }

    /**
     * Create Search Document instance
     *
     * @param array $rawDocument
     * @return Document
     * @since 100.1.0
     */
    public function create($rawDocument)
    {
        /** @var AttributeValue[] $fields */
        $attributes = [];
        $documentId = null;
        $entityId = $this->entityMetadata->getEntityId();
        foreach ($rawDocument as $fieldName => $value) {
            if ($fieldName === $entityId) {
                $documentId = $value;
            } elseif ($fieldName === '_score') {
                $attributes['score'] = new AttributeValue(
                    [
                        AttributeInterface::ATTRIBUTE_CODE => $fieldName,
                        AttributeInterface::VALUE => $value,
                    ]
                );
            }
        }

        return new Document(
            [
                DocumentInterface::ID => $documentId,
                CustomAttributesDataInterface::CUSTOM_ATTRIBUTES => $attributes,
            ]
        );
    }
}
