<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Model\Storage;

use Magento\CatalogUrlRewrite\Model\ResourceModel\Category\Product;
use Magento\UrlRewrite\Model\Storage\DbStorage as BaseDbStorage;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Class DbStorage
 */
class DbStorage extends BaseDbStorage
{
    /**
<<<<<<< HEAD
     * {@inheritDoc}
=======
     * @inheritDoc
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    protected function prepareSelect(array $data)
    {
        $metadata = [];
<<<<<<< HEAD
        if (array_key_exists(UrlRewrite::METADATA, $data)) {
=======
        if (isset($data[UrlRewrite::METADATA])) {
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            $metadata = $data[UrlRewrite::METADATA];
            unset($data[UrlRewrite::METADATA]);
        }

        $select = $this->connection->select();
<<<<<<< HEAD
        $select->from([
            'url_rewrite' => $this->resource->getTableName(self::TABLE_NAME)
        ]);
        $select->joinLeft(
            ['relation' => $this->resource->getTableName(Product::TABLE_NAME)],
            'url_rewrite.url_rewrite_id = relation.url_rewrite_id'
=======
        $select->from(
            [
                'url_rewrite' => $this->resource->getTableName(self::TABLE_NAME)
            ]
        );
        $select->joinLeft(
            ['relation' => $this->resource->getTableName(Product::TABLE_NAME)],
            'url_rewrite.url_rewrite_id = relation.url_rewrite_id',
            ['relation.category_id', 'relation.product_id']
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        );

        foreach ($data as $column => $value) {
            $select->where('url_rewrite.' . $column . ' IN (?)', $value);
        }
        if (empty($metadata['category_id'])) {
            $select->where('relation.category_id IS NULL');
        } else {
            $select->where(
                'relation.category_id = ?',
                $metadata['category_id']
            );
        }

        return $select;
    }
}
