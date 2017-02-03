<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Eav\Action;

/**
 * Class Full reindex action
 */
class Full extends \Magento\Catalog\Model\Indexer\Product\Eav\AbstractAction
{
    /**
     * Execute Full reindex
     *
     * @param array|int|null $ids
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($ids = null)
    {
        try {
            $this->reindex();
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
        }
    }
}
