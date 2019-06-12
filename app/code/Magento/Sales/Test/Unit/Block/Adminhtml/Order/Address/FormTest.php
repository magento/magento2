<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Block\Adminhtml\Order\Address;

use Magento\Customer\Model\Metadata\Form as CustomerForm;
use Magento\Customer\Model\Metadata\FormFactory as CustomerFormFactory;
use Magento\Directory\Model\ResourceModel\Country\Collection;
use Magento\Framework\Data\Form as DataForm;
use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Framework\Data\Form\Element\Select;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Block\Adminhtml\Order\Address\Form;
use Magento\Sales\Model\AdminOrder\Create;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Backend\Model\Session\Quote as QuoteSession;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FormTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Form
     */
    private $addressBlock;

    /**
     * @var MockObject
     */
    private $formFactory;

    /**
     * @var MockObject
     */
    private $customerFormFactory;

    /**
     * @var MockObject
     */
    private $coreRegistry;

    /**
     * @var MockObject
     */
    private $countriesCollection;

    /**
     * @var QuoteSession|MockObject
     */
    private $sessionQuote;

    /**
     * @var Create|MockObject
     */
    private $orderCreate;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->formFactory = $this->createMock(FormFactory::class);
        $this->customerFormFactory = $this->createMock(CustomerFormFactory::class);
        $this->coreRegistry = $this->createMock(Registry::class);
        $this->countriesCollection = $this->createMock(
            Collection::class
        );
        $this->sessionQuote = $this->getMockBuilder(QuoteSession::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStoreId', 'getStore'])
            ->getMock();

        $this->orderCreate = $this->getMockBuilder(Create::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderCreate->method('getSession')
            ->willReturn($this->sessionQuote);

        $this->addressBlock = $objectManager->getObject(
            Form::class,
            [
                '_formFactory' => $this->formFactory,
                '_customerFormFactory' => $this->customerFormFactory,
                '_coreRegistry' => $this->coreRegistry,
                'countriesCollection' => $this->countriesCollection,
                'sessionQuote' => $this->sessionQuote,
                '_orderCreate' => $this->orderCreate
            ]
        );
    }

    public function testGetForm()
    {
        $storeId = 5;
        $form = $this->createMock(DataForm::class);
        $fieldset = $this->createMock(Fieldset::class);
        $addressForm = $this->createMock(CustomerForm::class);
        $address = $this->createMock(Address::class);
        $select = $this->createMock(Select::class);
        $order = $this->createMock(Order::class);

        $this->formFactory->method('create')
            ->willReturn($form);
        $form->method('addFieldset')
            ->willReturn($fieldset);
        $this->customerFormFactory->method('create')
            ->willReturn($addressForm);
        $addressForm->method('getAttributes')
            ->willReturn([]);
        $this->coreRegistry->method('registry')
            ->willReturn($address);
        $form->method('getElement')
            ->willReturnOnConsecutiveCalls(
                $select,
                $select,
                $select,
                $select,
                $select,
                $select,
                $select,
                null
            );

        $address->method('getOrder')
            ->willReturn($order);
        $order->method('getStoreId')
            ->willReturn($storeId);
        $this->sessionQuote->method('getStoreId')
            ->willReturn($storeId);
        $this->countriesCollection->method('loadByStore')
            ->with($storeId)
            ->willReturn($this->countriesCollection);

        $this->addressBlock->getForm();
    }
}
