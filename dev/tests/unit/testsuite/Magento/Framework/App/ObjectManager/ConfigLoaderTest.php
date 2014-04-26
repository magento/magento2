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
 */
namespace Magento\Framework\App\ObjectManager;

class ConfigLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\ObjectManager\ConfigLoader
     */
    protected $_model;

    /**
     * @var \Magento\Framework\ObjectManager\Config\Reader\Dom
     */
    protected $_readerMock;

    /**
     * @var \Magento\Framework\App\Cache\Type\Config
     */
    protected $_cacheMock;

    protected function setUp()
    {
        $this->_readerMock = $this->getMock(
            'Magento\Framework\ObjectManager\Config\Reader\Dom',
            array(),
            array(),
            '',
            false
        );

        $this->_cacheMock = $this->getMock('Magento\Framework\App\Cache\Type\Config', array(), array(), '', false);
        $this->_model = new \Magento\Framework\App\ObjectManager\ConfigLoader($this->_cacheMock, $this->_readerMock);
    }

    /**
     * @param $area
     * @dataProvider loadDataProvider
     */
    public function testLoad($area)
    {
        $configData = array('some' => 'config', 'data' => 'value');

        $this->_cacheMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            $area . '::DiConfig'
        )->will(
            $this->returnValue(false)
        );

        $this->_readerMock->expects($this->once())->method('read')->with($area)->will($this->returnValue($configData));

        $this->assertEquals($configData, $this->_model->load($area));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function loadDataProvider()
    {
        return array(
            'global files' => array('global'),
            'adminhtml files' => array('adminhtml'),
            'any area files' => array('any')
        );
    }
}
