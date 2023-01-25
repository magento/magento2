<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Helper;

use Magento\Framework\App\Config;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\SalesRule\Helper\Coupon;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\TestCase;

class CouponTest extends TestCase
{
    /**
     * @var Coupon
     */
    protected $helper;

    /**
     * @var Config
     */
    protected $scopeConfig;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var array
     */
    protected $couponParameters;

    /**
     * @var string
     */
    protected $separator = '|';

    protected function setUp(): void
    {
        $this->couponParameters = [
            'separator' => $this->separator,
            'charset' => [
                'format' => 'abc',
            ],
        ];
        $objectManager = new ObjectManager($this);
        $className = Coupon::class;
        $arguments = $objectManager->getConstructArguments(
            $className,
            ['couponParameters' => $this->couponParameters]
        );
        /** @var Context $context */
        $context = $arguments['context'];
        $this->scopeConfig = $context->getScopeConfig();
        $this->helper = $objectManager->getObject(Coupon::class, $arguments);
    }

    public function testGetFormatsList()
    {
        $helper = $this->helper;
        $this->assertArrayHasKey(
            $helper::COUPON_FORMAT_ALPHABETICAL,
            $helper->getFormatsList(),
            'The returned list should contain COUPON_FORMAT_ALPHABETICAL constant value as a key'
        );
        $this->assertArrayHasKey(
            $helper::COUPON_FORMAT_ALPHANUMERIC,
            $helper->getFormatsList(),
            'The returned list should contain COUPON_FORMAT_ALPHANUMERIC constant value as a key'
        );
        $this->assertArrayHasKey(
            $helper::COUPON_FORMAT_NUMERIC,
            $helper->getFormatsList(),
            'The returned list should contain COUPON_FORMAT_NUMERIC constant value as a key'
        );
    }

    public function testGetDefaultLength()
    {
        $helper = $this->helper;
        $defaultLength = 100;
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with($helper::XML_PATH_SALES_RULE_COUPON_LENGTH, ScopeInterface::SCOPE_STORE)
            ->willReturn($defaultLength);

        $this->assertEquals($defaultLength, $helper->getDefaultLength());
    }

    public function testGetDefaultFormat()
    {
        $helper = $this->helper;
        $defaultFormat = 'format';
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with($helper::XML_PATH_SALES_RULE_COUPON_FORMAT, ScopeInterface::SCOPE_STORE)
            ->willReturn($defaultFormat);

        $this->assertEquals($defaultFormat, $helper->getDefaultFormat());
    }

    public function testGetDefaultPrefix()
    {
        $helper = $this->helper;
        $defaultPrefix = 'prefix';
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with($helper::XML_PATH_SALES_RULE_COUPON_PREFIX, ScopeInterface::SCOPE_STORE)
            ->willReturn($defaultPrefix);

        $this->assertEquals($defaultPrefix, $helper->getDefaultPrefix());
    }

    public function testGetDefaultSuffix()
    {
        $helper = $this->helper;
        $defaultSuffix = 'suffix';
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with($helper::XML_PATH_SALES_RULE_COUPON_SUFFIX, ScopeInterface::SCOPE_STORE)
            ->willReturn($defaultSuffix);

        $this->assertEquals($defaultSuffix, $helper->getDefaultSuffix());
    }

    public function testGetDefaultDashInterval()
    {
        $helper = $this->helper;
        $defaultDashInterval = 4;
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with($helper::XML_PATH_SALES_RULE_COUPON_DASH_INTERVAL, ScopeInterface::SCOPE_STORE)
            ->willReturn($defaultDashInterval);

        $this->assertEquals($defaultDashInterval, $helper->getDefaultDashInterval());
    }

    public function testGetCharset()
    {
        $format = 'format';
        $expected = ['a', 'b', 'c'];

        $this->assertEquals($expected, $this->helper->getCharset($format));
    }

    public function testGetSeparator()
    {
        $this->assertEquals($this->separator, $this->helper->getCodeSeparator());
    }
}
