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

/**
 * Stream filter that collect the data that is going through the stream
 *
 * @link http://php.net/manual/en/function.stream-filter-register.php
 */
namespace Magento\Test\Profiler;

class OutputBambooTestFilter extends \php_user_filter
{
    private static $_collectedData = '';

    /**
     * Collect intercepted data
     *
     * @param resource $in
     * @param resource $out
     * @param int $consumed
     * @param bool $closing
     * @return int
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function filter($in, $out, &$consumed, $closing)
    {
        while ($bucket = stream_bucket_make_writeable($in)) {
            self::$_collectedData .= $bucket->data;
            $consumed += $bucket->datalen;
            stream_bucket_append($out, $bucket);
        }
        return PSFS_PASS_ON;
    }

    public static function resetCollectedData()
    {
        self::$_collectedData = '';
    }

    /**
     * Assert that collected data matches expected format
     *
     * @param string $expectedData
     */
    public static function assertCollectedData($expectedData)
    {
        \PHPUnit_Framework_Assert::assertStringMatchesFormat(
            $expectedData,
            self::$_collectedData,
            'Expected data went through the stream.'
        );
    }
}
