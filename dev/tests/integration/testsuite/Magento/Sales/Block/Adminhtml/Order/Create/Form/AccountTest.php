<?php
/**
 * Test class for \Magento\Sales\Block\Adminhtml\Order\Create\Form\Account
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Block\Adminhtml\Order\Create\Form;

use Magento\Backend\Model\Session\Quote as SessionQuote;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterfaceFactory;
use Magento\Customer\Model\Data\Option;
use Magento\Customer\Model\Metadata\Form;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Framework\View\LayoutInterface;
use Magento\Quote\Model\Quote;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class for test Account
 *
 * @magentoAppArea adminhtml
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AccountTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Account
     */
    private $accountBlock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var SessionQuote|MockObject
     */
    private $session;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        parent::setUp();
    }

    /**
     * Test for get form with existing customer
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testGetFormWithCustomer()
    {
        $customerGroup = 2;
        $quote = $this->objectManager->create(Quote::class);

        $this->session = $this->getMockBuilder(SessionQuote::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerId','getQuote'])
            ->getMock();
        $this->session->method('getQuote')
            ->willReturn($quote);
        $this->session->method('getCustomerId')
            ->willReturn(1);

        /** @var LayoutInterface $layout */
        $layout = $this->objectManager->get(LayoutInterface::class);
        $this->accountBlock = $layout->createBlock(
            Account::class,
            'address_block' . rand(),
            ['sessionQuote' => $this->session]
        );

        $fixtureCustomerId = 1;
        /** @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository */
        $customerRepository = $this->objectManager->get(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
        $customer = $customerRepository->getById($fixtureCustomerId);
        $customer->setGroupId($customerGroup);
        $customerRepository->save($customer);

        $expectedFields = ['group_id', 'email'];
        $form = $this->accountBlock->getForm();
        self::assertEquals(1, $form->getElements()->count(), "Form has invalid number of fieldsets");
        $fieldset = $form->getElements()[0];
        $content = $form->toHtml();

        self::assertEquals(count($expectedFields), $fieldset->getElements()->count());

        foreach ($fieldset->getElements() as $element) {
            self::assertTrue(
                in_array($element->getId(), $expectedFields),
                sprintf('Unexpected field "%s" in form.', $element->getId())
            );
        }

        self::assertContains(
            '<option value="'.$customerGroup.'" selected="selected">Wholesale</option>',
            $content,
            'The Customer Group specified for the chosen customer should be selected.'
        );

        self::assertContains(
            'value="'.$customer->getEmail().'"',
            $content,
            'The Customer Email specified for the chosen customer should be input '
        );
    }

    /**
     * Tests a case when user defined custom attribute has default value.
     *
     * @magentoDataFixture Magento/Store/_files/core_second_third_fixturestore.php
     * @magentoConfigFixture current_store customer/create_account/default_group 2
     * @magentoConfigFixture secondstore_store customer/create_account/default_group 3
     */
    public function testGetFormWithUserDefinedAttribute()
    {
        /** @var \Magento\Store\Model\StoreManagerInterface  $storeManager */
        $storeManager = Bootstrap::getObjectManager()->get(\Magento\Store\Model\StoreManagerInterface::class);
        $secondStore = $storeManager->getStore('secondstore');

        $quoteSession = $this->objectManager->get(SessionQuote::class);
        $quoteSession->setStoreId($secondStore->getId());

        $formFactory = $this->getFormFactoryMock();
        $this->objectManager->addSharedInstance($formFactory, FormFactory::class);

        /** @var LayoutInterface $layout */
        $layout = $this->objectManager->get(LayoutInterface::class);
        $accountBlock = $layout->createBlock(
            Account::class,
            'address_block' . rand()
        );

        $form = $accountBlock->getForm();
        $form->setUseContainer(true);
        $content = $form->toHtml();

        self::assertContains(
            '<option value="1" selected="selected">Yes</option>',
            $content,
            'Default value for user defined custom attribute should be selected.'
        );

        self::assertContains(
            '<option value="3" selected="selected">Retailer</option>',
            $content,
            'The Customer Group specified for the chosen store should be selected.'
        );
    }

    /**
     * Creates a mock for Form object.
     *
     * @return MockObject
     */
    private function getFormFactoryMock(): MockObject
    {
        /** @var AttributeMetadataInterfaceFactory $attributeMetadataFactory */
        $attributeMetadataFactory = $this->objectManager->create(AttributeMetadataInterfaceFactory::class);
        $booleanAttribute = $attributeMetadataFactory->create()
            ->setAttributeCode('boolean')
            ->setBackendType('boolean')
            ->setFrontendInput('boolean')
            ->setDefaultValue('1')
            ->setFrontendLabel('Yes/No');

        /** @var Form|MockObject $form */
        $form = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();
        $form->method('getUserAttributes')->willReturn([$booleanAttribute]);
        $form->method('getSystemAttributes')->willReturn([$this->createCustomerGroupAttribute()]);

        $formFactory = $this->getMockBuilder(FormFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $formFactory->method('create')->willReturn($form);

        return $formFactory;
    }

    /**
     * Creates a customer group attribute object.
     *
     * @return AttributeMetadataInterface
     */
    private function createCustomerGroupAttribute(): AttributeMetadataInterface
    {
        /** @var Option $option1 */
        $option1 = $this->objectManager->create(Option::class);
        $option1->setValue(2);
        $option1->setLabel('Wholesale');

        /** @var Option $option2 */
        $option2 = $this->objectManager->create(Option::class);
        $option2->setValue(3);
        $option2->setLabel('Retailer');

        /** @var AttributeMetadataInterfaceFactory $attributeMetadataFactory */
        $attributeMetadataFactory = $this->objectManager->create(AttributeMetadataInterfaceFactory::class);
        $attribute = $attributeMetadataFactory->create()
            ->setAttributeCode('group_id')
            ->setBackendType('static')
            ->setFrontendInput('select')
            ->setOptions([$option1, $option2])
            ->setIsRequired(true);

        return $attribute;
    }
}
