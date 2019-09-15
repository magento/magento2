<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cron\Test\Unit\Model\System\Config\Initial;

use Magento\Cron\Model\Groups\Config\Data as GroupsConfigModel;
use Magento\Cron\Model\System\Config\Initial\Converter as ConverterPlugin;
use Magento\Framework\App\Config\Initial\Converter;

/**
 * Class ConverterTest
 *
 * Unit test for \Magento\Cron\Model\System\Config\Initial\Converter
 */
class ConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var GroupsConfigModel|\PHPUnit_Framework_MockObject_MockObject
     */
    private $groupsConfigMock;

    /**
     * @var Converter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $converterMock;

    /**
     * @var ConverterPlugin
     */
    private $converterPlugin;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->groupsConfigMock = $this->getMockBuilder(
            GroupsConfigModel::class
        )->disableOriginalConstructor()->getMock();
        $this->converterMock = $this->getMockBuilder(Converter::class)->getMock();
        $this->converterPlugin = new ConverterPlugin($this->groupsConfigMock);
    }

    /**
     * Tests afterConvert method with no $result['data']['default']['system'] set
     */
    public function testAfterConvertWithNoData()
    {
        $expectedResult = ['test'];
        $this->groupsConfigMock->expects($this->never())
            ->method('get');

        $result = $this->converterPlugin->afterConvert($this->converterMock, $expectedResult);

        self::assertSame($expectedResult, $result);
    }

    /**
     * Tests afterConvert method with $result['data']['default']['system'] set
     */
    public function testAfterConvertWithData()
    {
        $groups = [
            'group1' => ['val1' => ['value' => '1']],
            'group2' => ['val2' => ['value' => '2']]
        ];
        $expectedResult['data']['default']['system']['cron'] = [
            'group1' => [
                'val1' => '1'
            ],
            'group2' => [
                'val2' => '2'
            ]
        ];
        $result['data']['default']['system']['cron'] = '1';

        $this->groupsConfigMock->expects($this->once())
            ->method('get')
            ->willReturn($groups);

        $result = $this->converterPlugin->afterConvert($this->converterMock, $result);

        self::assertEquals($expectedResult, $result);
    }
}
