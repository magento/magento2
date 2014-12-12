<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Model\Indexer\Product\Flat\Action;

/**
 * Class Full reindex action
 *
 */
class Full extends \Magento\Catalog\Model\Indexer\Product\Flat\AbstractAction
{
    /**
     * Execute full reindex action
     *
     * @param null|array $ids
     *
     * @return \Magento\Catalog\Model\Indexer\Product\Flat\Action\Full
     * @throws \Magento\Framework\Exception
     * @throws \Exception
     */
    public function execute($ids = null)
    {
        try {
            foreach ($this->_storeManager->getStores() as $store) {
                $this->_reindex($store->getId());
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Model\Exception($e->getMessage(), $e->getCode(), $e);
        }
        return $this;
    }
}
