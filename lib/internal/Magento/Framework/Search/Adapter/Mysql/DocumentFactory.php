<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql;

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
     */
    private $attributeValueFactory;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Search\EntityMetadata $entityMetadata
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Search\EntityMetadata $entityMetadata
//        \Magento\Framework\Api\AttributeValueFactory $attributeValueFactory
    ) {
        $this->objectManager = $objectManager;
        $this->entityMetadata = $entityMetadata;
//        $this->attributeValueFactory = $attributeValueFactory;
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
                $attributes[$fieldName] = new \Magento\Framework\Api\AttributeValue([
		    \Magento\Framework\Api\AttributeInterface::ATTRIBUTE_CODE => $fieldName,
		    \Magento\Framework\Api\AttributeInterface::VALUE => $rawField['value']
		]);
		 //$this->attributeValueFactory->create()
            }
        }
	return new \Magento\Framework\Api\Search\Document([
	   \Magento\Framework\Api\Search\DocumentInterface::ID => $documentId,
           \Magento\Framework\Api\CustomAttributesDataInterface::CUSTOM_ATTRIBUTES => $attributes
	]);
    }
}
