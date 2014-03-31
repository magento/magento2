<?php
/**
 * \Magento\Theme\Model\Layout\Config
 *
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
 */
namespace Magento\Theme\Model\Layout;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Theme\Model\Layout\Config
     */
    protected $_model;

    public function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var $cache \Magento\App\Cache */
        $cache = $objectManager->create('Magento\App\Cache');
        $cache->clean();
        $fileResolverMock = $this->getMockBuilder(
            'Magento\Config\FileResolverInterface'
        )->disableOriginalConstructor()->getMock();
        $configFile = file_get_contents(__DIR__ . '/_files/page_layouts.xml');
        $fileResolverMock->expects($this->any())->method('get')->will($this->returnValue(array($configFile)));
        $reader = $objectManager->create(
            'Magento\Theme\Model\Layout\Config\Reader',
            array('fileResolver' => $fileResolverMock)
        );
        $data = $objectManager->create('Magento\Theme\Model\Layout\Config\Data', array('reader' => $reader));
        $this->_model = $objectManager->create('Magento\Theme\Model\Layout\Config', array('dataStorage' => $data));
    }

    public function testGetPageLayouts()
    {
        $empty = array(
            'label' => 'Empty',
            'code' => 'empty',
            'template' => 'empty.phtml',
            'layout_handle' => 'page_empty',
            'is_default' => '0'
        );
        $oneColumn = array(
            'label' => '1 column',
            'code' => 'one_column',
            'template' => '1column.phtml',
            'layout_handle' => 'page_one_column',
            'is_default' => '1'
        );
        $result = $this->_model->getPageLayouts();
        $this->assertEquals($empty, $result['empty']->getData());
        $this->assertEquals($oneColumn, $result['one_column']->getData());
    }

    public function testGetPageLayout()
    {
        $empty = array(
            'label' => 'Empty',
            'code' => 'empty',
            'template' => 'empty.phtml',
            'layout_handle' => 'page_empty',
            'is_default' => '0'
        );
        $this->assertEquals($empty, $this->_model->getPageLayout('empty')->getData());
        $this->assertFalse($this->_model->getPageLayout('unknownLayoutCode'));
    }

    public function testGetPageLayoutHandles()
    {
        $expected = array('empty' => 'page_empty', 'one_column' => 'page_one_column');
        $this->assertEquals($expected, $this->_model->getPageLayoutHandles());
    }
}
