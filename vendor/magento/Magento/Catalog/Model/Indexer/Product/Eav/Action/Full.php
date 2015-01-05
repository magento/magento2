<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
     * @throws \Magento\Catalog\Exception
     */
    public function execute($ids = null)
    {
        try {
            $this->reindex();
        } catch (\Exception $e) {
            throw new \Magento\Catalog\Exception($e->getMessage(), $e->getCode(), $e);
        }
    }
}
