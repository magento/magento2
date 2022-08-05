<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Model\Theme\Customization;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Theme\Model\Theme\Customization\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testGetFileTypes()
    {
        $expected = [
            'key'  => 'value',
            'key1' => 'value1',
        ];
        $config = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMock();
        $config->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_CUSTOM_FILES, 'default')
            ->willReturn($expected);
        /** @var ScopeConfigInterface $config */
        $object = new Config($config);
        $this->assertEquals($expected, $object->getFileTypes());
    }
}
