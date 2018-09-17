<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\Model\Config\Processor;

/**
 * Class PlaceholderTest
 */
class PlaceholderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Store\Model\Config\Processor\Placeholder
     */
    private $model;

    /**
     * @var \Magento\Store\Model\Config\Placeholder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configPlaceholderMock;

    protected function setUp()
    {
        $this->configPlaceholderMock = $this->getMock('Magento\Store\Model\Config\Placeholder', [], [], '', false);

        $this->configPlaceholderMock->expects(
            $this->any()
        )->method(
            'process'
        )->withConsecutive(
            [['key1' => 'value1']],
            [['key2' => 'value2']]
        )->willReturnOnConsecutiveCalls(
            ['key1' => 'value1-processed'],
            ['key2' => 'value2-processed']
        );

        $this->model = new \Magento\Store\Model\Config\Processor\Placeholder(
            $this->getMock(\Magento\Framework\App\RequestInterface::class),
            [],
            null,
            $this->configPlaceholderMock
        );
    }

    public function testProcess()
    {
        $data = [
            'default' => ['key1' => 'value1'],
            'websites' => [
                'code' => ['key2' => 'value2']
            ]
        ];
        $expected = [
            'default' => ['key1' => 'value1-processed'],
            'websites' => [
                'code' => ['key2' => 'value2-processed']
            ]
        ];

        $this->assertEquals($expected, $this->model->process($data));
    }
}
