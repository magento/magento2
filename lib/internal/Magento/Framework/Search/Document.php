<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search;

/**
 * Search Document
 */
class Document implements \IteratorAggregate
{
    /**
     * Document fields array
     *
     * @var DocumentField[]
     */
    protected $documentFields;

    /**
     * Document Id
     *
     * @var int
     */
    protected $documentId;

    /**
     * @param int $documentId
     * @param DocumentField[] $documentFields
     */
    public function __construct(
        $documentId,
        array $documentFields
    ) {
        $this->documentId = $documentId;
        $this->documentFields = $documentFields;
    }

    /**
     * Implementation of \IteratorAggregate::getIterator()
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->documentFields);
    }

    /**
     * Get Document field
     *
     * @param string $fieldName
     * @return DocumentField
     */
    public function getField($fieldName)
    {
        return array_key_exists($fieldName, $this->documentFields) ? $this->documentFields[$fieldName] : $fieldName;
    }

    /**
     * Get Document field names
     *
     * @return array
     */
    public function getFieldNames()
    {
        return array_keys($this->documentFields);
    }

    /**
     * Get Document Id
     *
     * @return int
     * @codeCoverageIgnore
     */
    public function getId()
    {
        return $this->documentId;
    }
}
