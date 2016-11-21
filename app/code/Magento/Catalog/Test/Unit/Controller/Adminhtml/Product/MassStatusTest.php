<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product;

use Magento\Backend\Model\View\Result\Redirect;
use Magento\Catalog\Controller\Adminhtml\Product\Builder;
use Magento\Catalog\Controller\Adminhtml\Product\MassStatus;
use Magento\Catalog\Model\Indexer\Product\Price\Processor;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Action;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\ObjectManager\ObjectManager as Manager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Ui\Component\MassAction\Filter;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassStatusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $priceProcessorMock;

    /**
     * @var \Magento\Catalog\Controller\Adminhtml\Product\MassStatus
     */
    private $action;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $abstractDbMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $filterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $actionMock;

    protected function setUp()
    {
        $objectManagerMock = $this->getMockBuilder(Manager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManager($this);
        $this->priceProcessorMock = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()->getMock();

        $productBuilderMock = $this->getMockBuilder(Builder::class)->setMethods([
            'build',
        ])->disableOriginalConstructor()->getMock();

        $this->requestMock = $this->getMockBuilder(Http::class)->setMethods(
            ['getParam', 'getPost', 'getFullActionName', 'getPostValue']
        )->disableOriginalConstructor()->getMock();

        $productMock = $this->getMockBuilder(Product::class)->disableOriginalConstructor()
            ->setMethods(['getTypeId', 'getStoreId', '__sleep', '__wakeup'])->getMock();
        $productMock->expects($this->any())->method('getTypeId')->will($this->returnValue('simple'));
        $productMock->expects($this->any())->method('getStoreId')->will($this->returnValue('1'));
        $productBuilderMock->expects($this->any())->method('build')->will($this->returnValue($productMock));

        $resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultFactory = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultFactory->expects($this->atLeastOnce())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($resultRedirectMock);

        $this->abstractDbMock = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllIds', 'getResource'])
            ->getMock();
        $this->abstractDbMock->expects($this->any())
            ->method('getAllIds')
            ->willReturn([]);

        $this->filterMock = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCollection'])
            ->getMock();
        $this->filterMock->expects($this->any())
            ->method('getCollection')
            ->willReturn($this->abstractDbMock);
        $this->actionMock = $this->getMockBuilder(Action::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerMock->expects($this->any())->method('get')->willReturn($this->actionMock);
        $collectionFactoryMock = $this->getMockBuilder(ProductCollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $collectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->abstractDbMock);
        $this->requestMock = $this->getMockBuilder(Http::class)->setMethods(
            ['getParam', 'getPost', 'getFullActionName', 'getPostValue']
        )->disableOriginalConstructor()->getMock();

        $this->action = $objectManagerHelper->getObject(
            MassStatus::class,
            [
                'objectManager' => $objectManagerMock,
                'request' => $this->requestMock,
                'productBuilder' => $productBuilderMock,
                'filter' => $this->filterMock,
                'productPriceIndexerProcessor' => $this->priceProcessorMock,
                'collectionFactory' => $collectionFactoryMock,
                'resultFactory' => $resultFactory
            ]
        );

    }

    public function testMassStatusAction()
    {
        $storeId = 1;
        $status = Status::STATUS_DISABLED;
        $filters = [
            'store_id' => 2,
        ];

        $productIds = [3];

        $this->filterMock->expects($this->once())
            ->method('getCollection')
            ->willReturn($this->abstractDbMock);
        $this->abstractDbMock->expects($this->once())
            ->method('getAllIds')
            ->willReturn($productIds);
        $this->requestMock->expects($this->exactly(3))
            ->method('getParam')
            ->willReturnMap([
                ['store', 0, $storeId],
                ['status', null, $status],
                ['filters', [], $filters]
            ]);
        $this->actionMock->expects($this->once())
            ->method('updateAttributes');
        $this->priceProcessorMock->expects($this->once())
            ->method('reindexList');

        $this->action->execute();
    }
}
