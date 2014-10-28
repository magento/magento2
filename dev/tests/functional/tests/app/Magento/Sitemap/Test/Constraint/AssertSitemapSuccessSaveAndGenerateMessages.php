<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Sitemap\Test\Constraint;

use Mtf\Constraint\AbstractConstraint;
use Magento\Sitemap\Test\Page\Adminhtml\SitemapIndex;
use Magento\Sitemap\Test\Fixture\Sitemap;

/**
 * Class AssertSitemapSuccessSaveAndGenerateMessages
 */
class AssertSitemapSuccessSaveAndGenerateMessages extends AbstractConstraint
{
    const SUCCESS_GENERATE_MESSAGE = 'The sitemap "%s" has been generated.';

    const SUCCESS_SAVE_MESSAGE = 'The sitemap has been saved.';

    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert that success messages is displayed after sitemap generate
     *
     * @param SitemapIndex $sitemapIndex
     * @param Sitemap $sitemap
     * @return void
     */
    public function processAssert(SitemapIndex $sitemapIndex, Sitemap $sitemap)
    {
        $actualMessages = $sitemapIndex->getMessagesBlock()->getSuccessMessages();
        \PHPUnit_Framework_Assert::assertTrue(
            in_array(self::SUCCESS_SAVE_MESSAGE, $actualMessages) &&
            in_array(sprintf(self::SUCCESS_GENERATE_MESSAGE, $sitemap->getSitemapFilename()), $actualMessages),
            'Wrong success messages is displayed.'
            . "\nExpected: " . sprintf(self::SUCCESS_GENERATE_MESSAGE, $sitemap->getSitemapFilename())
            . "\nExpected: " . self::SUCCESS_SAVE_MESSAGE
            . "\nActual messages: " . implode("\n", $actualMessages)
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Sitemap success generate and save messages are present.';
    }
}
