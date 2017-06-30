<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Test\Unit\Model\Country\Postcode;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $postcodesConfigMock;

    /**
     * @var \Magento\Directory\Model\Country\Postcode\Validator
     */
    protected $model;

    protected function setUp()
    {
        $this->postcodesConfigMock = $this->getMock(
            \Magento\Directory\Model\Country\Postcode\Config::class,
            [],
            [],
            '',
            false
        );
        $postCodes = [
            'US' => [
                'pattern_1' => ['pattern' => '^[0-9]{5}\-[0-9]{4}$'],
                'pattern_2' => ['pattern' => '^[0-9]{5}$']
            ]
        ];
        $this->postcodesConfigMock->expects($this->once())->method('getPostCodes')->willReturn($postCodes);
        $this->model = new \Magento\Directory\Model\Country\Postcode\Validator($this->postcodesConfigMock);
    }

    public function testValidatePositive()
    {
        $postcode = '12345-6789';
        $countryId = 'US';
        $this->assertTrue($this->model->validate($postcode, $countryId));
    }

    public function testValidateNegative()
    {
        $postcode = 'POST-CODE';
        $countryId = 'US';
        $this->assertFalse($this->model->validate($postcode, $countryId));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Provided countryId does not exist.
     */
    public function testValidateThrowExceptionIfCountryDoesNotExist()
    {
        $postcode = '12345-6789';
        $countryId = 'QQ';
        $this->assertFalse($this->model->validate($postcode, $countryId));
    }
}
