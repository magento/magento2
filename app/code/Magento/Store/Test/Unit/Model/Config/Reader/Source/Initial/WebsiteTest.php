<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Model\Config\Reader\Source\Initial;

use Magento\Framework\App\Config\Initial;
use Magento\Framework\App\Config\Scope\Converter;
use Magento\Store\Model\Config\Reader\Source\Initial\DefaultScope;
use Magento\Store\Model\Config\Reader\Source\Initial\Website;
use PHPUnit\Framework\TestCase;

class WebsiteTest extends TestCase
{
    public function testGet()
    {
        $scopeCode = 'myWebsite';
        $initialConfig = $this->getMockBuilder(Initial::class)
            ->disableOriginalConstructor()
            ->getMock();
        $initialConfig->expects($this->once())
            ->method('getData')
            ->with("websites|$scopeCode")
            ->willReturn([
                'general' => [
                    'locale' => [
                        'code'=> 'en_US'
                    ]
                ]
            ]);
        $defaultScopeReader = $this->getMockBuilder(DefaultScope::class)
            ->disableOriginalConstructor()
            ->getMock();
        $defaultScopeReader->expects($this->once())
            ->method('get')
            ->willReturn([
                'general' => [
                    'locale' => [
                        'code'=> 'ru_RU'
                    ]
                ]
            ]);
        $converter = $this->getMockBuilder(Converter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $converter->expects($this->once())
            ->method('convert')
            ->willReturnArgument(0);

        $websiteSource = new Website($initialConfig, $defaultScopeReader, $converter);
        $this->assertEquals(
            [
                'general' => [
                    'locale' => [
                        'code'=> 'en_US'
                    ]
                ]
            ],
            $websiteSource->get($scopeCode)
        );
    }
}
