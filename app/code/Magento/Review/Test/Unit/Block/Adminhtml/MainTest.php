<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Test\Unit\Block\Adminhtml;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Helper\View as ViewHelper;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Review\Block\Adminhtml\Main as MainBlock;
use Magento\Framework\DataObject;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

/**
 * Unit Test For Main Block
 *
 * Class \Magento\Review\Test\Unit\Block\Adminhtml\MainTest
 */
class MainTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MainBlock
     */
    protected $model;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerRepository;

    /**
     * @var ViewHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerViewHelper;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactory;

    public function testConstruct()
    {
        $this->customerRepository = $this
            ->getMockForAbstractClass(CustomerRepositoryInterface::class);
        $this->customerViewHelper = $this->createMock(ViewHelper::class);
        $this->collectionFactory = $this->createMock(CollectionFactory::class);
        $dummyCustomer = $this->getMockForAbstractClass(CustomerInterface::class);

        $this->customerRepository->expects($this->once())
            ->method('getById')
            ->with('customer id')
            ->will($this->returnValue($dummyCustomer));
        $this->customerViewHelper->expects($this->once())
            ->method('getCustomerName')
            ->with($dummyCustomer)
            ->will($this->returnValue(new DataObject()));
        $this->request = $this->getMockForAbstractClass(RequestInterface::class);
        $this->request->expects($this->at(0))
            ->method('getParam')
            ->with('customerId', false)
            ->will($this->returnValue('customer id'));
        $this->request->expects($this->at(1))
            ->method('getParam')
            ->with('productId', false)
            ->will($this->returnValue(false));
        $productCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($productCollection));

        $objectManagerHelper = new ObjectManagerHelper($this);
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
