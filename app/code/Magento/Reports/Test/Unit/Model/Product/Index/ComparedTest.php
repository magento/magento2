<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Unit\Model\Product\Index;

use Magento\Reports\Model\Product\Index\Compared;

class ComparedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Reports\Model\Product\Index\Compared
     */
    protected $compared;

    /**
     * @var \Magento\Framework\Model\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Customer\Model\Visitor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $visitorMock;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionMock;

    /**
     * @var \Magento\Framework\Session\Generic|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $genericMock;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $visibilityMock;

    /**
     * @var \Magento\Framework\Stdlib\DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dateTimeMock;

    /**
     * @var \Magento\Catalog\Helper\Product\Compare|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $catalogProductHelperMock;

    /**
     * @var \Magento\Framework\Model\ResourceModel\AbstractResource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Framework\Data\Collection\AbstractDb|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dbMock;
    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->contextMock = $this->getMockBuilder('Magento\Framework\Model\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->registryMock = $this->getMockBuilder('Magento\Framework\Registry')
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->getMock();
        $this->visitorMock = $this->getMockBuilder('Magento\Customer\Model\Visitor')
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionMock = $this->getMockBuilder('Magento\Customer\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();
        $this->genericMock = $this->getMockBuilder('Magento\Framework\Session\Generic')
            ->disableOriginalConstructor()
            ->getMock();
        $this->visibilityMock = $this->getMockBuilder('Magento\Catalog\Model\Product\Visibility')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dateTimeMock = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime')
            ->getMock();
        $this->catalogProductHelperMock = $this->getMockBuilder('Magento\Catalog\Helper\Product\Compare')
            ->disableOriginalConstructor()
            ->getMock();

        $this->resourceMock = $this->getMockBuilder('Magento\Framework\Model\ResourceModel\AbstractResource')
            ->disableOriginalConstructor()
            ->setMethods(['getIdFieldName', '_construct', 'getConnection'])
            ->getMockForAbstractClass();
        $this->dbMock = $this->getMockBuilder('Magento\Framework\Data\Collection\AbstractDb')
            ->disableOriginalConstructor()
            ->getMock();

        $this->compared = new Compared(
            $this->contextMock,
            $this->registryMock,
            $this->storeManagerMock,
            $this->visitorMock,
            $this->sessionMock,
            $this->genericMock,
            $this->visibilityMock,
            $this->dateTimeMock,
            $this->catalogProductHelperMock,
            $this->resourceMock,
            $this->dbMock
        );
    }

    /**
     * @return void
     */
    public function testGetExcludeProductIds()
    {
        $collection = $this->getMockBuilder('Magento\Catalog\Model\ResourceModel\Product\Compare\Item\Collection')
            ->disableOriginalConstructor()
            ->setMethods(['getEntityId'])
            ->getMock();
        $collection->expects($this->once())->method('getEntityId')->willReturn(1);

        $product = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();
        $product->expects($this->once())->method('getId')->willReturn(2);

        $this->catalogProductHelperMock->expects($this->once())->method('hasItems')->willReturn(true);
        $this->catalogProductHelperMock->expects($this->once())->method('getItemCollection')->willReturn([$collection]);

        $this->registryMock->expects($this->any())->method('registry')->willReturn($product);

        $this->assertEquals([1, 2], $this->compared->getExcludeProductIds());
    }
}
