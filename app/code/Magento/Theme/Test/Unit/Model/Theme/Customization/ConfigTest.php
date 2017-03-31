<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Theme\Customization;

use \Magento\Theme\Model\Theme\Customization\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testGetFileTypes()
    {
        $expected = [
            'key'  => 'value',
            'key1' => 'value1',
        ];
        $config = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)->getMock();
        $config->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_CUSTOM_FILES, 'default')
            ->willReturn($expected);
        /** @var \Magento\Framework\App\Config\ScopeConfigInterface $config */
        $object = new Config($config);
        $this->assertEquals($expected, $object->getFileTypes());
    }
}
