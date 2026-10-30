<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Review\Test\Unit\Block\Adminhtml;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Helper\View as ViewHelper;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Review\Block\Adminhtml\Main as MainBlock;
use Magento\Backend\Block\Template\Context;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit Test For Main Block
 *
 * Class \Magento\Review\Test\Unit\Block\Adminhtml\MainTest
 */
class MainTest extends TestCase
{
    /**
     * @var MainBlock
     */
    protected $model;

    /**
     * @var RequestInterface|MockObject
     */
    protected $request;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    protected $customerRepository;

    /**
     * @var ViewHelper|MockObject
     */
    protected $customerViewHelper;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $collectionFactory;

    /**
     * @return void
     */
    public function testConstruct(): void
    {
        $this->customerRepository = $this->createMock(CustomerRepositoryInterface::class);
        $this->customerViewHelper = $this->createMock(ViewHelper::class);
        $this->collectionFactory = $this->createMock(CollectionFactory::class);
        $dummyCustomer = $this->createMock(CustomerInterface::class);

        $this->customerRepository->expects($this->once())
            ->method('getById')
            ->with('customer id')
            ->willReturn($dummyCustomer);
        $this->customerViewHelper->expects($this->once())
            ->method('getCustomerName')
            ->with($dummyCustomer)
            ->willReturn(new DataObject());
        $this->request = $this->createMock(RequestInterface::class);
        $this->request
            ->method('getParam')
            ->willReturnCallback(function ($arg1, $arg2) {
                if ($arg1 === 'customerId' && $arg2 == false) {
                    return 'customer id';
                }
                if ($arg1 === 'productId' && $arg2 == false) {
                    return false;
                }
            });
        $productCollection = $this->createMock(Collection::class);
        $this->collectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($productCollection);

        $objectManagerHelper = new ObjectManagerHelper($this);

        // Fix ObjectManager initialization issue using existing helper method
        $objects = [
            [
                Context::class,
                $this->createMock(Context::class)
            ]
        ];
        $objectManagerHelper->prepareObjectManager($objects);

        $this->model = $objectManagerHelper->getObject(
            MainBlock::class,
            [
                'request' => $this->request,
                'customerRepository' => $this->customerRepository,
                'customerViewHelper' => $this->customerViewHelper,
                'productCollectionFactory' => $this->collectionFactory
            ]
        );
    }
}
