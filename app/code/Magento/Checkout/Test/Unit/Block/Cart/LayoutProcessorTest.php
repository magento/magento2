<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Block\Cart;

class LayoutProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Checkout\Block\Cart\LayoutProcessor
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $merger;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $countryCollection;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $regionCollection;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSession;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerRepository;

    protected function setUp()
    {
        $this->merger = $this->getMock('\Magento\Checkout\Block\Checkout\AttributeMerger', [], [], '', false);
        $this->countryCollection = $this->getMock(
            '\Magento\Directory\Model\Resource\Country\Collection',
            [],
            [],
            '',
            false
        );
        $this->regionCollection = $this->getMock(
            '\Magento\Directory\Model\Resource\Region\Collection',
            [],
            [],
            '',
            false
        );
        $this->customerSession = $this->getMock('\Magento\Customer\Model\Session', [], [], '', false);
        $this->customerRepository = $this->getMock('\Magento\Customer\Api\CustomerRepositoryInterface');

        $this->model = new \Magento\Checkout\Block\Cart\LayoutProcessor(
            $this->merger,
            $this->countryCollection,
            $this->regionCollection,
            $this->customerSession,
            $this->customerRepository
        );
    }

    public function testProcess()
    {
        $customerId = 100;
        $city = 'New York';
        $countryId = 'US';
        $regionId = 'NY';
        $postcode = '04086';
        $countries = [];
        $regions = [];

        $layout = [];
        $layout['components']['block-summary']['children']['block-shipping']['children']
        ['address-fieldsets']['children'] = [
            'fieldOne' => ['param' => 'value'],
            'fieldTwo' => ['param' => 'value']
        ];
        $layoutPointer = &$layout['components']['block-summary']['children']['block-shipping']
        ['children']['address-fieldsets']['children'];

        $this->customerSession->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $this->customerSession->expects($this->once())->method('getCustomerId')->willReturn($customerId);

        $customerAddressMock = $this->getMock('\Magento\Customer\Api\Data\AddressInterface');
        $customerAddressMock->expects($this->once())->method('isDefaultShipping')->willReturn(true);
        $customerAddressMock->expects($this->once())->method('getCity')->willReturn($city);
        $customerAddressMock->expects($this->once())->method('getCountryId')->willReturn($countryId);
        $customerAddressMock->expects($this->once())->method('getRegionId')->willReturn($regionId);
        $customerAddressMock->expects($this->once())->method('getPostcode')->willReturn($postcode);

        $customerMock = $this->getMock('\Magento\Customer\Api\Data\CustomerInterface');
        $customerMock->expects($this->exactly(2))->method('getAddresses')->willReturn([$customerAddressMock]);

        $this->customerRepository->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($customerMock);

        $this->countryCollection->expects($this->once())->method('load')->willReturnSelf();
        $this->countryCollection->expects($this->once())->method('toOptionArray')->willReturn($countries);

        $this->regionCollection->expects($this->once())->method('load')->willReturnSelf();
        $this->regionCollection->expects($this->once())->method('toOptionArray')->willReturn($regions);

        $layoutMerged = $layout;
        $layoutMerged['components']['block-summary']['children']['block-shipping']['children']
        ['address-fieldsets']['children']['fieldThree'] = ['param' => 'value'];
        $layoutMergedPointer = &$layoutMerged['components']['block-summary']['children']['block-shipping']
        ['children']['address-fieldsets']['children'];

        $elements = [
            'city' => [
                'visible' => false,
                'formElement' => 'input',
                'label' => __('City'),
                'value' => 'New York'
            ],
            'country_id' => [
                'visible' => 1,
                'formElement' => 'select',
                'label' => __('Country'),
                'options' => [],
                'value' => 'US'
            ],
            'region_id' => [
                'visible' => 1,
                'formElement' => 'select',
                'label' => __('State/Province'),
                'options' => [],
                'value' => 'NY'
            ],
            'postcode' => [
                'visible' => 1,
                'formElement' => 'input',
                'label' => __('Zip/Postal Code'),
                'value' => '04086'
            ]
        ];

        $this->merger->expects($this->once())
            ->method('merge')
            ->with($elements, 'checkoutProvider', 'shippingAddress', $layoutPointer)
            ->willReturn($layoutMergedPointer);

        $this->assertEquals($layoutMerged, $this->model->process($layout));
    }
}
