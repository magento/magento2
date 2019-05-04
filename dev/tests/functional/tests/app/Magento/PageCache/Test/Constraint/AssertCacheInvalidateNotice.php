<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PageCache\Test\Constraint;

use Magento\PageCache\Test\Page\Adminhtml\AdminCache;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert cache invalidate notice.
 */
class AssertCacheInvalidateNotice extends AbstractConstraint
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
     * Assert cache invalidate notice.
     *
     * @param AdminCache $adminCache
     * @param array $caches
     * @return void
     */
    public function processAssert(AdminCache $adminCache, array $caches)
    {
        $adminCache->getSystemMessageDialog()->closePopup();
        foreach ($caches as $cacheType => $cacheStatus) {
            if ($cacheStatus === 'Invalidated') {
                \PHPUnit\Framework\Assert::assertContains(
                    $this->cacheTypes[$cacheType],
                    $adminCache->getSystemMessageBlock()->getContent()
                );
            } else {
                \PHPUnit\Framework\Assert::assertNotContains(
                    $this->cacheTypes[$cacheType],
                    $adminCache->getSystemMessageBlock()->getContent()
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
        return 'Cache invalidate notice is correct.';
    }
}
