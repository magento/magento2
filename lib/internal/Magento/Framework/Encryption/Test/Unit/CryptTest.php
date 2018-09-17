<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test case for \Magento\Framework\Encryption\Crypt
 */
namespace Magento\Framework\Encryption\Test\Unit;

class CryptTest extends \PHPUnit_Framework_TestCase
{
    private $_key;

    private static $_cipherInfo;

    protected $_supportedCiphers = [MCRYPT_BLOWFISH, MCRYPT_RIJNDAEL_128, MCRYPT_RIJNDAEL_256];

    protected $_supportedModes = [
        MCRYPT_MODE_ECB,
        MCRYPT_MODE_CBC,
        MCRYPT_MODE_CFB,
        MCRYPT_MODE_OFB,
        MCRYPT_MODE_NOFB,
    ];

    protected function setUp()
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
        /* Generate allowed sizes for encryption key and init vector
           $data = array();
           foreach ($this->_supportedCiphers as $cipher) {
           if (!array_key_exists($cipher, $data)) {
           $data[$cipher] = array();
           }
           foreach ($this->_supportedModes as $mode) {
           $cipherHandle = mcrypt_module_open($cipher, '', $mode, '');
           $data[$cipher][$mode] = array(
           'key_size' => mcrypt_enc_get_key_size($cipherHandle),
           'iv_size'  => mcrypt_enc_get_iv_size($cipherHandle),
           );
           mcrypt_module_close($cipherHandle);
           }
           }
           file_put_contents($filename, '<?php return ' . var_export($data, true) . ";\n", LOCK_EX);
           */
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
    public function getCipherModeCombinations()
    {
        $result = [];
        foreach ($this->_supportedCiphers as $cipher) {
            foreach ($this->_supportedModes as $mode) {
                $result[] = [$cipher, $mode];
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
        $result = [];
        foreach ($this->_supportedCiphers as $cipher) {
            foreach ($this->_supportedModes as $mode) {
                $tooLongKey = str_repeat('-', $this->_getKeySize($cipher, $mode) + 1);
                $tooShortInitVector = str_repeat('-', $this->_getInitVectorSize($cipher, $mode) - 1);
                $tooLongInitVector = str_repeat('-', $this->_getInitVectorSize($cipher, $mode) + 1);
                $result[] = [$tooLongKey, $cipher, $mode, false];
                $result[] = [$this->_key, $cipher, $mode, $tooShortInitVector];
                $result[] = [$this->_key, $cipher, $mode, $tooLongInitVector];
            }
        }
        return $result;
    }

    /**
     * @dataProvider getConstructorExceptionData
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testConstructorException($key, $cipher, $mode, $initVector)
    {
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
        /* Generate fixtures
           $fixtures = array();
           foreach (array('', 'Hello world!!!') as $inputString) {
           foreach ($this->_supportedCiphers as $cipher) {
           foreach ($this->_supportedModes as $mode) {
           $randomKey = $this->_getRandomString($this->_getKeySize($cipher, $mode));
           $randomInitVector = $this->_getRandomString($this->_getInitVectorSize($cipher, $mode));
           $crypt = new \Magento\Framework\Encryption\Crypt($randomKey, $cipher, $mode, $randomInitVector);
           $fixtures[] = array(
           $randomKey, // Encryption key
           $cipher,
           $mode,
           $randomInitVector, // Init vector
           $inputString, // String to encrypt
           base64_encode($crypt->encrypt($inputString)) // Store result of encryption as base64
           );
           }
           }
           }
           file_put_contents($fixturesFilename, '<?php return ' . var_export($fixtures, true) . ";\n", LOCK_EX);
           */
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
