<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Stream filter that collect the data that is going through the stream
 *
 * @link http://php.net/manual/en/function.stream-filter-register.php
 */
namespace Magento\Test\Profiler;

use PHPUnit\Framework\Assert;

class OutputBambooTestFilter extends \php_user_filter
{
    /**
     * @var string
     */
    private static $_collectedData = '';

    /**
     * Collect intercepted data.
     *
     * @param resource $in
     * @param resource $out
     * @param int $consumed
     * @param bool $closing
     *
     * @return int
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function filter($in, $out, &$consumed, $closing): int
    {
        while ($bucket = stream_bucket_make_writeable($in)) {
            self::$_collectedData .= $bucket->data;
            $consumed += $bucket->datalen;
            stream_bucket_append($out, $bucket);
        }
        return PSFS_PASS_ON;
    }

    /**
     * This method to reset collection data.
     *
     * @return void
     */
    public static function resetCollectedData(): void
    {
        self::$_collectedData = '';
    }

    /**
     * Assert that collected data matches expected format.
     *
     * @param string $expectedData
     *
     * @return void
     */
    public static function assertCollectedData(string $expectedData): void
    {
        Assert::assertStringMatchesFormat(
            $expectedData,
            self::$_collectedData,
            'Expected data went through the stream.'
        );
    }
}
