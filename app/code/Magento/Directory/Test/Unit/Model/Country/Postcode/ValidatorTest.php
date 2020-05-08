<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Directory\Test\Unit\Model\Country\Postcode;

use Magento\Directory\Model\Country\Postcode\Config;
use Magento\Directory\Model\Country\Postcode\Validator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $postcodesConfigMock;

    /**
     * @var Validator
     */
    protected $model;

    protected function setUp(): void
    {
        $this->postcodesConfigMock = $this->createMock(Config::class);
        $postCodes = [
            'US' => [
                'pattern_1' => ['pattern' => '^[0-9]{5}\-[0-9]{4}$'],
                'pattern_2' => ['pattern' => '^[0-9]{5}$']
            ]
        ];
        $this->postcodesConfigMock->expects($this->once())->method('getPostCodes')->willReturn($postCodes);
        $this->model = new Validator($this->postcodesConfigMock);
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

    public function testValidateThrowExceptionIfCountryDoesNotExist()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Provided countryId does not exist.');
        $postcode = '12345-6789';
        $countryId = 'QQ';
        $this->assertFalse($this->model->validate($postcode, $countryId));
    }
}
