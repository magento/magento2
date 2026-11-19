<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\AdminOrder;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\Data\ValidationResultsInterface;
use Magento\Customer\Model\Customer\Mapper;
use Magento\Customer\Model\Metadata\Form;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Request\Http;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\AdminOrder\Create;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreateTaxvatTest extends TestCase
{
    /**
     * Omitted fields must retain their value, while explicit edits and clearing must still work.
     */
    #[DataProvider('accountDataProvider')]
    public function testSetAccountData(array $account, ?string $restored, ?string $expected): void
    {
        $customer = $this->createMock(CustomerInterface::class);
        $customer->method('getEmail')->willReturn('customer@example.com');
        $customer->method('getTaxvat')->willReturn('original');
        $updatedCustomer = $this->createMock(CustomerInterface::class);
        $quote = $this->createMock(Quote::class);
        $quote->method('getCustomer')->willReturn($customer);
        $quote->method('getStoreId')->willReturn(1);
        $quote->expects(self::once())->method('addData')->with(['customer_taxvat' => $expected]);
        $attribute = $this->createMock(AttributeMetadataInterface::class);
        $attribute->method('getAttributeCode')->willReturn(CustomerInterface::TAXVAT);
        $form = $this->createMock(Form::class);
        $form->method('prepareRequest')->willReturn($this->createMock(Http::class));
        $form->method('extractData')->willReturn([]);
        $form->method('restoreData')->willReturn([CustomerInterface::TAXVAT => $restored]);
        $form->method('getAttributes')->willReturn([$attribute]);
        $formFactory = $this->createMock(FormFactory::class);
        $formFactory->method('create')->with(
            CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            'adminhtml_checkout',
            [CustomerInterface::TAXVAT => 'original'],
            false,
            Form::DONT_IGNORE_INVISIBLE
        )->willReturn($form);
        $customerFactory = $this->createMock(CustomerInterfaceFactory::class);
        $customerFactory->method('create')->willReturn($updatedCustomer);
        $mapper = $this->createMock(Mapper::class);
        $mapper->method('toFlatArray')->willReturnMap([
            [$customer, [CustomerInterface::TAXVAT => 'original']],
            [$updatedCustomer, [CustomerInterface::TAXVAT => $expected]],
        ]);
        $helper = $this->createMock(DataObjectHelper::class);
        $helper->expects(self::once())->method('populateWithArray')->with(
            $updatedCustomer,
            [CustomerInterface::TAXVAT => $expected],
            CustomerInterface::class
        );
        $create = (new ObjectManager($this))->getObject(Create::class, [
            'metadataFormFactory' => $formFactory,
            'customerFactory' => $customerFactory,
            'customerMapper' => $mapper,
            'dataObjectHelper' => $helper,
        ]);
        $create->setQuote($quote);

        self::assertSame($create, $create->setAccountData($account));
    }

    /**
     * Account requests and their restored and expected taxvat values.
     */
    public static function accountDataProvider(): array
    {
        return [
            'omitted' => [[], null, 'original'],
            'edited' => [[CustomerInterface::TAXVAT => 'updated'], 'updated', 'updated'],
            'empty' => [[CustomerInterface::TAXVAT => ''], '', ''],
            'null' => [[CustomerInterface::TAXVAT => null], null, null],
            'zero' => [[CustomerInterface::TAXVAT => '0'], '0', '0'],
        ];
    }

    /**
     * Customer validation must not clear taxvat when it is absent from the account request.
     */
    #[DataProvider('validationDataProvider')]
    public function testValidateCustomerData(array $account, array $expected): void
    {
        $customer = $this->createMock(CustomerInterface::class);
        $mapper = $this->createMock(Mapper::class);
        $mapper->method('toFlatArray')->willReturn([CustomerInterface::TAXVAT => 'original']);
        $form = $this->createMock(Form::class);
        $form->method('prepareRequest')->willReturn($this->createMock(Http::class));
        $form->method('extractData')->willReturn([]);
        $form->method('restoreData')->willReturn([CustomerInterface::TAXVAT => null]);
        $formFactory = $this->createMock(FormFactory::class);
        $formFactory->method('create')->willReturn($form);
        $validation = $this->createMock(ValidationResultsInterface::class);
        $validation->method('isValid')->willReturn(true);
        $accountManagement = $this->createMock(AccountManagementInterface::class);
        $accountManagement->method('validate')->with($customer)->willReturn($validation);
        $helper = $this->createMock(DataObjectHelper::class);
        $helper->expects(self::once())->method('populateWithArray')->with(
            $customer,
            $expected,
            CustomerInterface::class
        );
        $create = (new ObjectManager($this))->getObject(Create::class, [
            'metadataFormFactory' => $formFactory,
            'customerMapper' => $mapper,
            'dataObjectHelper' => $helper,
            'accountManagement' => $accountManagement,
        ]);
        $create->setData('account', $account);

        self::assertSame($customer, (new ReflectionMethod(Create::class, '_validateCustomerData'))
            ->invoke($create, $customer));
    }

    /**
     * Account requests and the values to populate during validation.
     */
    public static function validationDataProvider(): array
    {
        return [
            'omitted' => [[], []],
            'explicitly cleared' => [[CustomerInterface::TAXVAT => ''], [CustomerInterface::TAXVAT => null]],
        ];
    }
}
