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
namespace Magento\Sales\Model\Resource;

class QuoteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Resource\Quote
     */
    protected $_model;

    /**
     * @var \Magento\Framework\App\Resource
     */
    protected $_resourceMock;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_configMock;

    /**
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql
     */
    protected $_adapterMock;

    /**
     * @var \Magento\Framework\DB\Select
     */
    protected $_selectMock;

    protected function setUp()
    {
        $this->_selectMock = $this->getMock('\Magento\Framework\DB\Select', array(), array(), '', false);
        $this->_selectMock->expects($this->any())->method('from')->will($this->returnSelf());
        $this->_selectMock->expects($this->any())->method('where');

        $this->_adapterMock = $this->getMock('\Magento\Framework\DB\Adapter\Pdo\Mysql', array(), array(), '', false);
        $this->_adapterMock->expects($this->any())->method('select')->will($this->returnValue($this->_selectMock));

        $this->_resourceMock = $this->getMock('\Magento\Framework\App\Resource', array(), array(), '', false);
        $this->_resourceMock->expects(
            $this->any()
        )->method(
            'getConnection'
        )->will(
            $this->returnValue($this->_adapterMock)
        );

        $this->_configMock = $this->getMock('\Magento\Eav\Model\Config', array(), array(), '', false);

        $this->_model = new \Magento\Sales\Model\Resource\Quote(
            $this->_resourceMock,
            new \Magento\Framework\Stdlib\DateTime(),
            $this->_configMock
        );
    }

    /**
     * @param $value
     * @dataProvider isOrderIncrementIdUsedDataProvider
     */
    public function testIsOrderIncrementIdUsed($value)
    {
        $expectedBind = array(':increment_id' => $value);
        $this->_adapterMock->expects($this->once())->method('fetchOne')->with($this->_selectMock, $expectedBind);
        $this->_model->isOrderIncrementIdUsed($value);
    }

    /**
     * @return array
     */
    public function isOrderIncrementIdUsedDataProvider()
    {
        return array(array(100000001), array('10000000001'), array('M10000000001'));
    }
}
