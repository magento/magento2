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
namespace Magento\Sales\Service\V1\Action;

/**
 * Class OrderGetTest
 */
class OrderGetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Service\V1\Action\OrderGet
     */
    protected $orderGet;

    /**
     * @var \Magento\Sales\Model\OrderRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderRepositoryMock;

    /**
     * @var \Magento\Sales\Service\V1\Data\OrderMapper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderMapperMock;

    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderMock;

    /**
     * @var \Magento\Sales\Service\V1\Data\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObjectMock;

    /**
     * SetUp
     */
    protected function setUp()
    {
        $this->orderRepositoryMock = $this->getMock(
            'Magento\Sales\Model\OrderRepository',
            ['get'],
            [],
            '',
            false
        );
        $this->orderMapperMock = $this->getMock(
            'Magento\Sales\Service\V1\Data\OrderMapper',
            [],
            [],
            '',
            false
        );
        $this->searchResultsBuilderMock = $this->getMock(
            'Magento\Catalog\Service\V1\Data\Product\SearchResultsBuilder',
            [],
            [],
            '',
            false
        );
        $this->searchCriteriaMock = $this->getMock(
            'Magento\Framework\Service\V1\Data\SearchCriteria',
            [],
            [],
            '',
            false
        );
        $this->orderMock = $this->getMock(
            'Magento\Sales\Model\Order',
            [],
            [],
            '',
            false
        );
        $this->dataObjectMock = $this->getMock(
            'Magento\Sales\Service\V1\Data\Order',
            [],
            [],
            '',
            false
        );
        $this->orderGet = new OrderGet(
            $this->orderRepositoryMock,
            $this->orderMapperMock
        );
    }

    /**
     * test order list service
     */
    public function testInvoke()
    {
        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->with($this->equalTo(1))
            ->will($this->returnValue($this->orderMock));
        $this->orderMapperMock->expects($this->once())
            ->method('extractDto')
            ->with($this->equalTo($this->orderMock))
            ->will($this->returnValue($this->dataObjectMock));
        $this->assertEquals($this->dataObjectMock, $this->orderGet->invoke(1));
    }
}
