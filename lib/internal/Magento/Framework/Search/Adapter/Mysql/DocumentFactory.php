<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql;

use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\Api\AttributeValue;
use Magento\Framework\Api\CustomAttributesDataInterface;
use Magento\Framework\Api\Search\Document;
use Magento\Framework\Api\Search\DocumentInterface;

/**
 * Document Factory
 * @api
 * @since 2.0.0
 */
class DocumentFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     * @deprecated 2.1.0
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Search\EntityMetadata
     * @since 2.0.0
     */
    private $entityMetadata;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Search\EntityMetadata $entityMetadata
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Search\EntityMetadata $entityMetadata
    ) {
        $this->entityMetadata = $entityMetadata;
    }

    /**
     * Create Search Document instance
     *
     * @param mixed $rawDocument
     * @return \Magento\Framework\Api\Search\Document
     * @since 2.0.0
     */
    public function create($rawDocument)
    {
        $documentId = null;
        $entityId = $this->entityMetadata->getEntityId();
        $attributes = [];
        foreach ($rawDocument as $fieldName => $value) {
            if ($fieldName === $entityId) {
                $documentId = $value;
            } else {
                $attributes[$fieldName] = new AttributeValue(
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
