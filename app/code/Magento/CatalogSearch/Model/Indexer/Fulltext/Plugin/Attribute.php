<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin;

use Magento\CatalogSearch\Model\Indexer\Fulltext;

class Attribute extends AbstractPlugin
{
    /**
     * Invalidate indexer on attribute save (searchable flag change)
     *
     * @param \Magento\Catalog\Model\Resource\Attribute $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\Model\AbstractModel $attribute
     *
     * @return \Magento\Catalog\Model\Resource\Attribute
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(
        \Magento\Catalog\Model\Resource\Attribute $subject,
        \Closure $proceed,
        \Magento\Framework\Model\AbstractModel $attribute
    ) {
        $needInvalidation = (
                $attribute->dataHasChangedFor('is_searchable')
                || $attribute->dataHasChangedFor('is_filterable')
                || $attribute->dataHasChangedFor('is_visible_in_advanced_search')
            ) && !$attribute->isObjectNew();

        $result = $proceed($attribute);
        if ($needInvalidation) {
            $this->indexerRegistry->get(Fulltext::INDEXER_ID)->invalidate();
        }

        return $result;
    }

    /**
     * Invalidate indexer on searchable attribute delete
     *
     * @param \Magento\Catalog\Model\Resource\Attribute $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\Model\AbstractModel $attribute
     *
     * @return \Magento\Catalog\Model\Resource\Attribute
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundDelete(
        \Magento\Catalog\Model\Resource\Attribute $subject,
        \Closure $proceed,
        \Magento\Framework\Model\AbstractModel $attribute
    ) {
        $needInvalidation = !$attribute->isObjectNew() && $attribute->getIsSearchable();
        $result = $proceed($attribute);
        if ($needInvalidation) {
            $this->indexerRegistry->get(Fulltext::INDEXER_ID)->invalidate();
        }

        return $result;
    }
}
