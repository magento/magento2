<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Eav\Action;

/**
 * Class Rows reindex action for mass actions
 */
class Rows extends \Magento\Catalog\Model\Indexer\Product\Eav\AbstractAction
{
    /**
     * Execute Rows reindex
     *
     * @param array $ids
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute($ids)
    {
        if (empty($ids)) {
            throw new \Magento\Framework\Exception\InputException(__('Bad value was supplied.'));
        }
        try {
            $this->reindex($ids);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
        }
    }
}
