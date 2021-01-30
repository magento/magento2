<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Controller\Adminhtml\Cart\Product\Composite\Cart;

use Magento\Backend\Model\Session;
use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Model\Product\Option as ProductOption;
use Magento\Catalog\Model\Product\Option\Type\File\ValidatorInfo;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Tests for update quote item in customer shopping cart.
 *
 * @magentoAppArea adminhtml
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateTest extends AbstractBackendController
{
    /** @var CollectionFactory */
    private $quoteItemCollectionFactory;

    /** @var Session */
    private $session;

    /** @var SerializerInterface */
    private $json;

    /** @var CartRepositoryInterface */
    private $quoteRepository;

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var int */
    private $baseWebsiteId;

    /** @inheritdoc */
    public function setUp(): void
    {
        parent::setUp();
        $this->quoteItemCollectionFactory = $this->_objectManager->get(CollectionFactory::class);
        $this->session = $this->_objectManager->get(Session::class);
        $this->json = $this->_objectManager->get(SerializerInterface::class);
        $this->quoteRepository = $this->_objectManager->get(CartRepositoryInterface::class);
        $this->customerRepository = $this->_objectManager->get(CustomerRepositoryInterface::class);
        $this->baseWebsiteId = (int)$this->_objectManager->get(StoreManagerInterface::class)
            ->getWebsite('base')
            ->getId();
    }

    /**
     * @return void
     */
    public function testUpdateNoCustomerId(): void
    {
        $expectedUpdateResult = [
            'error' => true,
            'message' => (string)__("The customer ID isn't defined."),
            'js_var_name' => null,
        ];
        $this->dispatchCompositeCartUpdate();
        /** @var DataObject $updateResult */
        $updateResult = $this->session->getCompositeProductResult();
        $this->assertEquals($expectedUpdateResult, $updateResult->getData());
        $this->assertRedirect($this->stringContains('catalog/product/showUpdateResult'));
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @return void
     */
    public function testUpdateNoQuoteId(): void
    {
        $expectedUpdateResult = [
            'error' => true,
            'message' => (string)__('The quote items are incorrect. Verify the quote items and try again.'),
            'js_var_name' => 'iFrameResponse',
        ];
        $this->dispatchCompositeCartUpdate([
            'customer_id' => 1,
            'website_id' => $this->baseWebsiteId,
            'as_js_varname' => 'iFrameResponse',
        ]);
        /** @var DataObject $updateResult */
        $updateResult = $this->session->getCompositeProductResult();
        $this->assertEquals($expectedUpdateResult, $updateResult->getData());
        $this->assertRedirect($this->stringContains('catalog/product/showUpdateResult'));
    }

    /**
     * @dataProvider updateWithQuoteProvider
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/quote.php
     * @param bool $hasQuoteItem
     * @param array $expectedUpdateResult
     * @return void
     */
    public function testUpdateWithQuote(bool $hasQuoteItem, array $expectedUpdateResult): void
    {
        $itemsCollection = $this->quoteItemCollectionFactory->create();
        $itemId = $itemsCollection->getFirstItem()->getId();
        $this->assertNotEmpty($itemId);
        if (!$hasQuoteItem) {
            $itemId++;
        }
        $this->dispatchCompositeCartUpdate(
            [
                'customer_id' => 1,
                'website_id' => $this->baseWebsiteId,
            ],
            [
                'id' => $itemId,
                'as_js_varname' => 'iFrameResponse',
                'qty' => 20,
            ]
        );
        /** @var DataObject $updateResult */
        $updateResult = $this->session->getCompositeProductResult();
        $this->assertEquals($expectedUpdateResult, $updateResult->getData());
        $this->assertRedirect($this->stringContains('catalog/product/showUpdateResult'));
    }

    /**
     * Create update with quote provider
     *
     * @return array
     */
    public function updateWithQuoteProvider(): array
    {
        return [
            'with_quote_item_id' => [
                'has_quote_item' => true,
                'expected_update_result' => [
                    'ok' => true,
                    'js_var_name' => 'iFrameResponse',
                ],
            ],
            'without_quote_item_id' => [
                'has_quote_item' => false,
                'expected_update_result' => [
                    'error' => true,
                    'message' => (string)__('The quote items are incorrect. Verify the quote items and try again.'),
                    'js_var_name' => 'iFrameResponse',
                ],
            ],
        ];
    }

    /**
     * @magentoDataFixture Magento/Checkout/_files/customer_quote_with_items_simple_product_options.php
     * @return void
     */
    public function testUpdateSimpleProductOption(): void
    {
        $customer = $this->customerRepository->get('customer_uk_address@test.com');
        /** @var Quote $quote */
        $quote = $this->quoteRepository->getForCustomer($customer->getId());
        /** @var QuoteItem $quoteItem */
        $quoteItem = $quote->getItemsCollection()->getFirstItem();
        $this->assertNotEmpty($quoteItem->getId());
        $expectedData = $this->prepareExpectedData($quoteItem);
        $expectedUpdateResult = [
            'ok' => true,
            'js_var_name' => 'iFrameResponse',
        ];
        $expectedParams = [
            'id' => $quoteItem->getId(),
            'as_js_varname' => 'iFrameResponse',
            'options' => $expectedData['options'],
            'qty' => 5,
        ];
        $this->dispatchCompositeCartUpdate(
            [
                'customer_id' => $customer->getId(),
                'website_id' => $customer->getWebsiteId(),
            ],
            $expectedParams
        );
        /** @var DataObject $updateResult */
        $updateResult = $this->session->getCompositeProductResult();
        $this->assertEquals($expectedUpdateResult, $updateResult->getData());

        $quoteItem = $this->getQuoteItemBySku($quote, $expectedData['sku']);
        $this->assertNotNull($quoteItem, 'Missing expected shopping cart item after update.');
        $this->assertQuoteItemOptions($quoteItem, $expectedParams);
        $this->assertRedirect($this->stringContains('catalog/product/showUpdateResult'));
    }

    /**
     * Prepare quote item options and sku for update.
     *
     * @param QuoteItem $quoteItem
     * @return array
     */
    private function prepareExpectedData(QuoteItem $quoteItem): array
    {
        $buyRequest = $this->json->unserialize($quoteItem->getOptionByCode('info_buyRequest')->getValue());
        $productOptions = $quoteItem->getProduct()->getOptions();
        $options = [];
        $sku = $quoteItem->getSku();
        /** @var ProductOption $productOption */
        foreach ($productOptions as $productOption) {
            $itemOptionValue = $buyRequest['options'][$productOption->getId()];
            switch ($productOption->getType()) {
                case ProductCustomOptionInterface::OPTION_TYPE_RADIO:
                    $productValues = $productOption->getValues();
                    $currentRadioSku = $productValues[$itemOptionValue]->getSku();
                    unset($productValues[$itemOptionValue]);
                    $value = (string)key($productValues);
                    $newRadioSku = $productValues[$value]->getSku();
                    $sku = str_replace($currentRadioSku, $newRadioSku, $sku);
                    break;
                case ProductCustomOptionInterface::OPTION_TYPE_DATE:
                    $value = ['year' => 2019, 'month' => 8, 'day' => 9, 'hour' => 13, 'minute' => 35];
                    break;
                case ProductCustomOptionInterface::OPTION_TYPE_FILE:
                    $itemOptionValue['title'] = 'testcart.jpg';
                    $value = $itemOptionValue;
                    $validatorInfoMock = $this->prepareValidatorInfoMock();
                    $this->_objectManager->addSharedInstance($validatorInfoMock, ValidatorInfo::class);
                    break;
                case ProductCustomOptionInterface::OPTION_TYPE_AREA:
                    $value = 'testcart';
                    break;
                default:
                    $value = $itemOptionValue;
                    break;
            }
            $options[$productOption->getId()] = $value;
        }

        return [
            'options' => $options,
            'sku' => $sku,
        ];
    }

    /**
     * Prepare mock for updating file type options.
     *
     * @return MockObject
     */
    private function prepareValidatorInfoMock(): MockObject
    {
        $validatorInfoMock = $this->createMock(ValidatorInfo::class);
        $validatorInfoMock->method('setUseQuotePath')->willReturnSelf();
        $validatorInfoMock->expects($this->any())
            ->method('validate')
            ->willReturn(true);

        return $validatorInfoMock;
    }

    /**
     * Get quote item by sku.
     *
     * @param Quote $quote
     * @param string $sku
     * @return QuoteItem|null
     */
    private function getQuoteItemBySku(Quote $quote, string $sku): ?QuoteItem
    {
        $itemsCollection = $quote->getItemsCollection(false);
        $itemsCollection->addFieldToFilter('sku', $sku);
        /** @var QuoteItem $quoteItem */
        $quoteItem = $itemsCollection->getFirstItem();

        return empty($quoteItem->getId()) ? null : $quoteItem;
    }

    /**
     * Verify that the quote item options are saved successfully.
     *
     * @param QuoteItem $quoteItem
     * @param array $expectedParams
     * @return void
     */
    private function assertQuoteItemOptions(QuoteItem $quoteItem, array $expectedParams): void
    {
        $buyRequest = $this->json->unserialize($quoteItem->getOptionByCode('info_buyRequest')->getValue());
        foreach ($expectedParams as $key => $value) {
            if ($key == 'options') {
                foreach ($value as $optionId => $optionValue) {
                    $buyRequestValue = is_array($optionValue)
                        ? array_intersect_assoc($optionValue, $buyRequest[$key][$optionId])
                        : $buyRequest[$key][$optionId];
                    $this->assertEquals($optionValue, $buyRequestValue);
                }
            } else {
                $this->assertEquals($value, $buyRequest[$key]);
            }
        }
    }

    /**
     * Dispatch update quote item in customer shopping cart
     * using backend/customer/cart_product_composite_cart/update action.
     *
     * @param array $params
     * @param array $postValue
     * @return void
     */
    private function dispatchCompositeCartUpdate(array $params = [], array $postValue = []): void
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setParams($params);
        $this->getRequest()->setPostValue($postValue);
        $this->dispatch('backend/customer/cart_product_composite_cart/update');
    }
}
