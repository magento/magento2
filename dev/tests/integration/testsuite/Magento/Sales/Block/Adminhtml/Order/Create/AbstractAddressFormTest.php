<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Block\Adminhtml\Order\Create;

use Magento\Config\Model\Config\Backend\Admin\Custom;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Model\Address\AddressModelInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Class consist of basic logic to check address form
 */
abstract class AbstractAddressFormTest extends TestCase
{
    /** @var LayoutInterface */
    protected $layout;

    /** @var CustomerRegistry */
    protected $customerRegistry;

    /** @var CartRepositoryInterface */
    private $quoteRepository;

    /** @var BlockInterface */
    private $form;

    /** @var ScopeConfigInterface */
    private $config;

    /** @var array */
    private $formAttributes;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $objectManager = Bootstrap::getObjectManager();
        $this->layout = $objectManager->get(LayoutInterface::class);
        $this->form = $this->getFormBlock();
        $this->customerRegistry = $objectManager->get(CustomerRegistry::class);
        $this->quoteRepository = $objectManager->get(CartRepositoryInterface::class);
        $this->config = $objectManager->get(ScopeConfigInterface::class);
        $this->formAttributes = array_keys($objectManager->get(FormFactory::class)
            ->create('customer_address', 'adminhtml_customer_address')->getAttributes());
    }

    /**
     * Check that all form values are filled according to address attributes values
     *
     * @param int $customerId
     * @return void
     */
    protected function checkFormValuesExist(int $customerId): void
    {
        $address = $this->getAddress($customerId);
        $form = $this->prepareForm($customerId);
        foreach ($this->formAttributes as $attribute) {
            $this->assertEquals($address->getData($attribute), $form->getElement($attribute)->getValue());
        }
    }

    /**
     * Check that form values is empty
     *
     * @param int $customerId
     * @return void
     */
    protected function checkFormValuesAreEmpty(int $customerId): void
    {
        $defaultCountryCode = $this->config->getValue(Custom::XML_PATH_GENERAL_COUNTRY_DEFAULT);
        $form = $this->prepareForm($customerId);
        foreach ($this->formAttributes as $attribute) {
            if ($attribute === AddressInterface::COUNTRY_ID) {
                $this->assertEquals($defaultCountryCode, $form->getElement($attribute)->getValue());
                continue;
            }
            $this->assertNull($form->getElement($attribute)->getValue());
        }
    }

    /**
     * Prepare form
     *
     * @param int $customerId
     * @return Form
     */
    private function prepareForm(int $customerId): Form
    {
        $quote = $this->quoteRepository->getForCustomer($customerId);
        $this->form->getCreateOrderModel()->setQuote($quote);

        return $this->form->getForm();
    }

    /**
     * Get form block
     *
     * @return BlockInterface
     */
    abstract protected function getFormBlock(): BlockInterface;

    /**
     * Get appropriate customer address
     *
     * @param int $customerId
     * @return AddressModelInterface
     */
    abstract protected function getAddress(int $customerId): AddressModelInterface;
}
