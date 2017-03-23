<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
