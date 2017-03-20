<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PageCache\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\PageCache\Test\Page\Adminhtml\AdminCache;

/**
 * Assert cache status.
 */
class AssertCacheStatus extends AbstractConstraint
{
    /**
     * Cache types array.
     *
     * @var array
     */
    private $cacheTypes = [
        'block_html' => "Blocks HTML output",
        'full_page' => "Page Cache",
    ];

    /**
     * Assert cache status equals to passed from variation.
     *
     * @param AdminCache $adminCache
     * @param array $caches
     * @return void
     */
    public function processAssert(AdminCache $adminCache, array $caches)
    {
        $adminCache->open();
        foreach ($caches as $cacheType => $cacheStatus) {
            \PHPUnit_Framework_Assert::assertTrue(
                $adminCache->getGridBlock()->isCacheStatusCorrect($this->cacheTypes[$cacheType], $cacheStatus),
                $this->cacheTypes[$cacheType] . " cache status in grid does not equal to " . $cacheStatus
            );
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Cache status is correct.';
    }
}
