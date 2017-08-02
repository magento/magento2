<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Flat\Action;

/**
 * Class Full reindex action
 *
 * @since 2.0.0
 */
class Full extends \Magento\Catalog\Model\Indexer\Product\Flat\AbstractAction
{
    /**
     * Execute full reindex action
     *
     * @param null|array $ids
     *
     * @return \Magento\Catalog\Model\Indexer\Product\Flat\Action\Full
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function execute($ids = null)
    {
        try {
            foreach ($this->_storeManager->getStores() as $store) {
                $this->_reindex($store->getId());
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
        }
        return $this;
    }
}
