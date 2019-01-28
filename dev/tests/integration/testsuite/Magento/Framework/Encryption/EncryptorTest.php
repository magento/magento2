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
    protected $_model;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\Encryption\Encryptor::class
        );
    }

    public function testEncryptDecrypt()
    {
        $this->assertSame('', $this->_model->decrypt($this->_model->encrypt('')));
        $this->assertSame('test', $this->_model->decrypt($this->_model->encrypt('test')));
    }
}
