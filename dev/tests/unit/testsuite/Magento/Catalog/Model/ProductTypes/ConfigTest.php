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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\ProductTypes;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_readerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cacheMock;

    /**
     * @var \Magento\Catalog\Model\ProductTypes\Config
     */
    protected $_model;

    protected function setUp()
    {
        $this->_readerMock = $this->getMock(
            'Magento\Catalog\Model\ProductTypes\Config\Reader', array(), array(), '', false);
        $this->_cacheMock = $this->getMock('Magento\Config\CacheInterface');
    }

    /**
     * @dataProvider getTypeDataProvider
     *
     * @param array $value
     * @param mixed $expected
     */
    public function testGetType($value, $expected)
    {
        $this->_cacheMock->expects($this->any())->method('load')->will($this->returnValue(serialize($value)));
        $this->_model = new \Magento\Catalog\Model\ProductTypes\Config($this->_readerMock,
            $this->_cacheMock, 'cache_id');
        $this->assertEquals($expected, $this->_model->getType('global'));
    }

    public function getTypeDataProvider()
    {
        return array(
            'global_key_exist' => array(array('global' => 'value'), 'value'),
            'return_default_value' => array(array('some_key' => 'value'), array())
        );
    }

    public function testGetAll()
    {
        $expected = array('Expected Data');
        $this->_cacheMock->expects($this->once())->method('load')->will($this->returnValue(serialize($expected)));
        $this->_model = new \Magento\Catalog\Model\ProductTypes\Config(
            $this->_readerMock,
            $this->_cacheMock,
            'cache_id'
        );
        $this->assertEquals($expected, $this->_model->getAll());
    }
}
