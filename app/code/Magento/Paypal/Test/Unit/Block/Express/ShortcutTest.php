<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Block\Express;

use Magento\Paypal\Block\Express\Shortcut;
use Magento\Paypal\Model\ConfigFactory;
use Magento\Paypal\Model\Config;

class ShortcutTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Alias
     */
    const ALIAS = 'alias';

    /**
     * @var ConfigFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_paypalConfigFactory;

    public function testGetAlias()
    {
        $paypalConfigFactoryMock = $this->getMockBuilder(ConfigFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();


        $paypalConfigFactoryMock->expects(self::once())
            ->method('create')
            ->willReturn($configMock);

        $configMock->expects(self::once())
            ->method('setMethod')
            ->with('test-method');

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $model = $helper->getObject(
            Shortcut::class,
            [
                'alias' => self::ALIAS,
                'paymentMethodCode' => 'test-method',
                'paypalConfigFactory' => $paypalConfigFactoryMock
            ]
        );
        $this->assertEquals(self::ALIAS, $model->getAlias());
    }
}
