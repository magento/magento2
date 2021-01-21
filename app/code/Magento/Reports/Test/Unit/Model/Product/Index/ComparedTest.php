<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Unit\Model\Product\Index;

use Magento\Reports\Model\Product\Index\Compared;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ComparedTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Reports\Model\Product\Index\Compared
     */
    protected $compared;

    /**
     * @var \Magento\Framework\Model\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $registryMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Customer\Model\Visitor|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $visitorMock;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $sessionMock;

    /**
     * @var \Magento\Framework\Session\Generic|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $genericMock;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $visibilityMock;

    /**
     * @var \Magento\Framework\Stdlib\DateTime|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $dateTimeMock;

    /**
     * @var \Magento\Catalog\Helper\Product\Compare|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $catalogProductHelperMock;

    /**
     * @var \Magento\Framework\Model\ResourceModel\AbstractResource|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Framework\Data\Collection\AbstractDb|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $dbMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(\Magento\Framework\Model\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->registryMock = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->getMock();
        $this->visitorMock = $this->getMockBuilder(\Magento\Customer\Model\Visitor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionMock = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->genericMock = $this->getMockBuilder(\Magento\Framework\Session\Generic::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->visibilityMock = $this->getMockBuilder(\Magento\Catalog\Model\Product\Visibility::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dateTimeMock = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime::class)
            ->getMock();
        $this->catalogProductHelperMock = $this->getMockBuilder(\Magento\Catalog\Helper\Product\Compare::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resourceMock = $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\AbstractResource::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIdFieldName', '_construct', 'getConnection'])
            ->getMockForAbstractClass();
        $this->dbMock = $this->getMockBuilder(\Magento\Framework\Data\Collection\AbstractDb::class)
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
        $collection = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\Compare\Item\Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEntityId'])
            ->getMock();
        $collection->expects($this->once())->method('getEntityId')->willReturn(1);

        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
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
