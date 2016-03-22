<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestStep;

use Magento\Sales\Test\Page\Adminhtml\OrderCreateIndex;
use Magento\Store\Test\Fixture\Store;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Class SelectStoreStep
 * Step for select store
 */
class SelectStoreStep implements TestStepInterface
{
    /**
     * Store fixture
     *
     * @var Store
     */
    protected $store;

    /**
     * Order Create Index page
     *
     * @var OrderCreateIndex
     */
    protected $orderCreateIndex;

    /**
     * Preparing step properties
     *
     * @constructor
     * @param Store $store
     * @param OrderCreateIndex $orderCreateIndex
     */
    public function __construct(Store $store, OrderCreateIndex $orderCreateIndex)
    {
        $this->store = $store;
        $this->orderCreateIndex = $orderCreateIndex;
    }

    /**
     * Select store on order create page
     *
     * @return array
     */
    public function run()
    {
        if ($this->orderCreateIndex->getStoreBlock()->isVisible()) {
            $this->orderCreateIndex->getStoreBlock()->selectStoreView($this->store);
        }
    }
}
