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

class SodiumChachaIetfTest extends \PHPUnit\Framework\TestCase
{
    public function getCryptData(): array
    {
        $fixturesFilename = __DIR__ . '/../Crypt/_files/_sodium_chachaieft_fixtures.php';

        $result = include $fixturesFilename;
        /* Restore encoded string back to binary */
        foreach ($result as &$cryptParams) {
            $cryptParams['encrypted'] = base64_decode($cryptParams['encrypted']);
        }
        unset($cryptParams);

        return $result;
    }

    /**
     * @dataProvider getCryptData
     */
    public function testEncrypt(string $key, string $encrypted, string $decrypted)
    {
        $crypt = new \Magento\Framework\Encryption\Adapter\SodiumChachaIetf($key);
        $result = $crypt->encrypt($decrypted);

        $this->assertNotEquals($encrypted, $result);
    }

    /**
     * @dataProvider getCryptData
     */
    public function testDecrypt(string $key, string $encrypted, string $decrypted)
    {
        $crypt = new \Magento\Framework\Encryption\Adapter\SodiumChachaIetf($key);
        $result = $crypt->decrypt($encrypted);

        $this->assertEquals($decrypted, $result);
    }
}
