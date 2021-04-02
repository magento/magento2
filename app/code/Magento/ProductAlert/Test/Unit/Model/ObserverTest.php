<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ProductAlert\Test\Unit\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\ProductAlert\Model\Email;
use Magento\ProductAlert\Model\EmailFactory;
use Magento\ProductAlert\Model\Observer;
use Magento\ProductAlert\Model\ProductSalability;
USE Magento\ProductAlert\Model\ResourceModel\Price\Collection as PriceCollection;
USE Magento\ProductAlert\Model\ResourceModel\Stock\Collection as StockCollection;
use Magento\Sitemap\Model\ResourceModel\Sitemap\Collection;
use Magento\Sitemap\Model\ResourceModel\Sitemap\CollectionFactory;
use Magento\Sitemap\Model\Sitemap;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class ObserverTest
 *
 * Is used to test Product Alert Observer
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ObserverTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Observer
     */
    private $observer;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var CollectionFactory|MockObject
     */
    private $collectionFactoryMock;

    /**
     * @var TransportBuilder|MockObject
     */
    private $transportBuilderMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var StateInterface|MockObject
     */
    private $inlineTranslationMock;

    /**
     * @var Collection|MockObject
     */
    private $sitemapCollectionMock;

    /**
     * @var Sitemap|MockObject
     */
    private $sitemapMock;

    /**
     * @var EmailFactory|MockObject
     */
    private $emailFactoryMock;

    /**
     * @var Email|MockObject
     */
    private $emailMock;

    /**
     * @var \Magento\ProductAlert\Model\ResourceModel\Price\CollectionFactory|MockObject
     */
    private $priceColFactoryMock;

    /**
     * @var \Magento\ProductAlert\Model\ResourceModel\Stock\CollectionFactory|MockObject
     */
    private $stockColFactoryMock;

    /**
     * @var Website|MockObject
     */
    private $websiteMock;

    /**
     * @var Store|MockObject
     */
    private $storeMock;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    private $customerRepositoryMock;

    /**
     * @var ProductRepositoryInterface|MockObject
     */
    private $productRepositoryMock;

    /**
     * @var Product|MockObject
     */
    private $productMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var ProductSalability|MockObject
     */
    private $productSalabilityMock;

    /**
     * @var int
     */
    private $bunchSize = 100;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMock();
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMock();
        $this->collectionFactoryMock = $this->getMockBuilder(
            CollectionFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->transportBuilderMock = $this->getMockBuilder(TransportBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMock();
        $this->inlineTranslationMock = $this->getMockBuilder(StateInterface::class)
            ->getMock();
        $this->sitemapCollectionMock = $this->createPartialMock(
            Collection::class,
            ['getIterator']
        );
        $this->sitemapMock = $this->createPartialMock(Sitemap::class, ['generateXml']);

        $this->emailFactoryMock = $this->getMockBuilder(
            EmailFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->emailMock = $this->getMockBuilder(Email::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->priceColFactoryMock = $this->createMock(
            \Magento\ProductAlert\Model\ResourceModel\Price\CollectionFactory::class
        );
        $this->stockColFactoryMock = $this->createMock(
            \Magento\ProductAlert\Model\ResourceModel\Stock\CollectionFactory::class
        );

        $this->websiteMock = $this->createPartialMock(
            Website::class,
            ['getDefaultGroup', 'getDefaultStore']
        );
        $this->storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDefaultStore', 'getId', 'setWebsiteId'])
            ->getMock();
        $this->customerRepositoryMock = $this->getMockBuilder(CustomerRepositoryInterface::class)
            ->getMock();
        $this->productRepositoryMock = $this->getMockBuilder(ProductRepositoryInterface::class)
            ->getMock();
        $this->productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'setCustomerGroupId',
                    'getFinalPrice',
                ]
            )->getMock();

        $this->productSalabilityMock = $this->createPartialMock(ProductSalability::class, ['isSalable']);

        $this->objectManager = new ObjectManager($this);
        $this->observer = $this->objectManager->getObject(
            Observer::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'collectionFactory' => $this->collectionFactoryMock,
                'storeManager' => $this->storeManagerMock,
                'transportBuilder' => $this->transportBuilderMock,
                'inlineTranslation' => $this->inlineTranslationMock,
                'emailFactory' => $this->emailFactoryMock,
                'priceColFactory' => $this->priceColFactoryMock,
                'stockColFactory' => $this->stockColFactoryMock,
                'customerRepository' => $this->customerRepositoryMock,
                'productRepository' => $this->productRepositoryMock,
                'productSalability' => $this->productSalabilityMock,
                'bunchSize' => $this->bunchSize
            ]
        );
    }

    public function testGetWebsitesThrowsException()
    {
        $message = 'get website exception';
        $this->expectException(\Exception::class);
        $this->expectErrorMessage($message);

        $this->scopeConfigMock->expects($this->any())->method('isSetFlag')->willReturn(false);
        $this->emailFactoryMock->expects($this->once())->method('create')->willReturn($this->emailMock);
        $this->storeManagerMock->expects($this->once())
            ->method('getWebsites')
            ->willThrowException(new \Exception($message));

        $this->observer->process();
    }

    public function testProcessPriceThrowsException()
    {
        $message = 'create collection exception';
        $this->expectException(\Exception::class);
        $this->expectErrorMessage($message);

        $this->emailFactoryMock->expects($this->once())->method('create')->willReturn($this->emailMock);

        $this->storeManagerMock->expects($this->once())->method('getWebsites')->willReturn([$this->websiteMock]);
        $this->websiteMock->expects($this->any())->method('getDefaultGroup')->willReturn($this->storeMock);
        $this->storeMock->expects($this->any())->method('getDefaultStore')->willReturnSelf();

        $this->scopeConfigMock->expects($this->once())->method('getValue')->willReturn(true);

        $this->priceColFactoryMock->expects($this->once())
            ->method('create')
            ->willThrowException(new \Exception($message));

        $this->observer->process();
    }

    public function testProcessPriceCustomerRepositoryThrowsException()
    {
        $message = 'no such entity exception';
        $this->expectException(\Exception::class);
        $this->expectErrorMessage($message);

        $this->emailFactoryMock->expects($this->once())->method('create')->willReturn($this->emailMock);
        $this->storeManagerMock->expects($this->once())->method('getWebsites')->willReturn([$this->websiteMock]);
        $this->websiteMock->method('getDefaultGroup')->willReturn($this->storeMock);
        $this->storeMock->method('getDefaultStore')->willReturnSelf();
        $this->scopeConfigMock->expects($this->once())->method('getValue')->willReturn(true);

        $collectionMock = $this->createCollectionMock(PriceCollection::class);
        $this->priceColFactoryMock->expects($this->once())->method('create')->willReturn($collectionMock);
        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->willThrowException(new \Exception($message));

        $this->observer->process();
    }

    public function testProcessPriceEmailThrowsException()
    {
        $message = 'send exception';
        $this->expectException(\Exception::class);
        $this->expectErrorMessage($message);
        $id = 1;

        $this->emailFactoryMock->expects($this->once())->method('create')->willReturn($this->emailMock);

        $this->storeManagerMock->expects($this->once())->method('getWebsites')->willReturn([$this->websiteMock]);
        $this->websiteMock->expects($this->any())->method('getDefaultGroup')->willReturn($this->storeMock);
        $this->storeMock->expects($this->any())->method('getDefaultStore')->willReturnSelf();
        $this->websiteMock->expects($this->once())->method('getDefaultStore')->willReturn($this->storeMock);
        $this->storeMock->expects($this->any())->method('getId')->willReturn(2);
        $this->storeMock->expects($this->any())->method('setWebsiteId')->willReturnSelf();

        $this->scopeConfigMock->expects($this->once())->method('getValue')->willReturn(true);

        $collectionMock = $this->createCollectionMock(PriceCollection::class, $id);
        $this->priceColFactoryMock->expects($this->once())->method('create')->willReturn($collectionMock);

        $customerMock = $this->getMockForAbstractClass(CustomerInterface::class);
        $this->customerRepositoryMock->expects($this->once())->method('getById')->willReturn($customerMock);

        $this->productMock->expects($this->once())->method('setCustomerGroupId')->willReturnSelf();
        $this->productMock->expects($this->once())->method('getFinalPrice')->willReturn('655.99');
        $this->productRepositoryMock->expects($this->once())->method('getById')->willReturn($this->productMock);

        $this->emailMock->expects($this->once())->method('send')->willThrowException(new \Exception($message));

        $this->observer->process();
    }

    public function testProcessStockThrowsException()
    {
        $message = 'create collection exception';
        $this->expectException(\Exception::class);
        $this->expectErrorMessage($message);

        $this->emailFactoryMock->expects($this->once())->method('create')->willReturn($this->emailMock);

        $this->storeManagerMock->expects($this->once())->method('getWebsites')->willReturn([$this->websiteMock]);
        $this->websiteMock->expects($this->any())->method('getDefaultGroup')->willReturn($this->storeMock);
        $this->storeMock->expects($this->any())->method('getDefaultStore')->willReturnSelf();

        $this->scopeConfigMock->expects($this->at(0))->method('getValue')->willReturn(false);
        $this->scopeConfigMock->expects($this->at(1))->method('getValue')->willReturn(true);

        $this->stockColFactoryMock->expects($this->once())
            ->method('create')
            ->willThrowException(new \Exception($message));

        $this->observer->process();
    }

    public function testProcessStockCustomerRepositoryThrowsException()
    {
        $message = 'no such entity exception';
        $this->expectException(\Exception::class);
        $this->expectErrorMessage($message);

        $this->emailFactoryMock->expects($this->once())->method('create')->willReturn($this->emailMock);

        $this->storeManagerMock->expects($this->once())->method('getWebsites')->willReturn([$this->websiteMock]);
        $this->websiteMock->expects($this->any())->method('getDefaultGroup')->willReturn($this->storeMock);
        $this->storeMock->expects($this->any())->method('getDefaultStore')->willReturnSelf();

        $this->scopeConfigMock->expects($this->at(0))->method('getValue')->willReturn(false);
        $this->scopeConfigMock->expects($this->at(1))->method('getValue')->willReturn(true);

        $collectionMock = $this->createCollectionMock(StockCollection::class);
        $collectionMock->expects($this->once())
            ->method('addStatusFilter')
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('setCustomerOrder')
            ->willReturnSelf();
        $this->stockColFactoryMock->expects($this->once())->method('create')->willReturn($collectionMock);

        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->willThrowException(new \Exception($message));

        $this->observer->process();
    }

    public function testProcessStockEmailThrowsException()
    {
        $message = 'send exception';
        $this->expectException(\Exception::class);
        $this->expectErrorMessage($message);

        $this->emailFactoryMock->expects($this->once())->method('create')->willReturn($this->emailMock);

        $this->storeManagerMock->expects($this->once())->method('getWebsites')->willReturn([$this->websiteMock]);
        $this->websiteMock->expects($this->any())->method('getDefaultGroup')->willReturn($this->storeMock);
        $this->storeMock->expects($this->any())->method('getDefaultStore')->willReturnSelf();
        $this->websiteMock->expects($this->once())->method('getDefaultStore')->willReturn($this->storeMock);
        $this->storeMock->expects($this->any())->method('getId')->willReturn(2);

        $this->scopeConfigMock->expects($this->at(0))->method('getValue')->willReturn(false);
        $this->scopeConfigMock->expects($this->at(1))->method('getValue')->willReturn(true);

        $collectionMock = $this->createCollectionMock(StockCollection::class);
        $collectionMock->expects($this->once())
            ->method('addStatusFilter')
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('setCustomerOrder')
            ->willReturnSelf();
        $this->stockColFactoryMock->expects($this->once())->method('create')->willReturn($collectionMock);

        $customerMock = $this->getMockForAbstractClass(CustomerInterface::class);
        $this->customerRepositoryMock->expects($this->once())->method('getById')->willReturn($customerMock);

        $this->productMock->expects($this->once())->method('setCustomerGroupId')->willReturnSelf();
        $this->productSalabilityMock->expects($this->once())->method('isSalable')->willReturn(false);
        $this->productRepositoryMock->expects($this->once())->method('getById')->willReturn($this->productMock);

        $this->emailMock->expects($this->once())->method('send')->willThrowException(new \Exception($message));

        $this->observer->process();
    }

    /**
     * Create mock for collection
     *
     * @param string $type
     * @param int $customerId
     * @return MockObject
     */
    private function createCollectionMock(string $type, int $customerId = 1): MockObject
    {
        $items = [
            new DataObject(['customer_id' => $customerId])
        ];
        $collectionMock = $this->createMock($type);
        $collectionMock->expects($this->once())
            ->method('addWebsiteFilter')
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('setCustomerOrder')
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('addOrder')
            ->with('product_id')
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('setPageSize')
            ->with($this->bunchSize)
            ->willReturnSelf();
        $collectionMock->method('getLastPageNumber')
            ->willReturn(1);
        $collectionMock->expects($this->once())
            ->method('clear');
        $collectionMock->expects($this->once())
            ->method('setCurPage')
            ->with(1);
        $collectionMock->method('getIterator')
            ->willReturn(new \ArrayIterator($items));

        return $collectionMock;
    }
}
