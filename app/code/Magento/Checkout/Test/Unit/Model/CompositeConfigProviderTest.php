<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Model;

class CompositeConfigProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configProviderMock;

    /**
     * @var \Magento\Checkout\Model\CompositeConfigProvider
     */
    protected $model;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->configProviderMock = $this->getMock('\Magento\Checkout\Model\ConfigProviderInterface');
        $this->model = $objectManager->getObject(
            'Magento\Checkout\Model\CompositeConfigProvider',
            ['configProviders' => [$this->configProviderMock]]
        );
    }

    public function testGetConfig()
    {
        $config = ['key' => 'value'];
        $this->configProviderMock->expects($this->once())->method('getConfig')->willReturn($config);
        $this->assertEquals($config, $this->model->getConfig());
    }
}
