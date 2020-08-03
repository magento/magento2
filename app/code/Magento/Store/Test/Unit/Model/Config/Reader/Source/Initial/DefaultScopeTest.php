<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Model\Config\Reader\Source\Initial;

use Magento\Framework\App\Config\Initial;
use Magento\Framework\App\Config\Scope\Converter;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\Config\Reader\Source\Initial\DefaultScope;
use PHPUnit\Framework\TestCase;

class DefaultScopeTest extends TestCase
{
    public function testGet()
    {
        $initialConfig = $this->getMockBuilder(Initial::class)
            ->disableOriginalConstructor()
            ->getMock();
        $initialConfig->expects($this->once())
            ->method('getData')
            ->with(ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
            ->willReturn([]);
        $converter = $this->getMockBuilder(Converter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $converter->expects($this->once())
            ->method('convert')
            ->with([])
            ->willReturnArgument(0);

        $defaultSource = new DefaultScope($initialConfig, $converter);
        $this->assertEquals([], $defaultSource->get());
    }
}
