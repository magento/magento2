<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model;

use Laminas\ServiceManager\ServiceLocatorInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Setup\Model\AdminAccount;
use Magento\Setup\Model\AdminAccountFactory;
use PHPUnit\Framework\TestCase;

class AdminAccountFactoryTest extends TestCase
{
    public function testCreate()
    {
        $serviceLocatorMock =
            $this->getMockBuilder(ServiceLocatorInterface::class)
                ->onlyMethods(['get', 'has', 'build'])
                ->getMock();
        $serviceLocatorMock
            ->expects($this->once())
            ->method('get')
            ->with(Encryptor::class)
            ->willReturn($this->createMock(EncryptorInterface::class));
        $adminAccountFactory = new AdminAccountFactory($serviceLocatorMock);
        $adminAccount = $adminAccountFactory->create(
            $this->createMock(AdapterInterface::class),
            []
        );
        $this->assertInstanceOf(AdminAccount::class, $adminAccount);
    }
}
