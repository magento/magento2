<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Storage;

use Magento\CatalogUrlRewrite\Model\Resource\Category\Product;
use Magento\UrlRewrite\Model\Storage\DbStorage as BaseDbStorage;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

class DbStorage extends BaseDbStorage
{
    /**
     * @param array $data
     * @return \Magento\Framework\DB\Select
     */
    protected function prepareSelect($data)
    {
        $select = $this->connection->select();
        $select->from(['url_rewrite' => $this->resource->getTableName('url_rewrite')])
            ->joinLeft(
                ['relation' => $this->resource->getTableName(Product::TABLE_NAME)],
                'url_rewrite.url_rewrite_id = relation.url_rewrite_id'
            )
            ->where('url_rewrite.entity_id = ?', $data['entity_id'])
            ->where('url_rewrite.entity_type = ?', $data['entity_type'])
            ->where('url_rewrite.store_id = ?', $data['store_id']);
        if (empty($data[UrlRewrite::METADATA]['category_id'])) {
            $select->where('relation.category_id IS NULL');
        } else {
            $select->where('relation.category_id = ?', $data[UrlRewrite::METADATA]['category_id']);
        }
        return $select;
    }
}
