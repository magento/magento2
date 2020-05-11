<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Block\Adminhtml\Edit\Address;

use Magento\Customer\Block\Adminhtml\Edit\Address\DeleteButton;
use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\ResourceModel\Address;
use Magento\Customer\Model\ResourceModel\AddressRepository;
use Magento\Customer\Ui\Component\Listing\Address\Column\Actions;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/** \Magento\Customer\Block\Adminhtml\Edit\Address\DeleteButton unit tests
 */
class DeleteButtonTest extends TestCase
{
    /**
     * @var AddressFactory|MockObject
     */
    private $addressFactory;

    /**
     * @var UrlInterface|MockObject
     */
    private $urlBuilder;

    /**
     * @var Address|MockObject
     */
    private $addressResourceModel;

    /**
     * @var RequestInterface|MockObject
     */
    private $request;

    /**
     * @var AddressRepository|MockObject
     */
    private $addressRepository;

    /**
     * @var DeleteButton
     */
    private $deleteButton;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->addressFactory = $this->getMockBuilder(AddressFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlBuilder = $this->getMockForAbstractClass(UrlInterface::class);
        $this->addressResourceModel = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockForAbstractClass(RequestInterface::class);
        $this->addressRepository = $this->getMockBuilder(
            AddressRepository::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->deleteButton = $objectManagerHelper->getObject(
            DeleteButton::class,
            [
                'addressFactory' => $this->addressFactory,
                'urlBuilder' => $this->urlBuilder,
                'addressResourceModel' => $this->addressResourceModel,
                'request' => $this->request,
                'addressRepository' => $this->addressRepository,
            ]
        );
    }

    /**
     * Unit test for \Magento\Customer\Block\Adminhtml\Edit\Address\DeleteButton::getButtonData() method
     */
    public function testGetButtonData()
    {
        $addressId = 1;
        $customerId = 2;

        /** @var \Magento\Customer\Model\Address|MockObject $address */
        $address = $this->getMockBuilder(\Magento\Customer\Model\Address::class)
            ->disableOriginalConstructor()
            ->getMock();
        $address->expects($this->atLeastOnce())->method('getEntityId')->willReturn($addressId);
        $address->expects($this->atLeastOnce())->method('getCustomerId')->willReturn($customerId);
        $this->addressFactory->expects($this->atLeastOnce())->method('create')->willReturn($address);
        $this->request->expects($this->atLeastOnce())->method('getParam')->with('entity_id')
            ->willReturn($addressId);
        $this->addressResourceModel->expects($this->atLeastOnce())->method('load')->with($address, $addressId);
        $this->addressRepository->expects($this->atLeastOnce())->method('getById')->with($addressId)
            ->willReturn($address);
        $this->urlBuilder->expects($this->atLeastOnce())->method('getUrl')
            ->with(
                Actions::CUSTOMER_ADDRESS_PATH_DELETE,
                ['parent_id' => $customerId, 'id' => $addressId]
            )->willReturn('url');

        $buttonData = $this->deleteButton->getButtonData();
        $this->assertEquals('Delete', (string)$buttonData['label']);
    }
}
