<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Block\Express;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Paypal\Block\Express\Shortcut;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\ConfigFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ShortcutTest extends TestCase
{
    private const STUB_ALIAS = 'alias';

    /**
     * @var ConfigFactory|MockObject
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

        $helper = new ObjectManager($this);
        $model = $helper->getObject(
            Shortcut::class,
            [
                'alias' => self::STUB_ALIAS,
                'paymentMethodCode' => 'test-method',
                'paypalConfigFactory' => $paypalConfigFactoryMock
            ]
        );
        $this->assertEquals(self::STUB_ALIAS, $model->getAlias());
    }
}
