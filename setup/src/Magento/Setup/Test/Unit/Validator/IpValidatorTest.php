<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Validator;

use Magento\Setup\Validator\IpValidator;
use PHPUnit\Framework\TestCase;

class IpValidatorTest extends TestCase
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

    /**
     * @dataProvider normaliseNoChangesDataProvider
     * @param string[] $ips
     */
    public function testNormaliseNoChanges(array $ips): void
    {
        $this->assertEquals($ips, $this->ipValidator->normaliseIPAddresses($ips));
    }

    public function normaliseNoChangesDataProvider(): array
    {
        return [
            // IPv4
            [['203.0.113.1/32']],
            [['203.0.113.48/28']],
            [['203.0.113.0/24']],
            [['192.0.2.0/30', '198.51.100.0/25']],
            // IPv6
            [['2001:db8::/32']],
            [['2001:db8::/64']],
            [['2001:db8::/96']],
            [['2001:db8::/96', '2001:db8:ae::/96']],
            // IPv4 & IPv6
            [['203.0.113.1/32', '2001:db8::/96']],
            [['203.0.113.48/28', '2001:db8::/32']],
            [['192.0.2.0/30', '198.51.100.0/25', '2001:db8::/96', '2001:db8:ae::/96']],
        ];
    }

    /**
     * @dataProvider normaliseWithChangesDataProvider
     * @param string[] $input
     * @param string[] $output
     */
    public function testNormaliseWithChanges(array $input, array $output): void
    {
        $this->assertNotEquals($input, $output);
        $this->assertEquals($output, $this->ipValidator->normaliseIPAddresses($input));
    }

    public function normaliseWithChangesDataProvider(): array
    {
        return [
            // Convert to CIDR notation
            [
                ['198.51.100.1'],
                ['198.51.100.1/32']
            ],
            [
                ['2001:db8::'],
                ['2001:db8::/128']
            ],
            [
                ['198.51.100.1', '2001:db8::'],
                ['198.51.100.1/32', '2001:db8::/128'],
            ],

            // Correct network address
            [
                ['198.51.100.1/24'],
                ['198.51.100.0/24']
            ],
            [
                ['2001:db8::cf94/96'],
                ['2001:db8::/96']
            ],
            [
                ['198.51.100.13/28', '2001:db8::cf94/108'],
                ['198.51.100.0/28', '2001:db8::/108'],
            ],

            // Sort list
            [
                ['203.0.113.48/28', '198.51.100.0/25'],
                ['198.51.100.0/25', '203.0.113.48/28'],
            ],
            [
                ['2001:db8:ae::/96', '2001:db8::/96'],
                ['2001:db8::/96', '2001:db8:ae::/96'],
            ],
            [
                ['203.0.113.48/28', '2001:db8:ae::/96', '198.51.100.1/25', '2001:db8::/96'],
                ['198.51.100.0/25', '203.0.113.48/28', '2001:db8::/96',  '2001:db8:ae::/96'],
            ],

            // Combine adjacent
            [
                ['203.0.113.16', '203.0.113.17'],
                ['203.0.113.16/31'],
            ],
            [
                ['203.0.113.0', '203.0.113.1', '203.0.113.2', '203.0.113.3'],
                ['203.0.113.0/30'],
            ],
            [
                ['203.0.113.0/26', '203.0.113.114/26'],
                ['203.0.113.0/25'],
            ],
            [
                ['203.0.113.0/25', '203.0.113.128/26', '203.0.113.192/26'],
                ['203.0.113.0/24'],
            ],
            [
                ['2001:db8:ae:2::/64', '2001:db8:ae:3::/64'],
                ['2001:db8:ae:2::/63'],
            ],
            [
                ['2001:db8::0', '2001:db8::1', '2001:db8::2', '2001:db8::3'],
                ['2001:db8::/126'],
            ],
            [
                ['2001:db8::/116', '2001:db8::1000/116'],
                ['2001:db8::/115'],
            ],

            // Remove overlaps
            [
                ['203.0.113.16/28', '203.0.113.17'],
                ['203.0.113.16/28'],
            ],
            [
                ['203.0.113.0/25', '203.0.113.64/29', '203.0.113.55'],
                ['203.0.113.0/25'],
            ],
            [
                ['2001:db8:ae::/96', '2001:db8:ae::f00/108'],
                ['2001:db8:ae::/96'],
            ],
            [
                ['2001:db8::/120', '2001:db8::1', '2001:db8::2', '2001:db8::3'],
                ['2001:db8::/120'],
            ],
            [
                ['2001:db8::/116', '2001:db8::ace/120', '2001:db8::cab/120'],
                ['2001:db8::/116'],
            ],
        ];
    }

    /**
     * @dataProvider normaliseInvalidDataProvider
     * @param string[] $ips
     */
    public function testNormaliseInvalid(string $message, array $ips): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage($message);
        $this->ipValidator->normaliseIPAddresses($ips);
    }

    public function normaliseInvalidDataProvider(): array
    {
        return [
            ['Invalid IP address format', ['store.magento.example']],
            ['Invalid IP address format', ['invalid']],
            ['Invalid IP address format', ['300.1.1.1']],
            ['Invalid IP address format', ['192.0.2.1.']],
            ['Invalid IP address format', ['192.0.2.1,']],
            ['Invalid IP address format', ['192.0.2.1,192.0.2.2']],
            ['Invalid IP address format', ['192.0.2.1//33']],
            ['Invalid IP address format', ['192.0.2.1//33']],
            ['Invalid prefix length', ['192.0.2.1/33']],
            ['Invalid prefix length', ['2001:db8::/129']],
        ];
    }
}
