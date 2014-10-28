<?php
/**
 * \Magento\Theme\Model\Layout\Config\Reader
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
            array('fileResolver' => $this->_fileResolverMock)
        );
    }

    public function testRead()
    {
        $fileList = array(file_get_contents(__DIR__ . '/../_files/page_layouts.xml'));
        $this->_fileResolverMock->expects($this->any())->method('get')->will($this->returnValue($fileList));
        $result = $this->_model->read('global');
        $expected = array(
            'empty' => array(
                'label' => 'Empty',
                'code' => 'empty',
            ),
            '1column' => array(
                'label' => '1 column',
                'code' => '1column',
            )
        );
        $this->assertEquals($expected, $result);
    }

    public function testMergeCompleteAndPartial()
    {
        $fileList = array(
            file_get_contents(__DIR__ . '/../_files/page_layouts.xml'),
            file_get_contents(__DIR__ . '/../_files/page_layouts2.xml')
        );
        $this->_fileResolverMock->expects($this->any())->method('get')->will($this->returnValue($fileList));

        $result = $this->_model->read('global');
        $expected = array(
            'empty' => array(
                'label' => 'Empty',
                'code' => 'empty',
            ),
            '1column' => array(
                'label' => '1 column modified',
                'code' => '1column',
            ),
            '2columns-left' => array(
                'label' => '2 columns with left bar',
                'code' => '2columns-left',
            )
        );
        $this->assertEquals($expected, $result);
    }
}
