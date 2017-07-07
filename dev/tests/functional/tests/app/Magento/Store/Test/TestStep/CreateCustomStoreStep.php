<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Store\Test\Fixture\Store;

/**
 * Create Custom Store.
 */
class CreateCustomStoreStep implements TestStepInterface
{
    /**
     * Fixture Store.
     *
     * @var Store
     */
    private $store;

    /**
     * @param Store $store
     */
    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    /**
     * Runs Test Creation Custom Store.
     *
     * @return array
     */
    public function run()
    {
        if (!$this->store->hasData('store_id')) {
            $this->store->persist();
        }

        return ['store' => $this->store];
    }
}
