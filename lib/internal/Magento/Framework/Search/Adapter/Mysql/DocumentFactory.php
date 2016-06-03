<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
 */
class DocumentFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     * @deprecated
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Search\EntityMetadata
     */
    private $entityMetadata;

    /**
     * @var \Magento\Framework\Api\AttributeValueFactory
     * @deprecated
     */
    private $attributeValueFactory;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Search\EntityMetadata $entityMetadata
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Search\EntityMetadata $entityMetadata,
        \Magento\Framework\Api\AttributeValueFactory $attributeValueFactory
    ) {
        $this->entityMetadata = $entityMetadata;
    }

    /**
     * Create Search Document instance
     *
     * @param mixed $rawDocument
     * @return \Magento\Framework\Search\Document
     */
    public function create($rawDocument)
    {
        $documentId = null;
        $entityId = $this->entityMetadata->getEntityId();
        $attributes = [];
        foreach ($rawDocument as $rawField) {
            $fieldName = $rawField['name'];
            if ($fieldName === $entityId) {
                $documentId = $rawField['value'];
            } else {
                $attributes[$fieldName] = new AttributeValue(
                    [
                        AttributeInterface::ATTRIBUTE_CODE => $fieldName,
                        AttributeInterface::VALUE => $rawField['value'],
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
