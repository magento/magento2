<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Block\Adminhtml\Edit\Address;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class for \Magento\Customer\Block\Adminhtml\Edit\Address\DeleteButton unit tests
 */
class DeleteButtonTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Model\AddressFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressFactory;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilder;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressResourceModel;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Customer\Model\ResourceModel\AddressRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressRepository;

    /**
     * @var \Magento\Customer\Block\Adminhtml\Edit\Address\DeleteButton
     */
    private $deleteButton;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->addressFactory = $this->getMockBuilder(\Magento\Customer\Model\AddressFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlBuilder = $this->getMockForAbstractClass(\Magento\Framework\UrlInterface::class);
        $this->addressResourceModel = $this->getMockBuilder(\Magento\Customer\Model\ResourceModel\Address::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockForAbstractClass(\Magento\Framework\App\RequestInterface::class);
        $this->addressRepository = $this->getMockBuilder(
            \Magento\Customer\Model\ResourceModel\AddressRepository::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->deleteButton = $objectManagerHelper->getObject(
            \Magento\Customer\Block\Adminhtml\Edit\Address\DeleteButton::class,
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

        /** @var \Magento\Customer\Model\Address|\PHPUnit_Framework_MockObject_MockObject $address */
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
                \Magento\Customer\Ui\Component\Listing\Address\Column\Actions::CUSTOMER_ADDRESS_PATH_DELETE,
                ['parent_id' => $customerId, 'id' => $addressId]
            )->willReturn('url');

        $buttonData = $this->deleteButton->getButtonData();
        $this->assertEquals('Delete', (string)$buttonData['label']);
    }
}
