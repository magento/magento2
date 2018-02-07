<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model;

use \Magento\Setup\Model\AdminAccountFactory;

class AdminAccountFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $serviceLocatorMock = $this->getMockForAbstractClass('Zend\ServiceManager\ServiceLocatorInterface', ['get']);
        $serviceLocatorMock
            ->expects($this->once())
            ->method('get')
            ->with('Magento\Framework\Encryption\Encryptor')
            ->willReturn($this->getMockForAbstractClass('Magento\Framework\Encryption\EncryptorInterface'));
        $adminAccountFactory = new AdminAccountFactory($serviceLocatorMock);
        $adminAccount = $adminAccountFactory->create(
            $this->getMock('Magento\Setup\Module\Setup', [], [], '', false),
            []
        );
        $this->assertInstanceOf('Magento\Setup\Model\AdminAccount', $adminAccount);
    }
}
