<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Model\ResourceModel\Product\Link;

use Magento\Catalog\Model\ProductLink\LinkFactory;
use Magento\Catalog\Model\ResourceModel\Product\Link;
use Magento\Catalog\Model\ResourceModel\Product\Relation;
use Magento\Framework\Exception\LocalizedException;
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
     * @param Link $result
     * @param int $linkId
     * @return Link
     * @throws LocalizedException
     */
    public function afterDeleteProductLink(Link $subject, Link $result, $linkId)
    {
        /** @var \Magento\Catalog\Model\ProductLink\Link $link */
        $link = $this->linkFactory->create();
        $subject->load($link, $linkId, $subject->getIdFieldName());
        if ($link->getLinkTypeId() == GroupedLink::LINK_TYPE_GROUPED) {
            $this->relationProcessor->removeRelations(
                $link->getProductId(),
                $link->getLinkedProductId()
            );
        }
        return $result;
    }
}
