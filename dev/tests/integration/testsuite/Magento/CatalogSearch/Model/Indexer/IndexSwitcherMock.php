<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Indexer;

/**
 * The proxy class around index switcher which allows to ensure that the IndexSwitcher was actually used
 */
class IndexSwitcherMock extends \PHPUnit\Framework\Assert implements IndexSwitcherInterface
{
    private $isSwitched = false;

    /**
     * @var \Magento\CatalogSearch\Model\Indexer\IndexSwitcherInterface
     */
    private $indexSwitcher;

    /**
     * @param \Magento\CatalogSearch\Model\Indexer\IndexSwitcherInterface $indexSwitcher
     */
    public function __construct(
        IndexSwitcherInterface $indexSwitcher
    ) {
        $this->indexSwitcher = $indexSwitcher;
    }

    /**
     * Switch current index with temporary index
     *
     * It will drop current index table and rename temporary index table to the current index table.
     *
     * @param array $dimensions
     * @return void
     */
    public function switchIndex(array $dimensions)
    {
        $this->isSwitched |= true;
        $this->indexSwitcher->switchIndex($dimensions);
    }

    /**
     * @return bool
     */
    public function isSwitched()
    {
        return (bool) $this->isSwitched;
    }
}
