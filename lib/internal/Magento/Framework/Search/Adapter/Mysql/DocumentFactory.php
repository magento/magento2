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
namespace Magento\Framework\Search\Adapter\Mysql;

/**
 * Document Factory
 */
class DocumentFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManager
     */
    protected $objectManager;
    /**
     * @var \Magento\Framework\Search\EntityMetadata
     */
    private $entityId;

    /**
     * @param \Magento\Framework\ObjectManager $objectManager
     * @param \Magento\Framework\Search\EntityMetadata $entityId
     */
    public function __construct(
        \Magento\Framework\ObjectManager $objectManager,
        \Magento\Framework\Search\EntityMetadata $entityId
    ) {
        $this->objectManager = $objectManager;
        $this->entityId = $entityId;
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
        $entityId = $this->entityId->getEntityId();
        foreach ($rawDocument as $rawField) {
            if ($rawField['name'] == $entityId) {
                $documentId = $rawField['value'];
            } else {
                $fields[] = $this->objectManager->create('\Magento\Framework\Search\DocumentField', $rawField);
            }
        }
        return $this->objectManager->create(
            '\Magento\Framework\Search\Document',
            ['documentFields' => $fields, 'documentId' => $documentId]
        );
    }
}
