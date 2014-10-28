<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Model\Order\Shipment\Track;

/**
 * Class ValidatorTest
 */
class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Shipment\Track\Validator
     */
    protected $validator;

    /**
     * @var \Magento\Sales\Model\Order\Shipment\Track|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $trackModelMock;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->trackModelMock = $this->getMock(
            'Magento\Sales\Model\Order\Shipment\Track',
            ['hasData', 'getData', '__wakeup'],
            [],
            '',
            false
        );
        $this->validator = new \Magento\Sales\Model\Order\Shipment\Track\Validator();
    }


    /**
     * Run test validate
     *
     * @param $trackDataMap
     * @param $trackData
     * @param $expectedWarnings
     * @dataProvider providerTrackData
     */
    public function testValidate($trackDataMap, $trackData, $expectedWarnings)
    {
        $this->trackModelMock->expects($this->any())
            ->method('hasData')
            ->will($this->returnValueMap($trackDataMap));
        $this->trackModelMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($trackData));
        $actualWarnings = $this->validator->validate($this->trackModelMock);
        $this->assertEquals($expectedWarnings, $actualWarnings);
    }

    /**
     * Provides track data for tests
     *
     * @return array
     */
    public function providerTrackData()
    {
        return [
            [
                [
                    ['parent_id', true],
                    ['order_id', true],
                    ['track_number', true],
                    ['carrier_code', true]
                ],
                [
                    'parent_id' => 25,
                    'order_id' => 12,
                    'track_number' => 125,
                    'carrier_code' => 'custom'
                ],
                []
            ],
            [
                [
                    ['parent_id', true],
                    ['order_id', false],
                    ['track_number', true],
                    ['carrier_code', false]
                ],
                [
                    'parent_id' => 0,
                    'order_id' => null,
                    'track_number' => '',
                    'carrier_code' => null
                ],
                [
                    'parent_id' => 'Parent Track Id can not be empty',
                    'order_id' => 'Order Id is a required field',
                    'track_number' => 'Number can not be empty',
                    'carrier_code' => 'Carrier Code is a required field'
                ]
            ]
        ];
    }
}
 