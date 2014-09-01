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
namespace Magento\Sales\Model\Order\Address;

/**
 * Class ValidatorTest
 */
class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Address\Validator
     */
    protected $validator;

    /**
     * @var \Magento\Sales\Model\Order\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressMock;

    /**
     * Mock order address model
     */
    public function setUp()
    {
        $this->addressMock = $this->getMock(
            'Magento\Sales\Model\Order\Address',
            ['hasData', 'getEmail', 'getAddressType', '__wakeup'],
            [],
            '',
            false
        );
        $this->validator = new \Magento\Sales\Model\Order\Address\Validator();
    }

    /**
     * Run test validate
     *
     * @param $addressData
     * @param $email
     * @param $addressType
     * @param $expectedWarnings
     * @dataProvider providerAddressData
     */
    public function testValidate($addressData, $email, $addressType, $expectedWarnings)
    {
        $this->addressMock->expects($this->any())
            ->method('hasData')
            ->will($this->returnValueMap($addressData));
        $this->addressMock->expects($this->once())
            ->method('getEmail')
            ->will($this->returnValue($email));
        $this->addressMock->expects($this->once())
            ->method('getAddressType')
            ->will($this->returnValue($addressType));
        $actualWarnings = $this->validator->validate($this->addressMock);
        $this->assertEquals($expectedWarnings, $actualWarnings);
    }

    /**
     * Provides address data for tests
     *
     * @return array
     */
    public function providerAddressData()
    {
        return [
            [
                [
                    ['parent_id', true],
                    ['postcode', true],
                    ['lastname', true],
                    ['street', true],
                    ['city', true],
                    ['email', true],
                    ['telephone', true],
                    ['country_id', true],
                    ['firstname', true],
                    ['address_type', true],
                ],
                'co@co.co',
                'billing',
                []
            ],
            [
                [
                    ['parent_id', true],
                    ['postcode', true],
                    ['lastname', true],
                    ['street', false],
                    ['city', true],
                    ['email', true],
                    ['telephone', true],
                    ['country_id', true],
                    ['firstname', true],
                    ['address_type', true],
                ],
                'co.co.co',
                'coco-shipping',
                [
                    'Street is a required field',
                    'Email has a wrong format',
                    'Address type doesn\'t match required options'
                ]
            ]
        ];
    }
}
