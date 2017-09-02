<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestStep;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Store\Test\Fixture\Store;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Step for select store.
 */
class SelectStoreFrontendStep implements TestStepInterface
{
    /**
     * Store fixture.
     *
     * @var Store
     */
    private $store;

    /**
     * Cms Index page.
     *
     * @var CmsIndex
     */
    private $cmsIndex;

    /**
     * Preparing step properties.
     *
     * @param Store $store
     * @param CmsIndex $cmsIndex
     */
    public function __construct(Store $store, CmsIndex $cmsIndex)
    {
        $this->store = $store;
        $this->cmsIndex = $cmsIndex;
    }

    /**
     * Select store on order create page.
     *
     * @return void
     */
    public function run()
    {
        $this->cmsIndex->open();
        if ($this->cmsIndex->getStoreSwitcherBlock()->isVisible()) {
            $this->cmsIndex->getStoreSwitcherBlock()->selectStoreView($this->store->getName());
        }
    }
}
