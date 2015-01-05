<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
     * @throws \Magento\Catalog\Exception
     */
    public function execute($ids)
    {
        if (empty($ids)) {
            throw new \Magento\Catalog\Exception(__('Bad value was supplied.'));
        }
        try {
            $this->reindex($ids);
        } catch (\Exception $e) {
            throw new \Magento\Catalog\Exception($e->getMessage(), $e->getCode(), $e);
        }
    }
}
