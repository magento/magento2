<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Storage;

use Magento\CatalogUrlRewrite\Model\ResourceModel\Category\Product;
use Magento\UrlRewrite\Model\Storage\DbStorage as BaseDbStorage;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

class DbStorage extends BaseDbStorage
{
    /**
     * @param array $data
     * @return \Magento\Framework\DB\Select
     */
    protected function prepareSelect(array $data)
    {
        if (
            !array_key_exists(UrlRewrite::ENTITY_ID, $data)
            ||
            !array_key_exists(UrlRewrite::ENTITY_TYPE, $data)
            ||
            !array_key_exists(UrlRewrite::STORE_ID, $data)
        ) {
            throw new \InvalidArgumentException(
                UrlRewrite::ENTITY_ID . ', ' . UrlRewrite::ENTITY_TYPE
                . ' and ' . UrlRewrite::STORE_ID . ' parameters are required.'
            );
        }

        $select = $this->connection->select();
        $select->from(['url_rewrite' => $this->resource->getTableName('url_rewrite')])
            ->joinLeft(
                ['relation' => $this->resource->getTableName(Product::TABLE_NAME)],
                'url_rewrite.url_rewrite_id = relation.url_rewrite_id'
            )
            ->where(
                'url_rewrite.entity_id IN (?)',
                $data[UrlRewrite::ENTITY_ID]
            )
            ->where(
                'url_rewrite.entity_type = ?',
                $data[UrlRewrite::ENTITY_TYPE]
            )
            ->where('url_rewrite.store_id IN (?)', $data[UrlRewrite::STORE_ID]);
        if (array_key_exists(UrlRewrite::REDIRECT_TYPE, $data)) {
            $select->where(
                'url_rewrite.redirect_type = ?',
                $data[UrlRewrite::REDIRECT_TYPE]
            );
        }
        if (empty($data[UrlRewrite::METADATA]['category_id'])) {
            $select->where('relation.category_id IS NULL');
        } else {
            $select->where('relation.category_id = ?', $data[UrlRewrite::METADATA]['category_id']);
        }
        return $select;
    }
}
