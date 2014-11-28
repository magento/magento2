<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
namespace Magento\Widget\Model\Config;

/**
 * @magentoDataFixture Magento/Backend/controllers/_files/cache/all_types_disabled.php
 * @magentoAppArea adminhtml
 */
class DataTest extends \PHPUnit_Framework_TestCase
{
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
        $configData = $objectManager->create('Magento\Widget\Model\Config\Data', array('reader' => $reader));
        $result = $configData->get();
        $expected = include '_files/expectedGlobalDesignArray.php';
        $this->assertEquals($expected, $result);
    }
}
