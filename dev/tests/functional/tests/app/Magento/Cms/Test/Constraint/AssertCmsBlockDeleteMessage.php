<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\Constraint;

use Magento\Cms\Test\Page\Adminhtml\CmsBlockIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that after delete CMS block successful message appears.
 */
class AssertCmsBlockDeleteMessage extends AbstractConstraint
{
    const SUCCESS_DELETE_MESSAGE = 'You deleted the block.';

    /**
     * Assert that after delete CMS block successful message appears.
     *
     * @param CmsBlockIndex $cmsBlockIndex
     * @return void
     */
    public function processAssert(CmsBlockIndex $cmsBlockIndex)
    {
        $actualMessage = $cmsBlockIndex->getMessagesBlock()->getSuccessMessage();
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_DELETE_MESSAGE,
            $actualMessage,
            'Wrong success message is displayed.'
            . "\nExpected: " . self::SUCCESS_DELETE_MESSAGE
            . "\nActual: " . $actualMessage
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'CMS Block success delete message is present.';
    }
}
