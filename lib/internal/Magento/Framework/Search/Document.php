<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        return $this->documentFields[$fieldName];
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
