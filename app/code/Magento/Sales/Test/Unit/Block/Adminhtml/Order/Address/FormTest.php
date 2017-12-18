<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Block\Adminhtml\Order\Address;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FormTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Block\Adminhtml\Order\Address\Form
     */
    private $addressBlock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $formFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $customerFormFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $coreRegistryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $countriesCollection;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->formFactoryMock = $this->createMock(\Magento\Framework\Data\FormFactory::class);
        $this->customerFormFactoryMock = $this->createMock(\Magento\Customer\Model\Metadata\FormFactory::class);
        $this->coreRegistryMock = $this->createMock(\Magento\Framework\Registry::class);
        $this->countriesCollection = $this->createMock(
            \Magento\Directory\Model\ResourceModel\Country\Collection::class
        );

        $this->addressBlock = $objectManager->getObject(
            \Magento\Sales\Block\Adminhtml\Order\Address\Form::class,
            [
                '_formFactory' => $this->formFactoryMock,
                '_customerFormFactory' => $this->customerFormFactoryMock,
                '_coreRegistry' => $this->coreRegistryMock,
                'countriesCollection' => $this->countriesCollection,
            ]
        );
    }

    public function testGetForm()
    {
        $formMock = $this->createMock(\Magento\Framework\Data\Form::class);
        $fieldsetMock = $this->createMock(\Magento\Framework\Data\Form\Element\Fieldset::class);
        $addressFormMock = $this->createMock(\Magento\Customer\Model\Metadata\Form::class);
        $addressMock = $this->createMock(\Magento\Sales\Model\Order\Address::class);
        $selectMock = $this->createMock(\Magento\Framework\Data\Form\Element\Select::class);
        $orderMock = $this->createMock(\Magento\Sales\Model\Order::class);

        $this->formFactoryMock->expects($this->atLeastOnce())->method('create')->willReturn($formMock);
        $formMock->expects($this->atLeastOnce())->method('addFieldset')->willReturn($fieldsetMock);
        $this->customerFormFactoryMock->expects($this->atLeastOnce())->method('create')->willReturn($addressFormMock);
        $addressFormMock->expects($this->atLeastOnce())->method('getAttributes')->willReturn([]);
        $this->coreRegistryMock->expects($this->atLeastOnce())->method('registry')->willReturn($addressMock);
        $formMock->expects($this->atLeastOnce())->method('getElement')->willReturnOnConsecutiveCalls(
            $selectMock,
            $selectMock,
            $selectMock,
            $selectMock,
            $selectMock,
            $selectMock,
            $selectMock,
            null
        );
        $addressMock->expects($this->once())->method('getOrder')->willReturn($orderMock);
        $orderMock->expects($this->once())->method('getStoreId')->willReturn(5);
        $this->countriesCollection->expects($this->atLeastOnce())->method('loadByStore')
            ->willReturn($this->countriesCollection);

        $this->addressBlock->getForm();
    }
}
