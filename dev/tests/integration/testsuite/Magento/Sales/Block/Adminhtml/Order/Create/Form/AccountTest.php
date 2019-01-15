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
use Magento\Customer\Api\Data\AttributeMetadataInterfaceFactory;
use Magento\Customer\Model\Metadata\Form;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Framework\View\LayoutInterface;
use Magento\Quote\Model\Quote;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppArea adminhtml
 */
class AccountTest extends \PHPUnit\Framework\TestCase
{
    /** @var Account */
    private $accountBlock;

    /**
     * @var Bootstrap
     */
    private $objectManager;

    /**
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $quote = $this->objectManager->create(Quote::class)->load(1);
        $sessionQuoteMock = $this->getMockBuilder(
            SessionQuote::class
        )->disableOriginalConstructor()->setMethods(
            ['getCustomerId', 'getStore', 'getStoreId', 'getQuote']
        )->getMock();
        $sessionQuoteMock->expects($this->any())->method('getCustomerId')->will($this->returnValue(1));
        $sessionQuoteMock->expects($this->any())->method('getQuote')->will($this->returnValue($quote));
        /** @var LayoutInterface $layout */
        $layout = $this->objectManager->get(LayoutInterface::class);
        $this->accountBlock = $layout->createBlock(
            Account::class,
            'address_block' . rand(),
            ['sessionQuote' => $sessionQuoteMock]
        );
        parent::setUp();
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testGetForm()
    {
        $expectedFields = ['group_id', 'email'];
        $form = $this->accountBlock->getForm();
        $this->assertEquals(1, $form->getElements()->count(), "Form has invalid number of fieldsets");
        $fieldset = $form->getElements()[0];

        $this->assertEquals(count($expectedFields), $fieldset->getElements()->count());

        foreach ($fieldset->getElements() as $element) {
            $this->assertTrue(
                in_array($element->getId(), $expectedFields),
                sprintf('Unexpected field "%s" in form.', $element->getId())
            );
        }
    }

    /**
     * Tests a case when user defined custom attribute has default value.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testGetFormWithUserDefinedAttribute()
    {
        $formFactory = $this->getFormFactoryMock();
        $this->objectManager->addSharedInstance($formFactory, FormFactory::class);

        /** @var LayoutInterface $layout */
        $layout = $this->objectManager->get(LayoutInterface::class);
        $accountBlock = $layout->createBlock(Account::class, 'address_block' . rand());

        $form = $accountBlock->getForm();
        $form->setUseContainer(true);

        $this->assertContains(
            '<option value="1" selected="selected">Yes</option>',
            $form->toHtml(),
            'Default value for user defined custom attribute should be selected'
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getFormFactoryMock(): \PHPUnit_Framework_MockObject_MockObject
    {
        /** @var AttributeMetadataInterfaceFactory $attributeMetadataFactory */
        $attributeMetadataFactory = $this->objectManager->create(AttributeMetadataInterfaceFactory::class);
        $booleanAttribute = $attributeMetadataFactory->create()
            ->setAttributeCode('boolean')
            ->setBackendType('boolean')
            ->setFrontendInput('boolean')
            ->setDefaultValue('1')
            ->setFrontendLabel('Yes/No');

        $form = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();
        $form->method('getUserAttributes')->willReturn([$booleanAttribute]);
        $form->method('getSystemAttributes')->willReturn([]);

        $formFactory = $this->getMockBuilder(FormFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $formFactory->method('create')->willReturn($form);

        return $formFactory;
    }
}
