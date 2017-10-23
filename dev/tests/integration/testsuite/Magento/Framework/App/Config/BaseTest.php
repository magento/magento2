<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config;

class BaseTest extends \PHPUnit\Framework\TestCase
{
    public function testConstruct()
    {
        $xml = <<<XML
<?xml version="1.0"?>
<root><key>value</key></root>
XML;
        $config = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\App\Config\Base::class,
            ['sourceData' => $xml]
        );

        $this->assertInstanceOf(\Magento\Framework\App\Config\Element::class, $config->getNode('key'));
    }
}
