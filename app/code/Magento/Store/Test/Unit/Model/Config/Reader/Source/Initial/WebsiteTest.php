<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\Model\Config\Reader\Source\Initial;

use Magento\Framework\App\Config\Initial;
use Magento\Store\Model\Config\Reader\Source\Initial\DefaultScope;
use Magento\Store\Model\Config\Reader\Source\Initial\Website;
use Magento\Framework\App\Config\Scope\Converter;

class WebsiteTest extends \PHPUnit_Framework_TestCase
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
