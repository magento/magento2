<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PageCache\Test\Constraint;

use Magento\PageCache\Test\Page\Adminhtml\AdminCache;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert cache invalidate pop up.
 */
class AssertCacheInvalidatePopUp extends AbstractConstraint
{
    /**
     * Cache types array.
     *
     * @var array
     */
    private $cacheTypes = [
        'block_html' => "Blocks HTML output",
    ];

    /**
     * Assert cache invalidate pop up.
     *
     * @param AdminCache $adminCache
     * @param array $caches
     * @return void
     */
    public function processAssert(AdminCache $adminCache, array $caches)
    {
        foreach ($caches as $cacheType => $cacheStatus) {
            if ($cacheStatus === 'Invalidated') {
                \PHPUnit_Framework_Assert::assertContains(
                    $this->cacheTypes[$cacheType],
                    $adminCache->getSystemMessageDialog()->getPopupText()
                );
            } else {
                \PHPUnit_Framework_Assert::assertNotContains(
                    $this->cacheTypes[$cacheType],
                    $adminCache->getSystemMessageDialog()->getPopupText()
                );
            }
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Cache invalidate pop up is correct.';
    }
}
