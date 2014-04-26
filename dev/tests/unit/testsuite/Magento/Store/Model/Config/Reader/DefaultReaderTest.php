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
namespace Magento\Store\Model\Config\Reader;

class DefaultReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Store\Model\Config\Reader\DefaultReader
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_initialConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_collectionFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_appStateMock;

    protected function setUp()
    {
        $this->_initialConfigMock = $this->getMock('Magento\Framework\App\Config\Initial', array(), array(), '', false);
        $this->_collectionFactory = $this->getMock(
            'Magento\Store\Model\Resource\Config\Collection\ScopedFactory',
            array('create'),
            array(),
            '',
            false
        );
        $this->_appStateMock = $this->getMock('Magento\Framework\App\State', array(), array(), '', false);
        $this->_appStateMock->expects($this->any())->method('isInstalled')->will($this->returnValue(true));
        $this->_model = new \Magento\Store\Model\Config\Reader\DefaultReader(
            $this->_initialConfigMock,
            new \Magento\Framework\App\Config\Scope\Converter(),
            $this->_collectionFactory,
            $this->_appStateMock
        );
    }

    public function testRead()
    {
        $this->_initialConfigMock->expects(
            $this->any()
        )->method(
            'getData'
        )->with(
            \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT
        )->will(
            $this->returnValue(array('config' => array('key1' => 'default_value1', 'key2' => 'default_value2')))
        );
        $this->_collectionFactory->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            array('scope' => 'default')
        )->will(
            $this->returnValue(
                array(
                    new \Magento\Framework\Object(array('path' => 'config/key1', 'value' => 'default_db_value1')),
                    new \Magento\Framework\Object(array('path' => 'config/key3', 'value' => 'default_db_value3'))
                )
            )
        );
        $expectedData = array(
            'config' => array('key1' => 'default_db_value1', 'key2' => 'default_value2', 'key3' => 'default_db_value3')
        );
        $this->assertEquals($expectedData, $this->_model->read());
    }
}
