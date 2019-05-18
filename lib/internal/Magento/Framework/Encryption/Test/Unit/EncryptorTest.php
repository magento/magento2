<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Encryption\Test\Unit;

use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Encryption\Crypt;
use Magento\Framework\App\DeploymentConfig;

class EncryptorTest extends \PHPUnit_Framework_TestCase
{
    private $cryptKey = 'g9mY9KLrcuAVJfsmVUSRkKFLDdUPVkaZ';

    /**
     * @var \Magento\Framework\Encryption\Encryptor
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_randomGenerator;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->_randomGenerator = $this->getMock(\Magento\Framework\Math\Random::class, [], [], '', false);
        $deploymentConfigMock = $this->getMock(DeploymentConfig::class, [], [], '', false);
        $deploymentConfigMock->expects($this->any())
            ->method('get')
            ->with(Encryptor::PARAM_CRYPT_KEY)
            ->will($this->returnValue('cryptKey'));
        $this->_model = new Encryptor($this->_randomGenerator, $deploymentConfigMock);
    }

    /**
     * Hashing without a salt.
     */
    public function testGetHashNoSalt()
    {
        $this->_randomGenerator->expects($this->never())->method('getRandomString');
        $expected = '2c7d52d272ca4899fbffa05e52e0c77ae5a51e6001b13b9021b040e8267a596e';
        $actual = $this->_model->getHash('password');
        $this->assertEquals($expected, $actual);
    }

    /**
     * Providing salt for hash.
     */
    public function testGetHashSpecifiedSalt()
    {
        $this->_randomGenerator->expects($this->never())->method('getRandomString');
        $expected = '13601bda4ea78e55a07b98866d2be6be0744e3866f13c00c811cab608a28f322:salt:1';
        $actual = $this->_model->getHash('password', 'salt');
        $this->assertEquals($expected, $actual);
    }

    /**
     * Hashing with random salt.
     */
    public function testGetHashRandomSaltDefaultLength()
    {
        $salt = '-----------random_salt----------';
        $this->_randomGenerator
            ->expects($this->once())
            ->method('getRandomString')
            ->with(32)
            ->will($this->returnValue($salt));
        $expected = 'a1c7fc88037b70c9be84d3ad12522c7888f647915db78f42eb572008422ba2fa:' . $salt . ':1';
        $actual = $this->_model->getHash('password', true);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Hashing with random salt of certain length.
     */
    public function testGetHashRandomSaltSpecifiedLength()
    {
        $this->_randomGenerator
            ->expects($this->once())
            ->method('getRandomString')
            ->with(11)
            ->will($this->returnValue('random_salt'));
        $expected = '4c5cab8dd00137d11258f8f87b93fd17bd94c5026fc52d3c5af911dd177a2611:random_salt:1';
        $actual = $this->_model->getHash('password', 11);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Validating a hash.
     *
     * @param string $password
     * @param string $hash
     * @param bool $expected
     *
     * @dataProvider validateHashDataProvider
     */
    public function testValidateHash($password, $hash, $expected)
    {
        $actual = $this->_model->validateHash($password, $hash);
        $this->assertEquals($expected, $actual);
    }

    /**
     * List of values and their hashes using different algorithms.
     *
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
     * Encrypting with empty keys.
     *
     * @param mixed $key
     * @dataProvider emptyKeyDataProvider
     */
    public function testEncryptWithEmptyKey($key)
    {
        $deploymentConfigMock = $this->getMock(DeploymentConfig::class, [], [], '', false);
        $deploymentConfigMock->expects($this->any())
            ->method('get')
            ->with(Encryptor::PARAM_CRYPT_KEY)
            ->will($this->returnValue($key));
        $model = new Encryptor($this->_randomGenerator, $deploymentConfigMock);
        $value = 'arbitrary_string';
        $this->assertEquals($value, $model->encrypt($value));
    }

    /**
     * List of invalid keys.
     *
     * @return array
     */
    public function emptyKeyDataProvider()
    {
        return [[null], [0], [''], ['0']];
    }

    /**
     * @param mixed $key
     *
     * @dataProvider emptyKeyDataProvider
     */
    public function testDecryptWithEmptyKey($key)
    {
        $deploymentConfigMock = $this->getMock(DeploymentConfig::class, [], [], '', false);
        $deploymentConfigMock->expects($this->any())
            ->method('get')
            ->with(Encryptor::PARAM_CRYPT_KEY)
            ->will($this->returnValue($key));
        $model = new Encryptor($this->_randomGenerator, $deploymentConfigMock);
        $value = 'arbitrary_string';
        $this->assertEquals('', $model->decrypt($value));
    }

    /**
     * Seeing that encrypting uses RIJNDAEL_256.
     */
    public function testEncrypt()
    {
        // sample data to encrypt
        $data = 'Mares eat oats and does eat oats, but little lambs eat ivy.';

        $actual = $this->_model->encrypt($data);

        // Extract the initialization vector and encrypted data
        $parts = explode(':', $actual, 4);

        // Decrypt returned data with RIJNDAEL_256 cipher, cbc mode
        $crypt = new Crypt('cryptKey', MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC, $parts[2]);
        // Verify decrypted matches original data
        $this->assertEquals($data, $crypt->decrypt(base64_decode((string)$parts[3])));
    }

    /**
     * Check that decrypting works.
     */
    public function testDecrypt()
    {
        // sample data to encrypt
        $data = '0:2:z3a4ACpkU35W6pV692U4ueCVQP0m0v0p:' .
            '7ZPIIRZzQrgQH+csfF3fyxYNwbzPTwegncnoTxvI3OZyqKGYlOCTSx5i1KRqNemCC8kuCiOAttLpAymXhzjhNQ==';

        $actual = $this->_model->decrypt($data);

        // Extract the initialization vector and encrypted data
        $parts = explode(':', $data, 4);

        // Decrypt returned data with RIJNDAEL_256 cipher, cbc mode
        $crypt = new Crypt('cryptKey', MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC, $parts[2]);
        // Verify decrypted matches original data
        $this->assertEquals($parts[3], base64_encode($crypt->encrypt($actual)));
    }

    /**
     * Seeing that changing a key does not stand in a way of decrypting.
     */
    public function testEncryptDecryptNewKeyAdded()
    {
        $deploymentConfigMock = $this->getMock(DeploymentConfig::class, [], [], '', false);
        $deploymentConfigMock->expects($this->at(0))
            ->method('get')
            ->with(Encryptor::PARAM_CRYPT_KEY)
            ->will($this->returnValue("cryptKey1"));
        $deploymentConfigMock->expects($this->at(1))
            ->method('get')
            ->with(Encryptor::PARAM_CRYPT_KEY)
            ->will($this->returnValue("cryptKey1\ncryptKey2"));
        $model1 = new Encryptor($this->_randomGenerator, $deploymentConfigMock);
        // simulate an encryption key is being added
        $model2 = new Encryptor($this->_randomGenerator, $deploymentConfigMock);

        // sample data to encrypt
        $data = 'Mares eat oats and does eat oats, but little lambs eat ivy.';
        // encrypt with old key
        $encryptedData = $model1->encrypt($data);
        $decryptedData = $model2->decrypt($encryptedData);

        $this->assertSame($data, $decryptedData, 'Encryptor failed to decrypt data encrypted by old keys.');
    }

    /**
     * Checking that encryptor relies on key validator.
     */
    public function testValidateKey()
    {
        $actual = $this->_model->validateKey('some_key');
        $crypt = new Crypt('some_key', MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC, $actual->getInitVector());
        $expectedEncryptedData = base64_encode($crypt->encrypt('data'));
        $actualEncryptedData = base64_encode($actual->encrypt('data'));
        $this->assertEquals($expectedEncryptedData, $actualEncryptedData);
        $this->assertEquals($crypt->decrypt($expectedEncryptedData), $actual->decrypt($actualEncryptedData));
    }

    /**
     * Algorithms and expressions to validate them.
     *
     * @return array
     */
    public function testUseSpecifiedHashingAlgoDataProvider()
    {
        return [
            [
                'password',
                'salt',
                Encryptor::HASH_VERSION_MD5,
                '/^[a-z0-9]{32}\:salt\:0$/',
            ],
            [
                'password',
                'salt',
                Encryptor::HASH_VERSION_SHA256,
                '/^[a-z0-9]{64}\:salt\:1$/',
            ],
            [
                'password',
                false,
                Encryptor::HASH_VERSION_MD5,
                '/^[0-9a-z]{32}$/',
            ],
            [
                'password',
                false,
                Encryptor::HASH_VERSION_SHA256,
                '/^[0-9a-z]{64}$/',
            ]
        ];
    }

    /**
     * Check that specified algorithm is in fact being used.
     *
     * @dataProvider testUseSpecifiedHashingAlgoDataProvider
     *
     * @param string $password
     * @param string|bool $salt
     * @param int $hashAlgo
     * @param string $pattern
     */
    public function testGetHashMustUseSpecifiedHashingAlgo($password, $salt, $hashAlgo, $pattern)
    {
        $hash = $this->_model->getHash($password, $salt, $hashAlgo);
        $this->assertRegExp($pattern, $hash);
    }

    /**
     * Test hashing working as promised.
     */
    public function testHash()
    {
        //Checking that the same hash is returned for the same value.
        $hash1 = $this->_model->hash($value = 'some value');
        $hash2 = $this->_model->hash($value);
        $this->assertEquals($hash1, $hash2);

        //Checking that hash works with hash validation.
        $this->assertTrue($this->_model->isValidHash($value, $hash1));

        //Checking that key matters.
        $this->_model->setNewKey($this->cryptKey);
        $hash3 = $this->_model->hash($value);
        $this->assertNotEquals($hash3, $hash1);
        //Validation still works
        $this->assertTrue($this->_model->validateHash($value, $hash3));
    }
}
