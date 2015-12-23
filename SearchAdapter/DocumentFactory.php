<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Search\EntityMetadata;
use Magento\Framework\Search\Document;
use Magento\Framework\Search\DocumentField;

/**
 * Document Factory
 */
class DocumentFactory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
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
     */
    public function create($rawDocument)
    {
        /** @var DocumentField[] $fields */
        $fields = [];
        $documentId = null;
        $entityId = $this->entityMetadata->getEntityId();
        foreach ($rawDocument as $fieldName => $value) {
            if ($fieldName === $entityId) {
                $documentId = $value;
            } elseif ($fieldName === '_score') {
                $fields['score'] = $this->objectManager->create(
                    'Magento\Framework\Search\DocumentField',
                    ['name' => 'score', 'value' => $value]
                );
            }
        }

        return $this->objectManager->create(
            'Magento\Framework\Search\Document',
            [
                'documentId' => $documentId,
                'documentFields' => $fields
            ]
        );
    }
}
