<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Validator;

use Magento\Setup\Validator\IpValidator;

class IpValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var IpValidator
     */
    private $ipValidator;

    protected function setUp(): void
    {
        $this->ipValidator = new IpValidator();
    }

    /**
     * @dataProvider validateIpsNoneAllowedDataProvider
     * @param string[] $ips
     * @param string[] $expectedMessages
     */
    public function testValidateIpsNoneAllowed($ips, $expectedMessages)
    {
        $this->assertEquals($expectedMessages, $this->ipValidator->validateIps($ips, true));
    }

    /**
     * @return array
     */
    public function validateIpsNoneAllowedDataProvider()
    {
        return [
            [['127.0.0.1', '127.0.0.2'], []],
            [['none'], []],
            [['none', '127.0.0.1'], ["Multiple values are not allowed when 'none' is used"]],
            [['127.0.0.1', 'none'], ["Multiple values are not allowed when 'none' is used"]],
            [['none', 'invalid'], ["Multiple values are not allowed when 'none' is used"]],
            [['invalid', 'none'], ["Multiple values are not allowed when 'none' is used"]],
            [['none', 'none'], ["'none' can be only used once"]],
            [['invalid'], ['Invalid IP invalid']],
        ];
    }

    /**
     * @dataProvider validateIpsNoneNotAllowedDataProvider
     * @param string[] $ips
     * @param string[] $expectedMessages
     */
    public function testValidateIpsNoneNotAllowed($ips, $expectedMessages)
    {
        $this->assertEquals($expectedMessages, $this->ipValidator->validateIps($ips, false));
    }

    /**
     * @return array
     */
    public function validateIpsNoneNotAllowedDataProvider()
    {
        return [
            [['127.0.0.1', '127.0.0.2'], []],
            [['none'], ["'none' is not allowed"]],
            [['none', '127.0.0.1'], ["'none' is not allowed"]],
            [['127.0.0.1', 'none'], ["'none' is not allowed"]],
            [['none', 'invalid'], ["'none' is not allowed"]],
            [['invalid', 'none'], ["'none' is not allowed"]],
            [['none', 'none'], ["'none' is not allowed"]],
            [['invalid'], ['Invalid IP invalid']],
        ];
    }
}
