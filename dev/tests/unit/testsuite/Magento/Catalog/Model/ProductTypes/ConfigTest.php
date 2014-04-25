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
namespace Magento\Catalog\Model\ProductTypes;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $readerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheMock;

    /**
     * @var \Magento\Catalog\Model\ProductTypes\Config
     */
    protected $model;

    protected function setUp()
    {
        $this->readerMock = $this->getMock(
            'Magento\Catalog\Model\ProductTypes\Config\Reader',
            array(),
            array(),
            '',
            false
        );
        $this->cacheMock = $this->getMock('Magento\Framework\Config\CacheInterface');
    }

    /**
     * @dataProvider getTypeDataProvider
     *
     * @param array $value
     * @param mixed $expected
     */
    public function testGetType($value, $expected)
    {
        $this->cacheMock->expects($this->any())->method('load')->will($this->returnValue(serialize($value)));
        $this->model = new \Magento\Catalog\Model\ProductTypes\Config($this->readerMock, $this->cacheMock, 'cache_id');
        $this->assertEquals($expected, $this->model->getType('global'));
    }

    public function getTypeDataProvider()
    {
        return array(
            'global_key_exist' => array(array('types' => array('global' => 'value')), 'value'),
            'return_default_value' => array(array('types' => array('some_key' => 'value')), array())
        );
    }

    public function testGetAll()
    {
        $expected = array('Expected Data');
        $this->cacheMock->expects(
            $this->once()
        )->method(
            'load'
        )->will(
            $this->returnValue(serialize(array('types' => $expected)))
        );
        $this->model = new \Magento\Catalog\Model\ProductTypes\Config($this->readerMock, $this->cacheMock, 'cache_id');
        $this->assertEquals($expected, $this->model->getAll());
    }

    public function testIsProductSet()
    {
        $this->cacheMock->expects($this->once())->method('load')->will($this->returnValue(serialize(array())));
        $this->model = new \Magento\Catalog\Model\ProductTypes\Config($this->readerMock, $this->cacheMock, 'cache_id');

        $this->assertEquals(false, $this->model->isProductSet('typeId'));
    }
}
