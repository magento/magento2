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

namespace Magento\Encryption;

class EncryptorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var @var \Magento\Encryption\Encryptor
     */
    protected $_model;

    public function testGetHash()
    {
        $hash = $this->_getEncryptor()->getHash('password', 'some_salt_string');

        $this->assertEquals('a42f82cf25f63f40ff85f8c9b3ff0cb4:some_salt_string', $hash);
    }

    /**
     * @param string $password
     * @param string $hash
     * @param bool $expected
     *
     * @dataProvider validateHashDataProvider
     */
    public function testValidateHash($password, $hash, $expected)
    {
        $actual = $this->_getEncryptor()->validateHash($password, $hash);

        $this->assertEquals($expected, $actual);
    }

    public function validateHashDataProvider()
    {
        return array(
            array('password', 'hash', false),
            array('password', 'hash:salt', false),
            array('password', md5('password'), true),
            array('password', md5('saltpassword') . ':' . 'salt', true),
        );
    }

    /**
     * @param string $password
     * @param string $hash
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid hash.
     * @dataProvider validateHashExceptionDataProvider
     */
    public function testValidateHashException($password, $hash)
    {
        $this->_getEncryptor()->validateHash($password, $hash);
    }

    public function validateHashExceptionDataProvider()
    {
        return array(
            array('password', 'hash1:hash2:hash3'),
            array('password', 'hash1:hash2:hash3:hash4'),
        );
    }

    /**
     * @param mixed $key
     *
     * @dataProvider encryptWithEmptyKeyDataProvider
     */
    public function testEncryptWithEmptyKey($key)
    {
        $encryptor = new \Magento\Encryption\Encryptor(
            $this->getMock('\Magento\Math\Random', array(), array(), '', false),
            $this->getMock('Magento\Encryption\CryptFactory', array(), array(), '', false),
            $key
        );

        $value = 'arbitrary_string';

        $this->assertEquals($value, $encryptor->encrypt($value));
    }

    public function encryptWithEmptyKeyDataProvider()
    {
        return array(
            array(null),
            array(0),
            array(''),
            array('0'),
        );
    }

    /**
     * @param string $value
     * @param string $expected
     *
     * @dataProvider encryptDataProvider
     */
    public function testEncrypt($value, $expected)
    {
        $crypt = $this->getMock('Magento\Encryption\Crypt', array(), array(), '', false);
        $cryptFactory = $this->getMock('Magento\Encryption\CryptFactory', array(), array(), '', false);
        $encryptor = new \Magento\Encryption\Encryptor(
            $this->getMock('\Magento\Math\Random', array(), array(), '', false),
            $cryptFactory,
            'cryptKey'
        );

        $cryptFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($crypt));
        $crypt->expects($this->any())
            ->method('encrypt')
            ->with($value)
            ->will($this->returnArgument(0));

        $actual = $encryptor->encrypt($value);

        $this->assertEquals($expected, $actual);
    }

    public function encryptDataProvider()
    {
        return array(
            array('value1', base64_encode('value1')),
            array(true, base64_encode('1')),
        );
    }

    /**
     * @param string $value
     * @param string $expected
     *
     * @dataProvider decryptDataProvider
     */
    public function testDecrypt($value, $expected)
    {
        $crypt = $this->getMock('Magento\Encryption\Crypt', array(), array(), '', false);
        $cryptFactory = $this->getMock('Magento\Encryption\CryptFactory', array(), array(), '', false);
        $encryptor = new \Magento\Encryption\Encryptor(
            $this->getMock('\Magento\Math\Random', array(), array(), '', false),
            $cryptFactory,
            'cryptKey'
        );

        $cryptFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($crypt));
        $crypt->expects($this->any())
            ->method('decrypt')
            ->with($expected)
            ->will($this->returnValue($expected));

        $actual = $encryptor->decrypt($value);

        $this->assertEquals($expected, $actual);
    }

    public function decryptDataProvider()
    {
        return array(
            array(base64_encode('value1'), 'value1'),
        );
    }

    public function testValidateKey()
    {
        $key = 'some_key';

        $crypt = $this->getMock('Magento\Encryption\Crypt', array(), array(), '', false);
        $cryptFactory = $this->getMock('Magento\Encryption\CryptFactory', array(), array(), '', false);
        $encryptor = new \Magento\Encryption\Encryptor(
            $this->getMock('\Magento\Math\Random', array(), array(), '', false),
            $cryptFactory,
            'cryptKey'
        );

        $cryptFactory->expects($this->any())
            ->method('create')
            ->with(array('key' => $key))
            ->will($this->returnValue($crypt));

        $encryptor->validateKey($key);
    }

    public function testValidateKeyDefault()
    {
        $key = null;

        $crypt = $this->getMock('Magento\Encryption\Crypt', array(), array(), '', false);
        $cryptFactory = $this->getMock('Magento\Encryption\CryptFactory', array(), array(), '', false);
        $encryptor = new \Magento\Encryption\Encryptor(
            $this->getMock('\Magento\Math\Random', array(), array(), '', false),
            $cryptFactory,
            'cryptKey'
        );

        $cryptFactory->expects($this->any())
            ->method('create')
            ->with(array('key' => 'cryptKey'))
            ->will($this->returnValue($crypt));

        $encryptor->validateKey($key);
    }

    /**
     * @return \Magento\Encryption\Encryptor
     */
    protected function _getEncryptor()
    {
        return new \Magento\Encryption\Encryptor(
            $this->getMock('\Magento\Math\Random', array(), array(), '', false),
            $this->getMock('Magento\Encryption\CryptFactory', array(), array(), '', false),
            'cryptKey'
        );
    }
}
