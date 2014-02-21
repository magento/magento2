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
namespace Magento\Catalog\Model\Plugin;

class LogTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Plugin\Log
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_compareItemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_logResourceMock;

    protected function setUp()
    {
        $this->_logResourceMock = $this->getMock('Magento\Log\Model\Resource\Log', array(), array(), '', false);
        $this->_compareItemMock = $this->getMock(
            'Magento\Catalog\Model\Product\Compare\Item', array(), array(), '', false
        );
        $this->_model = new \Magento\Catalog\Model\Plugin\Log($this->_compareItemMock);
    }

    /**
     * @covers \Magento\Catalog\Model\Plugin\Log::afterClean
     */
    public function testAfterClean()
    {
        $this->_compareItemMock->expects($this->once())
            ->method('clean');

        $this->assertEquals($this->_logResourceMock, $this->_model->afterClean($this->_logResourceMock));
    }
}
