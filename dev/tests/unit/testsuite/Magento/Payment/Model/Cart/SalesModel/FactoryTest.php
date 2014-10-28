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
namespace Magento\Payment\Model\Cart\SalesModel;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Payment\Model\Cart\SalesModel\Factory */
    protected $_model;

    /** @var \Magento\Framework\ObjectManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $_objectManagerMock;

    protected function setUp()
    {
        $this->_objectManagerMock = $this->getMockForAbstractClass('Magento\Framework\ObjectManager');
        $this->_model = new \Magento\Payment\Model\Cart\SalesModel\Factory($this->_objectManagerMock);
    }

    /**
     * @param string $salesModelClass
     * @param string $expectedType
     * @dataProvider createDataProvider
     */
    public function testCreate($salesModelClass, $expectedType)
    {
        $salesModel = $this->getMock($salesModelClass, array('__wakeup'), array(), '', false);
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $expectedType,
            array('salesModel' => $salesModel)
        )->will(
            $this->returnValue('some value')
        );
        $this->assertEquals('some value', $this->_model->create($salesModel));
    }

    public function createDataProvider()
    {
        return array(
            array('Magento\Sales\Model\Quote', 'Magento\Payment\Model\Cart\SalesModel\Quote'),
            array('Magento\Sales\Model\Order', 'Magento\Payment\Model\Cart\SalesModel\Order')
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateInvalid()
    {
        $this->_model->create('any invalid');
    }
}
