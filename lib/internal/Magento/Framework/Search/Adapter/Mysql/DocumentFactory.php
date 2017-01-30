<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Search\EntityMetadata
     */
    private $entityMetadata;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Search\EntityMetadata $entityMetadata
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Search\EntityMetadata $entityMetadata
    ) {
        $this->objectManager = $objectManager;
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
        /** @var \Magento\Framework\Search\DocumentField[] $fields */
        $fields = [];
        $documentId = null;
        $entityId = $this->entityMetadata->getEntityId();
        foreach ($rawDocument as $rawField) {
            $fieldName = $rawField['name'];
            if ($fieldName === $entityId) {
                $documentId = $rawField['value'];
            } else {
                $fields[$fieldName] = $this->objectManager->create('Magento\Framework\Search\DocumentField', $rawField);
            }
        }
        return $this->objectManager->create(
            'Magento\Framework\Search\Document',
            ['documentFields' => $fields, 'documentId' => $documentId]
        );
    }
}
