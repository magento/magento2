<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test case for \Magento\Framework\Encryption\Crypt
 */
namespace Magento\Framework\Encryption\Test\Unit;

class CryptTest extends \PHPUnit\Framework\TestCase
{
    private $_key;

    private static $_cipherInfo;

    private const SUPPORTED_CIPHER_MODE_COMBINATIONS = [
        MCRYPT_BLOWFISH => [MCRYPT_MODE_ECB],
        MCRYPT_RIJNDAEL_128 => [MCRYPT_MODE_ECB],
        MCRYPT_RIJNDAEL_256 => [MCRYPT_MODE_CBC],
    ];

    protected function setUp(): void
    {
        $this->_key = substr(__CLASS__, -32, 32);
    }

    /**
     * @param $length
     * @return bool|string
     */
    protected function _getRandomString($length)
    {
        $result = '';
        do {
            $result .= sha1(microtime());
        } while (strlen($result) < $length);
        return substr($result, -$length);
    }

    protected function _requireCipherInfo()
    {
        $filename = __DIR__ . '/Crypt/_files/_cipher_info.php';

        if (!self::$_cipherInfo) {
            self::$_cipherInfo = include $filename;
        }
    }

    /**
     * @param $cipherName
     * @param $modeName
     * @return mixed
     */
    protected function _getKeySize($cipherName, $modeName)
    {
        $this->_requireCipherInfo();
        return self::$_cipherInfo[$cipherName][$modeName]['key_size'];
    }

    /**
     * @param $cipherName
     * @param $modeName
     * @return mixed
     */
    protected function _getInitVectorSize($cipherName, $modeName)
    {
        $this->_requireCipherInfo();
        return self::$_cipherInfo[$cipherName][$modeName]['iv_size'];
    }

    /**
     * @return array
     */
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
    public function testConstructor($cipher, $mode)
    {
        /* Generate random init vector */
        $initVector = $this->_getRandomString($this->_getInitVectorSize($cipher, $mode));

        $crypt = new \Magento\Framework\Encryption\Crypt($this->_key, $cipher, $mode, $initVector);

        $this->assertEquals($cipher, $crypt->getCipher());
        $this->assertEquals($mode, $crypt->getMode());
        $this->assertEquals($initVector, $crypt->getInitVector());
    }

    /**
     * @return array
     */
    public function getConstructorExceptionData()
    {
        $key = substr(__CLASS__, -32, 32);
        $result = [];
        foreach (self::SUPPORTED_CIPHER_MODE_COMBINATIONS as $cipher => $modes) {
            /** @var array $modes */
            foreach ($modes as $mode) {
                $tooLongKey = str_repeat('-', $this->_getKeySize($cipher, $mode) + 1);
                $tooShortInitVector = str_repeat('-', $this->_getInitVectorSize($cipher, $mode) - 1);
                $tooLongInitVector = str_repeat('-', $this->_getInitVectorSize($cipher, $mode) + 1);
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
     */
    public function testConstructorException($key, $cipher, $mode, $initVector)
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);

        new \Magento\Framework\Encryption\Crypt($key, $cipher, $mode, $initVector);
    }

    public function testConstructorDefaults()
    {
        $cryptExpected = new \Magento\Framework\Encryption\Crypt($this->_key, MCRYPT_BLOWFISH, MCRYPT_MODE_ECB, false);
        $cryptActual = new \Magento\Framework\Encryption\Crypt($this->_key);

        $this->assertEquals($cryptExpected->getCipher(), $cryptActual->getCipher());
        $this->assertEquals($cryptExpected->getMode(), $cryptActual->getMode());
        $this->assertEquals($cryptExpected->getInitVector(), $cryptActual->getInitVector());
    }

    /**
     * @return mixed
     */
    public function getCryptData()
    {
        $fixturesFilename = __DIR__ . '/Crypt/_files/_crypt_fixtures.php';

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
    public function testEncrypt($key, $cipher, $mode, $initVector, $inputData, $expectedData)
    {
        $crypt = new \Magento\Framework\Encryption\Crypt($key, $cipher, $mode, $initVector);
        $actualData = $crypt->encrypt($inputData);
        $this->assertEquals($expectedData, $actualData);
    }

    /**
     * @dataProvider getCryptData
     */
    public function testDecrypt($key, $cipher, $mode, $initVector, $expectedData, $inputData)
    {
        $crypt = new \Magento\Framework\Encryption\Crypt($key, $cipher, $mode, $initVector);
        $actualData = $crypt->decrypt($inputData);
        $this->assertEquals($expectedData, $actualData);
    }

    /**
     * @dataProvider getCipherModeCombinations
     */
    public function testInitVectorRandom($cipher, $mode)
    {
        $crypt1 = new \Magento\Framework\Encryption\Crypt($this->_key, $cipher, $mode, true);
        $initVector1 = $crypt1->getInitVector();

        $crypt2 = new \Magento\Framework\Encryption\Crypt($this->_key, $cipher, $mode, true);
        $initVector2 = $crypt2->getInitVector();

        $expectedSize = $this->_getInitVectorSize($cipher, $mode);
        $this->assertEquals($expectedSize, strlen($initVector1));
        $this->assertEquals($expectedSize, strlen($initVector2));
        $this->assertNotEquals($initVector2, $initVector1);
    }

    /**
     * @dataProvider getCipherModeCombinations
     */
    public function testInitVectorNone($cipher, $mode)
    {
        $crypt = new \Magento\Framework\Encryption\Crypt($this->_key, $cipher, $mode, false);
        $actualInitVector = $crypt->getInitVector();

        $expectedInitVector = str_repeat("\0", $this->_getInitVectorSize($cipher, $mode));
        $this->assertEquals($expectedInitVector, $actualInitVector);
    }
}
