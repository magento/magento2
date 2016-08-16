<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Model\ResourceModel\Product\Link;

use Magento\Catalog\Model\ProductLink\LinkFactory;
use Magento\Catalog\Model\ResourceModel\Product\Link;
use Magento\Catalog\Model\ResourceModel\Product\Relation;
use Magento\Catalog\Model\ProductLink\Link as ProductLink;
use Magento\GroupedProduct\Model\ResourceModel\Product\Link as GroupedLink;

class RelationPersister
{
    /**
     * @var Relation
     */
    private $relationProcessor;

    /**
     * @var LinkFactory
     */
    private $linkFactory;

    /**
     * @var ProductLink
     */
    private $link;

    /**
     * RelationPersister constructor.
     *
     * @param Relation $relationProcessor
     * @param LinkFactory $linkFactory
     */
    public function __construct(Relation $relationProcessor, LinkFactory $linkFactory)
    {
        $this->relationProcessor = $relationProcessor;
        $this->linkFactory = $linkFactory;
    }

    /**
     * @param Link $subject
     * @param Link $result
     * @param int $parentId
     * @param array $data
     * @param int $typeId
     * @return Link
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSaveProductLinks(Link $subject, Link $result, $parentId, array $data, $typeId)
    {
        if ($typeId == GroupedLink::LINK_TYPE_GROUPED) {
            foreach ($data as $linkData) {
                $this->relationProcessor->addRelation(
                    $parentId,
                    $linkData['product_id']
                );
            }
        }
        return $result;
    }

    /**
     * @param Link $subject
     * @param int $linkId
     * @return array
     */
    public function beforeDeleteProductLink(Link $subject, $linkId)
    {
        $this->link = $this->linkFactory->create();
        $subject->load($this->link, $linkId, $subject->getIdFieldName());
        return [$linkId];
    }

    /**
     * @param Link $subject
     * @param int $result
     * @return int
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDeleteProductLink(Link $subject, $result)
    {
        if ($this->link->getLinkTypeId() == GroupedLink::LINK_TYPE_GROUPED) {
            $this->relationProcessor->removeRelations(
                $this->link->getProductId(),
                $this->link->getLinkedProductId()
            );
        }
        return $result;
    }
}
