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
namespace Magento\Framework\Encryption;

class ModelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Encryption\Encryptor
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\Encryption\Encryptor'
        );
    }

    public function testEncryptDecrypt()
    {
        $encryptor = $this->_model;

        $this->assertEquals('', $encryptor->decrypt($encryptor->encrypt('')));
        $this->assertEquals('test', $encryptor->decrypt($encryptor->encrypt('test')));
    }

    public function testEncryptDecrypt2()
    {
        $encryptor = $this->_model;

        $initial = md5(uniqid());
        $encrypted = $encryptor->encrypt($initial);
        $this->assertNotEquals($initial, $encrypted);
        $this->assertEquals($initial, $encryptor->decrypt($encrypted));
    }

    public function testValidateKey()
    {
        $validKey = md5(uniqid());
        $this->assertInstanceOf('Magento\Framework\Encryption\Crypt', $this->_model->validateKey($validKey));
    }

    public function testGetValidateHash()
    {
        $password = uniqid();
        $hash = $this->_model->getHash($password);

        $this->assertTrue(is_string($hash));
        $this->assertTrue($this->_model->validateHash($password, $hash));
    }
}
