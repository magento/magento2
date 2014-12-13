<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Indexer\Model\Resource\Mview\View\State;

class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection implements
    \Magento\Framework\Mview\View\State\CollectionInterface
{
    /**
     * Collection initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Indexer\Model\Mview\View\State', 'Magento\Indexer\Model\Resource\Mview\View\State');
    }
}
