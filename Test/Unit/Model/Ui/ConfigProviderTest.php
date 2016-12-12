<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Unit\Model\Ui;

use Magento\Signifyd\Model\OrderSessionId;
use Magento\Signifyd\Model\Ui\ConfigProvider;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class ConfigProviderTest extends \PHPUnit_Framework_TestCase
{
    const HASH = 'hash';

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var OrderSessionId|MockObject
     */
    private $orderSessionId;

    protected function setUp()
    {
        $this->orderSessionId = $this->getMockBuilder(OrderSessionId::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configProvider = new ConfigProvider($this->orderSessionId);
    }

    /**
     * @dataProvider getConfigDataProvider
     */
    public function testGetConfig($expected)
    {
        $this->orderSessionId->expects(static::once())
            ->method('generate')
            ->willReturn(self::HASH);

        static::assertSame($expected, $this->configProvider->getConfig());
    }

    /**
     * @return array
     */
    public function getConfigDataProvider()
    {
        return [
            [
                [
                    'fraud_protection' => [
                        ConfigProvider::SIGNIFYD_CODE => [
                            'orderSessionId' => self::HASH
                        ]
                    ]
                ]
            ]
        ];
    }
}
