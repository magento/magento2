<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Model\ResourceModel\Product\Link;

use Magento\Catalog\Model\ProductLink\LinkFactory;
use Magento\Catalog\Model\ResourceModel\Product\Link;
use Magento\Catalog\Model\ResourceModel\Product\Relation;
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
     * Save grouped products to product relation table
     *
     * @param Link $subject
     * @param Link $result
     * @param int $parentId
     * @param array $data
     * @param int $typeId
     * @return Link
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSaveProductLinks(Link $subject, Link $result, $parentId, $data, $typeId)
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
     * Remove grouped products from product relation table
     *
     * @param Link $subject
     * @param \Closure $proceed
     * @param int $linkId
     * @return Link
     */
    public function aroundDeleteProductLink(Link $subject, \Closure $proceed, $linkId)
    {
        /** @var \Magento\Catalog\Model\ProductLink\Link $link */
        $link = $this->linkFactory->create();
        $subject->load($link, $linkId, $subject->getIdFieldName());
        $result = $proceed($linkId);
        if ($link->getLinkTypeId() == GroupedLink::LINK_TYPE_GROUPED) {
            $this->relationProcessor->removeRelations(
                $link->getProductId(),
                $link->getLinkedProductId()
            );
        }
        return $result;
    }
}
