<?php
/**
 * \Magento\Theme\Model\Layout\Config
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
        /** @var $cache \Magento\Framework\App\Cache */
        $cache = $objectManager->create('Magento\Framework\App\Cache');
        $cache->clean();
        $configFile = file_get_contents(__DIR__ . '/_files/page_layouts.xml');
        $fileResolverMock = $this->getMockBuilder('Magento\Framework\Config\FileResolverInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $fileResolverMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue([$configFile]));
        $reader = $objectManager->create(
            'Magento\Theme\Model\Layout\Config\Reader',
            ['fileResolver' => $fileResolverMock]
        );
        $dataStorage = $objectManager->create('Magento\Theme\Model\Layout\Config\Data', ['reader' => $reader]);
        $this->_model = $objectManager->create(
            'Magento\Theme\Model\Layout\Config',
            ['dataStorage' => $dataStorage]
        );
    }

    public function testGetPageLayouts()
    {
        $empty = [
            'label' => 'Empty',
            'code' => 'empty',
        ];
        $oneColumn = [
            'label' => '1 column',
            'code' => '1column',
        ];
        $result = $this->_model->getPageLayouts();
        $this->assertEquals($empty, $result['empty']->getData());
        $this->assertEquals($oneColumn, $result['1column']->getData());
    }

    public function testGetPageLayout()
    {
        $empty = [
            'label' => 'Empty',
            'code' => 'empty',
        ];
        $this->assertEquals($empty, $this->_model->getPageLayout('empty')->getData());
        $this->assertFalse($this->_model->getPageLayout('unknownLayoutCode'));
    }

    public function testGetPageLayoutHandles()
    {
        $expected = ['empty' => 'empty', '1column' => '1column'];
        $this->assertEquals($expected, $this->_model->getPageLayoutHandles());
    }
}
