<?php
/**
 * \Magento\Payment\Model\Config
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model;

use Magento\Payment\Model\Config;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class ConfigTest
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config
     */
    private $model = null;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var $cache \Magento\Framework\App\Cache */
        $cache = $objectManager->create(\Magento\Framework\App\Cache::class);
        $cache->clean();
        $fileResolverMock = $this->getMockBuilder(
            \Magento\Framework\Config\FileResolverInterface::class
        )->disableOriginalConstructor()->getMock();
        $fileList = [
            file_get_contents(__DIR__ . '/_files/payment.xml'),
            file_get_contents(__DIR__ . '/_files/payment2.xml'),
        ];
        $fileResolverMock->expects($this->any())->method('get')->will($this->returnValue($fileList));
        $reader = $objectManager->create(
            \Magento\Payment\Model\Config\Reader::class,
            ['fileResolver' => $fileResolverMock]
        );
        $data = $objectManager->create(\Magento\Payment\Model\Config\Data::class, ['reader' => $reader]);
        $this->model = $objectManager->create(Config::class, ['dataStorage' => $data]);
    }

    /**
     * @covers \Magento\Payment\Model\Config::getActiveMethods
     */
    public function testGetActiveMethods()
    {
        $paymentMethods = $this->model->getActiveMethods();
        static::assertNotEmpty($paymentMethods);

        /** @var \Magento\Payment\Model\MethodInterface $method */
        foreach ($paymentMethods as $method) {
            static::assertNotEmpty($method->getCode());
            static::assertTrue($method->isActive());
            static::assertEquals(0, $method->getStore());
        }
    }

    public function testGetCcTypes()
    {
        $expected = ['AE' => 'American Express', 'SM' => 'Switch/Maestro', 'SO' => 'Solo'];
        $ccTypes = $this->model->getCcTypes();
        $this->assertEquals($expected, $ccTypes);
    }

    public function testGetGroups()
    {
        $expected = ['any_payment' => 'Any Payment Methods', 'offline' => 'Offline Payment Methods'];
        $groups = $this->model->getGroups();
        $this->assertEquals($expected, $groups);
    }

    protected function tearDown()
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var $cache \Magento\Framework\App\Cache */
        $cache = $objectManager->create(\Magento\Framework\App\Cache::class);
        $cache->clean();
    }
}
