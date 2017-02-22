<?php
/**
 * \Magento\Theme\Model\Layout\Config\Reader
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Layout\Config;

class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Theme\Model\Layout\Config\Reader
     */
    protected $_model;

    /** @var  \Magento\Framework\Config\FileResolverInterface/PHPUnit_Framework_MockObject_MockObject */
    protected $_fileResolverMock;

    public function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var $cache \Magento\Framework\App\Cache */
        $cache = $objectManager->create('Magento\Framework\App\Cache');
        $cache->clean();
        $this->_fileResolverMock = $this->getMockBuilder(
            'Magento\Framework\Config\FileResolverInterface'
        )->disableOriginalConstructor()->getMock();
        $this->_model = $objectManager->create(
            'Magento\Theme\Model\Layout\Config\Reader',
            ['fileResolver' => $this->_fileResolverMock]
        );
    }

    public function testRead()
    {
        $fileList = [file_get_contents(__DIR__ . '/../_files/page_layouts.xml')];
        $this->_fileResolverMock->expects($this->any())->method('get')->will($this->returnValue($fileList));
        $result = $this->_model->read('global');
        $expected = [
            'empty' => [
                'label' => 'Empty',
                'code' => 'empty',
            ],
            '1column' => [
                'label' => '1 column',
                'code' => '1column',
            ],
        ];
        $this->assertEquals($expected, $result);
    }

    public function testMergeCompleteAndPartial()
    {
        $fileList = [
            file_get_contents(__DIR__ . '/../_files/page_layouts.xml'),
            file_get_contents(__DIR__ . '/../_files/page_layouts2.xml'),
        ];
        $this->_fileResolverMock->expects($this->any())->method('get')->will($this->returnValue($fileList));

        $result = $this->_model->read('global');
        $expected = [
            'empty' => [
                'label' => 'Empty',
                'code' => 'empty',
            ],
            '1column' => [
                'label' => '1 column modified',
                'code' => '1column',
            ],
            '2columns-left' => [
                'label' => '2 columns with left bar',
                'code' => '2columns-left',
            ],
        ];
        $this->assertEquals($expected, $result);
    }
}
