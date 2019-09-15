<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

/**
 * Test case for \Magento\Framework\Encryption\Adapter\Mcrypt
 */
namespace Magento\Framework\Encryption\Test\Unit\Adapter;

class McryptTest extends \PHPUnit\Framework\TestCase
{
    private $key;

    private static $cipherInfo;

    private const SUPPORTED_CIPHER_MODE_COMBINATIONS = [
        MCRYPT_BLOWFISH => [MCRYPT_MODE_ECB],
        MCRYPT_RIJNDAEL_128 => [MCRYPT_MODE_ECB],
        MCRYPT_RIJNDAEL_256 => [MCRYPT_MODE_CBC],
    ];

    protected function setUp()
    {
        $this->key = substr(__CLASS__, -32, 32);
    }

    protected function getRandomString(int $length): string
    {
        $result = '';

        do {
            $result .= sha1(microtime());
        } while (strlen($result) < $length);

        return substr($result, -$length);
    }

    private function requireCipherInfo()
    {
        $filename = __DIR__ . '/../Crypt/_files/_cipher_info.php';

        if (!self::$cipherInfo) {
            self::$cipherInfo = include $filename;
        }
    }

    private function getKeySize(string $cipherName, string $modeName): int
    {
        $this->requireCipherInfo();
        return self::$cipherInfo[$cipherName][$modeName]['key_size'];
    }

    private function getInitVectorSize(string $cipherName, string $modeName): int
    {
        $this->requireCipherInfo();
        return self::$cipherInfo[$cipherName][$modeName]['iv_size'];
    }

    public function getCipherModeCombinations(): array
    {
        $result = [];
        foreach (self::SUPPORTED_CIPHER_MODE_COMBINATIONS as $cipher => $modes) {
            /** @var array $modes */
            foreach ($modes as $mode) {
                $result[$cipher . '-' . $mode] = [$cipher, $mode];
            }
        }
        return $result;
    }

    /**
     * @dataProvider getCipherModeCombinations
     */
    public function testConstructor(string $cipher, string $mode)
    {
        /* Generate random init vector */
        $initVector = $this->getRandomString($this->getInitVectorSize($cipher, $mode));

        $crypt = new \Magento\Framework\Encryption\Adapter\Mcrypt($this->key, $cipher, $mode, $initVector);

        $this->assertEquals($cipher, $crypt->getCipher());
        $this->assertEquals($mode, $crypt->getMode());
        $this->assertEquals($initVector, $crypt->getInitVector());
    }

    public function getConstructorExceptionData(): array
    {
        $key = substr(__CLASS__, -32, 32);
        $result = [];
        foreach (self::SUPPORTED_CIPHER_MODE_COMBINATIONS as $cipher => $modes) {
            /** @var array $modes */
            foreach ($modes as $mode) {
                $tooLongKey = str_repeat('-', $this->getKeySize($cipher, $mode) + 1);
                $tooShortInitVector = str_repeat('-', $this->getInitVectorSize($cipher, $mode) - 1);
                $tooLongInitVector = str_repeat('-', $this->getInitVectorSize($cipher, $mode) + 1);
                $result['tooLongKey-' . $cipher . '-' . $mode . '-false'] = [$tooLongKey, $cipher, $mode, false];
                $keyPrefix = 'key-' . $cipher . '-' . $mode;
                $result[$keyPrefix . '-tooShortInitVector'] = [$key, $cipher, $mode, $tooShortInitVector];
                $result[$keyPrefix . '-tooLongInitVector'] = [$key, $cipher, $mode, $tooLongInitVector];
            }
        }
        return $result;
    }

    /**
     * @dataProvider getConstructorExceptionData
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testConstructorException(string $key, string $cipher, string $mode, ?string $initVector = null)
    {
        new \Magento\Framework\Encryption\Adapter\Mcrypt($key, $cipher, $mode, $initVector);
    }

    public function testConstructorDefaults()
    {
        $cryptExpected = new \Magento\Framework\Encryption\Adapter\Mcrypt(
            $this->key,
            MCRYPT_BLOWFISH,
            MCRYPT_MODE_ECB,
            null
        );
        $cryptActual = new \Magento\Framework\Encryption\Adapter\Mcrypt($this->key);

        $this->assertEquals($cryptExpected->getCipher(), $cryptActual->getCipher());
        $this->assertEquals($cryptExpected->getMode(), $cryptActual->getMode());
        $this->assertEquals($cryptExpected->getInitVector(), $cryptActual->getInitVector());
    }

    public function getCryptData(): array
    {
        $fixturesFilename = __DIR__ . '/../Crypt/_files/_crypt_fixtures.php';

        $result = include $fixturesFilename;
        /* Restore encoded string back to binary */
        foreach ($result as &$cryptParams) {
            $cryptParams[5] = base64_decode($cryptParams[5]);
        }
        unset($cryptParams);

        return $result;
    }

    /**
     * @dataProvider getCryptData
     */
    public function testDecrypt(
        string $key,
        string $cipher,
        string $mode,
        ?string $initVector,
        string $expectedData,
        string $inputData
    ) {
        $crypt = new \Magento\Framework\Encryption\Adapter\Mcrypt($key, $cipher, $mode, $initVector);
        $actualData = $crypt->decrypt($inputData);
        $this->assertEquals($expectedData, $actualData);
    }

    /**
     * @dataProvider getCipherModeCombinations
     */
    public function testInitVectorNone(string $cipher, string $mode)
    {
        $crypt = new \Magento\Framework\Encryption\Adapter\Mcrypt(
            $this->key,
            $cipher,
            $mode,
            null
        );
        $actualInitVector = $crypt->getInitVector();

        $expectedInitVector = str_repeat("\0", $this->getInitVectorSize($cipher, $mode));
        $this->assertEquals($expectedInitVector, $actualInitVector);
    }
}
