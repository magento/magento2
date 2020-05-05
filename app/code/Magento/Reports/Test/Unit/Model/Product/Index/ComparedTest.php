<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Reports\Test\Unit\Model\Product\Index;

use Magento\Catalog\Helper\Product\Compare;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Compare\Item\Collection;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Visitor;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Session\Generic;
use Magento\Framework\Stdlib\DateTime;
use Magento\Reports\Model\Product\Index\Compared;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ComparedTest extends TestCase
{
    /**
     * @var Compared
     */
    protected $compared;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var Registry|MockObject
     */
    protected $registryMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManagerMock;

    /**
     * @var Visitor|MockObject
     */
    protected $visitorMock;

    /**
     * @var Session|MockObject
     */
    protected $sessionMock;

    /**
     * @var Generic|MockObject
     */
    protected $genericMock;

    /**
     * @var Visibility|MockObject
     */
    protected $visibilityMock;

    /**
     * @var DateTime|MockObject
     */
    protected $dateTimeMock;

    /**
     * @var Compare|MockObject
     */
    protected $catalogProductHelperMock;

    /**
     * @var AbstractResource|MockObject
     */
    protected $resourceMock;

    /**
     * @var AbstractDb|MockObject
     */
    protected $dbMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->registryMock = $this->getMockBuilder(Registry::class)
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMock();
        $this->visitorMock = $this->getMockBuilder(Visitor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->genericMock = $this->getMockBuilder(Generic::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->visibilityMock = $this->getMockBuilder(Visibility::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dateTimeMock = $this->getMockBuilder(DateTime::class)
            ->getMock();
        $this->catalogProductHelperMock = $this->getMockBuilder(Compare::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resourceMock = $this->getMockBuilder(AbstractResource::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIdFieldName', '_construct', 'getConnection'])
            ->getMockForAbstractClass();
        $this->dbMock = $this->getMockBuilder(AbstractDb::class)
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
        $collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEntityId'])
            ->getMock();
        $collection->expects($this->once())->method('getEntityId')->willReturn(1);

        $product = $this->getMockBuilder(Product::class)
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
