<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 */
namespace Magento\Widget\Model\Config;

/**
 * @magentoAppArea adminhtml
 */
class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoCache config disabled
     */
    public function testGet()
    {
        $fileResolver = $this->getMockForAbstractClass('Magento\Framework\Config\FileResolverInterface');
        $fileResolver->expects($this->exactly(3))->method('get')->will($this->returnValueMap([
            ['widget.xml', 'global', [file_get_contents(__DIR__ . '/_files/orders_and_returns.xml')]],
            ['widget.xml', 'adminhtml', []],
            ['widget.xml', 'design', [file_get_contents(__DIR__ . '/_files/orders_and_returns_customized.xml')]],
        ]));
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $reader = $objectManager->create('Magento\Widget\Model\Config\Reader', ['fileResolver' => $fileResolver]);
        /** @var \Magento\Widget\Model\Config\Data $configData */
        $configData = $objectManager->create('Magento\Widget\Model\Config\Data', ['reader' => $reader]);
        $result = $configData->get();
        $expected = include '_files/expectedGlobalDesignArray.php';
        $this->assertEquals($expected, $result);
    }
}
