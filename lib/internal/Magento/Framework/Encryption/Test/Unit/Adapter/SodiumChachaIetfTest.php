<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

/**
 * Test case for \Magento\Framework\Encryption\Adapter\SodiumChachaIetf
 */
namespace Magento\Framework\Encryption\Test\Unit\Adapter;

use Magento\Framework\Encryption\Adapter\SodiumChachaIetf;
use PHPUnit\Framework\TestCase;

class SodiumChachaIetfTest extends TestCase
{
    /**
     * @return array
     */
    public static function getCryptData(): array
    {
        $result = include __DIR__ . '/../Crypt/_files/_sodium_chachaieft_fixtures.php';
        /* Restore encoded string back to binary */
        foreach ($result as &$cryptParams) {
            $cryptParams['encrypted'] = base64_decode($cryptParams['encrypted']);
        }
        unset($cryptParams);

        return $result;
    }

    /**
     * @dataProvider getCryptData
     *
     * @param string $key
     * @param string $encrypted
     * @param string $decrypted
     * @throws \SodiumException
     */
    public function testEncrypt(string $key, string $encrypted, string $decrypted): void
    {
        $crypt = new SodiumChachaIetf($key);
        $result = $crypt->encrypt($decrypted);

        $this->assertNotEquals($encrypted, $result);
    }

    /**
     * @dataProvider getCryptData
     *
     * @param string $key
     * @param string $encrypted
     * @param string $decrypted
     */
    public function testDecrypt(string $key, string $encrypted, string $decrypted): void
    {
        $crypt = new SodiumChachaIetf($key);
        $result = $crypt->decrypt($encrypted);

        $this->assertEquals($decrypted, $result);
    }
}
