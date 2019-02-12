<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Encryption\Test\Unit;

use Magento\Framework\Encryption\Adapter\SodiumChachaIetf;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Encryption\Crypt;
use Magento\Framework\Encryption\KeyValidator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class EncryptorTest extends \PHPUnit\Framework\TestCase
{
    const CRYPT_KEY_1 = 'g9mY9KLrcuAVJfsmVUSRkKFLDdUPVkaZ';
    const CRYPT_KEY_2 = '7wEjmrliuqZQ1NQsndSa8C8WHvddeEbN';

    /**
     * @var \Magento\Framework\Encryption\Encryptor
     */
    private $encryptor;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $randomGeneratorMock;

    /**
     * @var KeyValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $keyValidatorMock;

    protected function setUp()
    {
        $this->randomGeneratorMock = $this->createMock(\Magento\Framework\Math\Random::class);
        $deploymentConfigMock = $this->createMock(\Magento\Framework\App\DeploymentConfig::class);
        $deploymentConfigMock->expects($this->any())
            ->method('get')
            ->with(Encryptor::PARAM_CRYPT_KEY)
            ->will($this->returnValue(self::CRYPT_KEY_1));
        $this->keyValidatorMock = $this->createMock(KeyValidator::class);
        $this->encryptor = (new ObjectManager($this))->getObject(
            \Magento\Framework\Encryption\Encryptor::class,
            [
                'random' => $this->randomGeneratorMock,
                'deploymentConfig' => $deploymentConfigMock,
                'keyValidator' => $this->keyValidatorMock
            ]
        );
    }

    public function testGetHashNoSalt()
    {
        $this->randomGeneratorMock->expects($this->never())->method('getRandomString');
        $expected = '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8';
        $actual = $this->encryptor->getHash('password');
        $this->assertEquals($expected, $actual);
    }

    public function testGetHashSpecifiedSalt()
    {
        $this->randomGeneratorMock->expects($this->never())->method('getRandomString');
        $expected = '13601bda4ea78e55a07b98866d2be6be0744e3866f13c00c811cab608a28f322:salt:1';
        $actual = $this->encryptor->getHash('password', 'salt');
        $this->assertEquals($expected, $actual);
    }

    public function testGetHashRandomSaltDefaultLength()
    {
        $salt = '-----------random_salt----------';
        $this->randomGeneratorMock
            ->expects($this->once())
            ->method('getRandomString')
            ->with(32)
            ->will($this->returnValue($salt));
        $expected = 'a1c7fc88037b70c9be84d3ad12522c7888f647915db78f42eb572008422ba2fa:' . $salt . ':1';
        $actual = $this->encryptor->getHash('password', true);
        $this->assertEquals($expected, $actual);
    }

    public function testGetHashRandomSaltSpecifiedLength()
    {
        $this->randomGeneratorMock
            ->expects($this->once())
            ->method('getRandomString')
            ->with(11)
            ->will($this->returnValue('random_salt'));
        $expected = '4c5cab8dd00137d11258f8f87b93fd17bd94c5026fc52d3c5af911dd177a2611:random_salt:1';
        $actual = $this->encryptor->getHash('password', 11);
        $this->assertEquals($expected, $actual);
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
        $actual = $this->encryptor->validateHash($password, $hash);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public function validateHashDataProvider()
    {
        return [
            ['password', 'hash:salt:1', false],
            ['password', '67a1e09bb1f83f5007dc119c14d663aa:salt:0', true],
            ['password', '13601bda4ea78e55a07b98866d2be6be0744e3866f13c00c811cab608a28f322:salt:1', true],
        ];
    }

    /**
     * @param mixed $key
     *
     * @dataProvider encryptWithEmptyKeyDataProvider
     * @expectedException \SodiumException
     */
    public function testEncryptWithEmptyKey($key)
    {
        $deploymentConfigMock = $this->createMock(\Magento\Framework\App\DeploymentConfig::class);
        $deploymentConfigMock->expects($this->any())
            ->method('get')
            ->with(Encryptor::PARAM_CRYPT_KEY)
            ->will($this->returnValue($key));
        $model = new Encryptor($this->randomGeneratorMock, $deploymentConfigMock);
        $value = 'arbitrary_string';
        $this->assertEquals($value, $model->encrypt($value));
    }

    /**
     * @return array
     */
    public function encryptWithEmptyKeyDataProvider()
    {
        return [[null], [0], [''], ['0']];
    }

    /**
     * @param mixed $key
     *
     * @dataProvider decryptWithEmptyKeyDataProvider
     */
    public function testDecryptWithEmptyKey($key)
    {
        $deploymentConfigMock = $this->createMock(\Magento\Framework\App\DeploymentConfig::class);
        $deploymentConfigMock->expects($this->any())
            ->method('get')
            ->with(Encryptor::PARAM_CRYPT_KEY)
            ->will($this->returnValue($key));
        $model = new Encryptor($this->randomGeneratorMock, $deploymentConfigMock);
        $value = 'arbitrary_string';
        $this->assertEquals('', $model->decrypt($value));
    }

    /**
     * @return array
     */
    public function decryptWithEmptyKeyDataProvider()
    {
        return [[null], [0], [''], ['0']];
    }

    public function testEncrypt()
    {
        // sample data to encrypt
        $data = 'Mares eat oats and does eat oats, but little lambs eat ivy.';

        $actual = $this->encryptor->encrypt($data);

        // Extract the initialization vector and encrypted data
        $parts = explode(':', $actual, 3);
        list(, , $encryptedData) = $parts;

        $crypt = new SodiumChachaIetf(self::CRYPT_KEY_1);
        // Verify decrypted matches original data
        $this->assertEquals($data, $crypt->decrypt(base64_decode((string)$encryptedData)));
    }

    public function testDecrypt()
    {
        $message = 'Mares eat oats and does eat oats, but little lambs eat ivy.';
        $encrypted = $this->encryptor->encrypt($message);

        $this->assertEquals($message, $this->encryptor->decrypt($encrypted));
    }

    public function testLegacyDecrypt()
    {
        // sample data to encrypt
        $data = '0:2:z3a4ACpkU35W6pV692U4ueCVQP0m0v0p:' .
            'DhEG8/uKGGq92ZusqrGb6X/9+2Ng0QZ9z2UZwljgJbs5/A3LaSnqcK0oI32yjHY49QJi+Z7q1EKu2yVqB8EMpA==';

        $actual = $this->encryptor->decrypt($data);

        // Extract the initialization vector and encrypted data
        $parts = explode(':', $data, 4);
        list(, , $iv, $encrypted) = $parts;

        // Decrypt returned data with RIJNDAEL_256 cipher, cbc mode
        $crypt = new Crypt(self::CRYPT_KEY_1, MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC, $iv);
        // Verify decrypted matches original data
        $this->assertEquals($encrypted, base64_encode($crypt->encrypt($actual)));
    }

    public function testEncryptDecryptNewKeyAdded()
    {
        $deploymentConfigMock = $this->createMock(\Magento\Framework\App\DeploymentConfig::class);
        $deploymentConfigMock->expects($this->at(0))
            ->method('get')
            ->with(Encryptor::PARAM_CRYPT_KEY)
            ->will($this->returnValue(self::CRYPT_KEY_1));
        $deploymentConfigMock->expects($this->at(1))
            ->method('get')
            ->with(Encryptor::PARAM_CRYPT_KEY)
            ->will($this->returnValue(self::CRYPT_KEY_1 . "\n" . self::CRYPT_KEY_2));
        $model1 = new Encryptor($this->randomGeneratorMock, $deploymentConfigMock);
        // simulate an encryption key is being added
        $model2 = new Encryptor($this->randomGeneratorMock, $deploymentConfigMock);

        // sample data to encrypt
        $data = 'Mares eat oats and does eat oats, but little lambs eat ivy.';
        // encrypt with old key
        $encryptedData = $model1->encrypt($data);
        $decryptedData = $model2->decrypt($encryptedData);

        $this->assertSame($data, $decryptedData, 'Encryptor failed to decrypt data encrypted by old keys.');
    }

    public function testValidateKey()
    {
        $this->keyValidatorMock->method('isValid')->willReturn(true);
        $this->encryptor->validateKey(self::CRYPT_KEY_1);
    }

    /**
     * @expectedException \Exception
     */
    public function testValidateKeyInvalid()
    {
        $this->keyValidatorMock->method('isValid')->willReturn(false);
        $this->encryptor->validateKey('-----    ');
    }

    /**
     * @return array
     */
    public function useSpecifiedHashingAlgoDataProvider()
    {
        return [
            ['password', 'salt', Encryptor::HASH_VERSION_MD5,
             '67a1e09bb1f83f5007dc119c14d663aa:salt:0'],
            ['password', 'salt', Encryptor::HASH_VERSION_SHA256,
             '13601bda4ea78e55a07b98866d2be6be0744e3866f13c00c811cab608a28f322:salt:1'],
            ['password', false, Encryptor::HASH_VERSION_MD5,
             '5f4dcc3b5aa765d61d8327deb882cf99'],
            ['password', false, Encryptor::HASH_VERSION_SHA256,
             '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8']
        ];
    }

    /**
     * @dataProvider useSpecifiedHashingAlgoDataProvider
     *
     * @param $password
     * @param $salt
     * @param $hashAlgo
     * @param $expected
     */
    public function testGetHashMustUseSpecifiedHashingAlgo($password, $salt, $hashAlgo, $expected)
    {
        $hash = $this->encryptor->getHash($password, $salt, $hashAlgo);
        $this->assertEquals($expected, $hash);
    }
}
