<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product\ProductFrontendAction;

use Magento\Catalog\Api\Data\ProductFrontendActionInterface;
use Magento\Catalog\Model\ResourceModel\ProductFrontendAction\Collection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SynchronizerTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Catalog\Model\Product\ProductFrontendAction\Synchronizer */
    protected $model;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $sessionMock;

    /** @var \Magento\Customer\Model\Visitor|\PHPUnit_Framework_MockObject_MockObject */
    protected $visitorMock;

    /** @var \Magento\Catalog\Model\ProductFrontendActionFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $productFrontendActionFactoryMock;

    /** @var \Magento\Framework\EntityManager\EntityManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $entityManagerMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $collectionFactoryMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $frontendStorageConfigurationPoolMock;

    protected function setUp()
    {
        $this->sessionMock = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->visitorMock = $this->getMockBuilder(\Magento\Customer\Model\Visitor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productFrontendActionFactoryMock = $this
            ->getMockBuilder(\Magento\Catalog\Model\ProductFrontendActionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->entityManagerMock = $this->getMockBuilder(\Magento\Framework\EntityManager\EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionFactoryMock = $this
            ->getMockBuilder(\Magento\Catalog\Model\ResourceModel\ProductFrontendAction\CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->frontendStorageConfigurationPoolMock = $this
            ->getMockBuilder(\Magento\Catalog\Model\FrontendStorageConfigurationPool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            \Magento\Catalog\Model\Product\ProductFrontendAction\Synchronizer::class,
            [
                'session' => $this->sessionMock,
                'visitor' => $this->visitorMock,
                'productFrontendActionFactory' => $this->productFrontendActionFactoryMock,
                'entityManager' => $this->entityManagerMock,
                'collectionFactory' => $this->collectionFactoryMock,
                'frontendStorageConfigurationPool' => $this->frontendStorageConfigurationPoolMock
            ]
        );
    }

    public function testFilterProductActions()
    {
        $typeId = 'recently_compared_product';
        $productsData = [
            'website-1-1' => [
                'added_at' => 12,
                'product_id' => 1,
            ],
            'website-1-2' => [
                'added_at' => 13,
                'product_id' => '2',
            ],
            'website-2-3' => [
                'added_at' => 14,
                'product_id' => 3,
            ]
        ];
        $frontendConfiguration = $this->createMock(\Magento\Catalog\Model\FrontendStorageConfigurationInterface::class);
        $frontendConfiguration->expects($this->once())
            ->method('get')
            ->willReturn([
                'lifetime' => 2
            ]);
        $this->frontendStorageConfigurationPoolMock->expects($this->once())
            ->method('get')
            ->with('recently_compared_product')
            ->willReturn($frontendConfiguration);
        $action1 = $this->getMockBuilder(ProductFrontendActionInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $action2 = $this->getMockBuilder(ProductFrontendActionInterface::class)
            ->getMockForAbstractClass();

        $frontendAction = $this->createMock(ProductFrontendActionInterface::class);
        $collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionMock->expects($this->any())
            ->method('getCustomerId')
            ->willReturn(1);
        $this->visitorMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn(34);
        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collection);
        $collection->expects($this->once())
            ->method('addFilterByUserIdentities')
            ->with(1, 34);
        $collection->expects($this->at(1))
            ->method('addFieldToFilter')
            ->with('type_id', $typeId);
        $collection->expects($this->at(2))
            ->method('addFieldToFilter')
            ->with('product_id', [1, 2]);
        $iterator = new \IteratorIterator(new \ArrayIterator([$frontendAction]));
        $collection->expects($this->once())
            ->method('getIterator')
            ->willReturn($iterator);
        $this->entityManagerMock->expects($this->once())
            ->method('delete')
            ->with($frontendAction);
        $this->productFrontendActionFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->withConsecutive(
                [
                    [
                        'data' => [
                            'visitor_id' => null,
                            'customer_id' => 1,
                            'added_at' => 12,
                            'product_id' => 1,
                            'type_id' => 'recently_compared_product'
                        ]
                    ]
                ],
                [
                    [
                        'data' => [
                            'visitor_id' => null,
                            'customer_id' => 1,
                            'added_at' => 13,
                            'product_id' => 2,
                            'type_id' => 'recently_compared_product'
                        ]
                    ]
                ]
            )
            ->willReturnOnConsecutiveCalls($action1, $action2);
        $this->entityManagerMock->expects($this->exactly(2))
            ->method('save')
            ->withConsecutive([$action1], [$action2]);
        $this->model->syncActions($productsData, 'recently_compared_product');
    }
}
