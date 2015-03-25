<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Test\Unit\Model\Config\Backend;

use Magento\Developer\Model\Config\Backend\AllowedIps;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class AllowedIpsTest
 */
class AllowedIpsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Developer\Model\Config\Backend\AllowedIps
     */
    protected $model;

    /**
     * @var \Magento\Framework\Model\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventMangerMock;

    protected function setUp()
    {
        $this->contextMock = $this->getMockBuilder('\Magento\Framework\Model\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $eventMangerMock = $this->getMockBuilder('\Magento\Framework\Event\ManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->any())
            ->method('getEventDispatcher')
            ->willReturn($eventMangerMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->model = $this->objectManagerHelper->getObject(
            'Magento\Developer\Model\Config\Backend\AllowedIps',
            [
                'context' => $this->contextMock,
            ]
        );
    }

    protected function tearDown()
    {
        $this->model = null;
    }

    /**
     * @param array $fieldSetData
     * @param string $expected
     * @dataProvider beforeSaveDataProvider
     * @return void
     */
    public function testBeforeSave($fieldSetData, $expected)
    {
        $this->assertNull($this->model->getFieldsetDataValue('allow_ips'));
        $this->model->setFieldsetData($fieldSetData);
        $this->model->beforeSave();
        $this->assertEquals($expected, $this->model->getData('value'));
    }

    /**
     * @return array
     */
    public function beforeSaveDataProvider()
    {
        return [
            [
                ['allow_ips' => ''],
                '',
            ],
            [
                ['allow_ips' => ', 10.64.206.85, 10. 64.85.206 '],
                '10.64.206.85,10.64.85.206',
            ],
            [
                ['allow_ips' => '10.64.206.85, 10.64.1a.x'],
                '10.64.206.85',
            ],
            [
                ['allow_ips' => ' 10.64. 206.85, 10.49.a. b  '], /* with whitespaces */
                '10.64.206.85',
            ],
            [
                ['allow_ips' => '2001:db8:0:1234:0:567:8:1, '], /* valid IPV6 address */
                '2001:db8:0:1234:0:567:8:1',
            ],

            [
                ['allow_ips' => '2001:0cb8:25a3:04c1:1324:8a2b:0471:8221'], /* valid IPV6 address */
                '2001:0cb8:25a3:04c1:1324:8a2b:0471:8221',
            ],
            [
                ['allow_ips' => '255.255.255.255'], /* valid private ip */
                '255.255.255.255',
            ],
            [
                ['allow_ips' => '127.0.0.1, ::1'], /* valid reserved ip */
                '127.0.0.1,::1',
            ],
        ];
    }
}
