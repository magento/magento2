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
namespace Magento\OfflineShipping\Model\SalesRule;

class CalculatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\SalesRule\Model\Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = $this->getMock(
            'Magento\OfflineShipping\Model\SalesRule\Calculator',
            array('_getRules', '__wakeup'),
            array(),
            '',
            false
        );
        $this->_model->expects($this->any())->method('_getRules')->will($this->returnValue(array()));
    }

    public function testProcessFreeShipping()
    {
        $item = $this->getMock('Magento\Sales\Model\Quote\Item', array('getAddress', '__wakeup'), array(), '', false);
        $item->expects($this->once())->method('getAddress')->will($this->returnValue(true));

        $this->assertInstanceOf(
            'Magento\OfflineShipping\Model\SalesRule\Calculator',
            $this->_model->processFreeShipping($item)
        );

        return true;
    }
}
