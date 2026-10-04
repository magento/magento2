<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

/**
 * Test case for \Magento\Framework\Encryption\Adapter\SodiumChachaIetf
 */
namespace Magento\Framework\Encryption\Test\Unit\Adapter;

use Magento\Framework\Encryption\Adapter\SodiumChachaIetf;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

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

    /**     *
     * @param string $key
     * @param string $encrypted
     * @param string $decrypted
     * @throws \SodiumException
     */
    #[DataProvider('getCryptData')]
    public function testEncrypt(string $key, string $encrypted, string $decrypted): void
    {
        $crypt = new SodiumChachaIetf($key);
        $result = $crypt->encrypt($decrypted);

        $this->assertNotEquals($encrypted, $result);
    }

    /**     *
     * @param string $key
     * @param string $encrypted
     * @param string $decrypted
     */
    #[DataProvider('getCryptData')]
    public function testDecrypt(string $key, string $encrypted, string $decrypted): void
    {
        $crypt = new SodiumChachaIetf($key);
        $result = $crypt->decrypt($encrypted);

        $this->assertEquals($decrypted, $result);
    }
}
