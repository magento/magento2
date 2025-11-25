<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Encryption;

class EncryptorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Encryption\Encryptor
     */
    private $encryptor;

    protected function setUp(): void
    {
        $this->encryptor = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\Encryption\Encryptor::class
        );
    }

    public function testEncryptDecrypt()
    {
        $this->assertEquals('', $this->encryptor->decrypt($this->encryptor->encrypt('')));
        $this->assertEquals('test', $this->encryptor->decrypt($this->encryptor->encrypt('test')));
    }

    /**
     * @param string $key
     * @dataProvider validEncryptionKeyDataProvider
     */
    public function testValidateKey($key)
    {
        $this->encryptor->validateKey($key);
    }

    public static function validEncryptionKeyDataProvider()
    {
        return [
            '32 numbers' => ['12345678901234567890123456789012'],
            '32 characters' => ['aBcdeFghIJKLMNOPQRSTUvwxYzabcdef'],
            '32 special characters' => ['!@#$%^&*()_+~`:;"<>,.?/|*&^%$#@!'],
            '32 combination' =>['1234eFghI1234567^&*(890123456789'],
        ];
    }

    /**
     *
     * @param string $key
     * @dataProvider invalidEncryptionKeyDataProvider
     */
    public function testValidateKeyInvalid($key)
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Encryption key must be 32 character string without any white space.');

        $this->encryptor->validateKey($key);
    }

    public static function invalidEncryptionKeyDataProvider()
    {
        return [
            'empty string' => [''],
            'leading space' => [' 1234567890123456789012345678901'],
            'tailing space' => ['1234567890123456789012345678901 '],
            'space in the middle' => ['12345678901 23456789012345678901'],
            'tab in the middle' => ['12345678901    23456789012345678'],
            'return in the middle' => ['12345678901
            23456789012345678901'],
            '31 characters' => ['1234567890123456789012345678901'],
            '33 characters' => ['123456789012345678901234567890123'],
        ];
    }
}
