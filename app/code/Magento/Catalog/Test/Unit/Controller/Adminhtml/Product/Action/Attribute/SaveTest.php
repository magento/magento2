<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product\Action\Attribute;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Save;
use Magento\Catalog\Helper\Product\Edit\Action\Attribute as AttributeHelper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Bulk\BulkManagementInterface;
use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory;
use Magento\Framework\DataObject\IdentityGeneratorInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Catalog\Model\Product\Filter\DateTime as DateTimeFilter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Eav\Model\Entity\Attribute\Exception as EavAttributeException;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Save
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends TestCase
{
    use MockCreationTrait;

    /** @var Context */
    private $context;

    /** @var AttributeHelper */
    private $attributeHelper;

    /** @var BulkManagementInterface */
    private $bulkManagement;

    /** @var OperationInterfaceFactory */
    private $operationFactory;

    /** @var IdentityGeneratorInterface */
    private $identityService;

    /** @var SerializerInterface */
    private $serializer;

    /** @var UserContextInterface */
    private $userContext;

    /** @var TimezoneInterface */
    private $timezone;

    /** @var EavConfig */
    private $eavConfig;

    /** @var ProductFactory */
    private $productFactory;

    /** @var DateTimeFilter */
    private $dateTimeFilter;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $request = $this->createMock(RequestInterface::class);
        $request->method('getParam')->willReturnMap([
            ['attributes', [], []],
            ['remove_website_ids', [], []],
            ['add_website_ids', [], []],
        ]);
        $messageManager = $this->createMock(ManagerInterface::class);
        $redirect = $this->createMock(Redirect::class);
        $redirect->method('setPath')->willReturnSelf();
        $resultRedirectFactory = $this->createMock(RedirectFactory::class);
        $resultRedirectFactory->method('create')->willReturn($redirect);
        $objectManager = $this->createObjectManagerForValidateProducts(true);

        $this->context = $this->createPartialMock(
            Context::class,
            ['getRequest', 'getMessageManager', 'getResultRedirectFactory', 'getObjectManager']
        );
        $this->context->method('getRequest')->willReturn($request);
        $this->context->method('getMessageManager')->willReturn($messageManager);
        $this->context->method('getResultRedirectFactory')->willReturn($resultRedirectFactory);
        $this->context->method('getObjectManager')->willReturn($objectManager);

        $this->attributeHelper = $this->createMock(AttributeHelper::class);
        $this->attributeHelper->method('getProductIds')->willReturn([1]);
        $this->attributeHelper->method('getSelectedStoreId')->willReturn(1);
        $this->attributeHelper->method('getStoreWebsiteId')->willReturn(1);

        $this->bulkManagement = $this->createMock(BulkManagementInterface::class);
        $this->operationFactory = $this->createMock(OperationInterfaceFactory::class);
        $this->identityService = $this->createMock(IdentityGeneratorInterface::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->userContext = $this->createMock(UserContextInterface::class);
        $this->timezone = $this->createMock(TimezoneInterface::class);
        $this->eavConfig = $this->createMock(EavConfig::class);
        $this->productFactory = $this->createMock(ProductFactory::class);
        $this->dateTimeFilter = $this->createMock(DateTimeFilter::class);
    }

    /**
     * Create ObjectManager mock for parent _validateProducts (create Product and isProductsHasSku).
     *
     * @param bool $isProductsHasSku Value returned by Product::isProductsHasSku()
     * @return ObjectManagerInterface
     */
    private function createObjectManagerForValidateProducts(bool $isProductsHasSku = true): ObjectManagerInterface
    {
        $product = $this->createMock(Product::class);
        $product->method('isProductsHasSku')->willReturn($isProductsHasSku);
        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $objectManager->method('create')->with(Product::class)->willReturn($product);
        return $objectManager;
    }

    /**
     * Build real Save controller with optional overrides (dependencies from setUp by default).
     *
     * @param array<string, object> $overrides Map of dependency name to mock (context, attributeHelper, ...)
     * @return Save
     */
    private function buildController(array $overrides = []): Save
    {
        return new Save(
            $overrides['context'] ?? $this->context,
            $overrides['attributeHelper'] ?? $this->attributeHelper,
            $overrides['bulkManagement'] ?? $this->bulkManagement,
            $overrides['operationFactory'] ?? $this->operationFactory,
            $overrides['identityService'] ?? $this->identityService,
            $overrides['serializer'] ?? $this->serializer,
            $overrides['userContext'] ?? $this->userContext,
            100,
            $overrides['timezone'] ?? $this->timezone,
            $overrides['eavConfig'] ?? $this->eavConfig,
            $overrides['productFactory'] ?? $this->productFactory,
            $overrides['dateTimeFilter'] ?? $this->dateTimeFilter
        );
    }

    /**
     * Create dependency mocks for execute() success path (request, context, services, product, date filter).
     *
     * @param array $attributesData Request 'attributes' param value
     * @param array $productIds Product IDs for attribute helper
     * @param int $storeId Store ID
     * @param callable|null $getPost Optional getPost callback for request (e.g. multiselect toggle)
     * @param string|null $specialToDate Value for product->getSpecialToDate()
     * @return array<string, mixed> Map of dependency name to mock (request, context, redirect, etc.)
     */
    private function createExecuteSuccessDependencyMocks(
        array $attributesData,
        array $productIds = [1],
        int $storeId = 1,
        ?callable $getPost = null,
        ?string $specialToDate = null
    ): array {
        $request = $getPost
            ? $this->createPartialMock(HttpRequest::class, ['getParam', 'getPost'])
            : $this->createMock(RequestInterface::class);
        $request->expects($this->atLeastOnce())->method('getParam')->willReturnMap([
            ['attributes', [], $attributesData],
            ['remove_website_ids', [], []],
            ['add_website_ids', [], []],
        ]);
        if ($getPost !== null && $request instanceof HttpRequest) {
            $request->expects($this->atLeastOnce())->method('getPost')->willReturnCallback($getPost);
        }
        $messageManager = $this->createMock(ManagerInterface::class);
        $messageManager->expects($this->once())->method('addSuccessMessage')->with($this->anything());
        $messageManager->expects($this->never())->method('addErrorMessage');
        $redirect = $this->createMock(Redirect::class);
        $redirect->expects($this->atLeastOnce())->method('setPath')->willReturnSelf();
        $resultRedirectFactory = $this->createMock(RedirectFactory::class);
        $resultRedirectFactory->expects($this->once())->method('create')->willReturn($redirect);
        $objectManager = $this->createObjectManagerForValidateProducts(true);
        $context = $this->createPartialMock(
            Context::class,
            ['getRequest', 'getMessageManager', 'getResultRedirectFactory', 'getObjectManager']
        );
        $context->expects($this->atLeastOnce())->method('getRequest')->willReturn($request);
        $context->expects($this->atLeastOnce())->method('getMessageManager')->willReturn($messageManager);
        $context->expects($this->atLeastOnce())->method('getResultRedirectFactory')->willReturn($resultRedirectFactory);
        $context->expects($this->atLeastOnce())->method('getObjectManager')->willReturn($objectManager);
        $attributeHelper = $this->createMock(AttributeHelper::class);
        $attributeHelper->expects($this->atLeastOnce())->method('getSelectedStoreId')->willReturn($storeId);
        $attributeHelper->expects($this->atLeastOnce())->method('getStoreWebsiteId')->with($storeId)->willReturn(1);
        $attributeHelper->expects($this->atLeastOnce())->method('getProductIds')->willReturn($productIds);
        $attributeHelper->expects($this->atLeastOnce())->method('setProductIds')->with([]);
        $bulkManagement = $this->createMock(BulkManagementInterface::class);
        $bulkManagement->expects($this->once())->method('scheduleBulk')->willReturn(true);
        $operation = $this->createMock(OperationInterface::class);
        $operationFactory = $this->createMock(OperationInterfaceFactory::class);
        $operationFactory->expects($this->atLeastOnce())->method('create')->willReturn($operation);
        $identityService = $this->createMock(IdentityGeneratorInterface::class);
        $identityService->expects($this->atLeastOnce())->method('generateId')->willReturn('bulk-uuid');
        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->atLeastOnce())->method('serialize')->willReturn('serialized');
        $userContext = $this->createMock(UserContextInterface::class);
        $userContext->expects($this->atLeastOnce())->method('getUserId')->willReturn(1);
        $timezone = $this->createMock(TimezoneInterface::class);
        $dateTimeFilter = $this->createMock(DateTimeFilter::class);
        $dateTimeFilter->expects($this->any())->method('filter')->willReturnCallback(function ($v) {
            return $v ? $v . ' 00:00:00' : null;
        });
        $product = $this->createPartialMock(Product::class, ['setData', 'getSpecialToDate']);
        $product->expects($this->any())->method('setData')->with($this->anything());
        $product->expects($this->any())->method('getSpecialToDate')->willReturn($specialToDate);
        $productFactory = $this->createMock(ProductFactory::class);
        $productFactory->expects($this->atLeastOnce())->method('create')->willReturn($product);
        return [
            'request' => $request,
            'messageManager' => $messageManager,
            'redirect' => $redirect,
            'context' => $context,
            'attributeHelper' => $attributeHelper,
            'bulkManagement' => $bulkManagement,
            'operationFactory' => $operationFactory,
            'identityService' => $identityService,
            'serializer' => $serializer,
            'userContext' => $userContext,
            'timezone' => $timezone,
            'dateTimeFilter' => $dateTimeFilter,
            'product' => $product,
            'productFactory' => $productFactory,
        ];
    }

    /**
     * Create identity, serializer, userContext, timezone, dateTimeFilter, product and productFactory
     * mocks for execute tests.
     *
     * @param string|null $specialToDate Value for product->getSpecialToDate()
     * @return array<string, mixed>
     */
    private function createBulkOperationAndProductMocks(?string $specialToDate = '2025-09-10 00:00:00'): array
    {
        $identityService = $this->createMock(IdentityGeneratorInterface::class);
        $identityService->expects($this->atLeastOnce())->method('generateId')->willReturn('bulk-uuid');
        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->atLeastOnce())->method('serialize')->willReturn('serialized');
        $userContext = $this->createMock(UserContextInterface::class);
        $userContext->expects($this->atLeastOnce())->method('getUserId')->willReturn(1);
        $timezone = $this->createMock(TimezoneInterface::class);
        $dateTimeFilter = $this->createMock(DateTimeFilter::class);
        $dateTimeFilter->expects($this->atLeastOnce())->method('filter')->willReturnCallback(function ($v) {
            return $v ? $v . ' 00:00:00' : null;
        });
        $product = $this->createPartialMock(Product::class, ['setData', 'getSpecialToDate']);
        $product->expects($this->atLeastOnce())->method('setData')->with($this->anything());
        $product->expects($this->atLeastOnce())->method('getSpecialToDate')->willReturn($specialToDate);
        $productFactory = $this->createMock(ProductFactory::class);
        $productFactory->expects($this->atLeastOnce())->method('create')->willReturn($product);
        return [
            'identityService' => $identityService,
            'serializer' => $serializer,
            'userContext' => $userContext,
            'timezone' => $timezone,
            'dateTimeFilter' => $dateTimeFilter,
            'product' => $product,
            'productFactory' => $productFactory,
        ];
    }

    /**
     * Create request, messageManager, redirect and context mocks for execute tests.
     *
     * @param array $getParamMap Return map for request->getParam (e.g. attributes, remove_website_ids, add_website_ids)
     * @param bool $expectSuccessMessage Whether messageManager should expect addSuccessMessage once
     * @return array<string, mixed> Keys: request, messageManager, redirect, context
     */
    private function createRequestContextAndRedirect(array $getParamMap, bool $expectSuccessMessage = true): array
    {
        $request = $this->createMock(RequestInterface::class);
        $request->expects($this->atLeastOnce())->method('getParam')->willReturnMap($getParamMap);
        $messageManager = $this->createMock(ManagerInterface::class);
        if ($expectSuccessMessage) {
            $messageManager->expects($this->once())->method('addSuccessMessage')->with($this->anything());
        }
        $redirect = $this->createMock(Redirect::class);
        $redirect->expects($this->atLeastOnce())->method('setPath')->willReturnSelf();
        $resultRedirectFactory = $this->createMock(RedirectFactory::class);
        $resultRedirectFactory->expects($this->once())->method('create')->willReturn($redirect);
        $objectManager = $this->createObjectManagerForValidateProducts(true);
        $context = $this->createPartialMock(
            Context::class,
            ['getRequest', 'getMessageManager', 'getResultRedirectFactory', 'getObjectManager']
        );
        $context->expects($this->atLeastOnce())->method('getRequest')->willReturn($request);
        $context->expects($this->atLeastOnce())->method('getMessageManager')->willReturn($messageManager);
        $context->expects($this->atLeastOnce())->method('getResultRedirectFactory')->willReturn($resultRedirectFactory);
        $context->expects($this->atLeastOnce())->method('getObjectManager')->willReturn($objectManager);
        return [
            'request' => $request,
            'messageManager' => $messageManager,
            'redirect' => $redirect,
            'context' => $context,
        ];
    }

    /**
     * Execute flow: invalid special price dates (From > To) must show error and must not call publish.
     *
     * @covers \Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Save::execute
     * @return void
     */
    public function testExecuteShowsErrorAndDoesNotPublishWhenSpecialPriceFromDateAfterToDate(): void
    {
        $storeId = 1;
        $productIds = [1, 2];
        $attributesData = [
            'special_from_date' => '2025-09-10',
            'special_to_date' => '2025-09-01',
        ];

        $request = $this->createMock(RequestInterface::class);
        $request->expects($this->atLeastOnce())->method('getParam')->willReturnMap([
            ['attributes', [], $attributesData],
            ['remove_website_ids', [], []],
            ['add_website_ids', [], []],
        ]);

        $messageManager = $this->createMock(ManagerInterface::class);
        $messageManager->expects($this->once())
            ->method('addErrorMessage')
            ->with('Make sure the To Date is later than or the same as the From Date.');

        $redirect = $this->createMock(Redirect::class);
        $redirect->expects($this->once())->method('setPath')->with('catalog/product/', ['store' => $storeId])
            ->willReturnSelf();
        $resultRedirectFactory = $this->createMock(RedirectFactory::class);
        $resultRedirectFactory->expects($this->once())->method('create')->willReturn($redirect);

        $objectManager = $this->createObjectManagerForValidateProducts(true);
        $context = $this->createPartialMock(
            Context::class,
            ['getRequest', 'getMessageManager', 'getResultRedirectFactory', 'getObjectManager']
        );
        $context->expects($this->atLeastOnce())->method('getRequest')->willReturn($request);
        $context->expects($this->atLeastOnce())->method('getMessageManager')->willReturn($messageManager);
        $context->expects($this->atLeastOnce())->method('getResultRedirectFactory')->willReturn($resultRedirectFactory);
        $context->expects($this->atLeastOnce())->method('getObjectManager')->willReturn($objectManager);

        $attributeHelper = $this->createMock(AttributeHelper::class);
        $attributeHelper->expects($this->atLeastOnce())->method('getSelectedStoreId')->willReturn($storeId);
        $attributeHelper->expects($this->atLeastOnce())->method('getStoreWebsiteId')->with($storeId)->willReturn(1);
        $attributeHelper->expects($this->atLeastOnce())->method('getProductIds')->willReturn($productIds);

        $bulkManagement = $this->createMock(BulkManagementInterface::class);
        $bulkManagement->expects($this->never())->method('scheduleBulk');

        $operationFactory = $this->createMock(OperationInterfaceFactory::class);
        $identityService = $this->createMock(IdentityGeneratorInterface::class);
        $serializer = $this->createMock(SerializerInterface::class);
        $userContext = $this->createMock(UserContextInterface::class);
        $timezone = $this->createMock(TimezoneInterface::class);

        $dateTimeFilter = $this->createMock(DateTimeFilter::class);
        $dateTimeFilter->expects($this->atLeastOnce())->method('filter')->willReturnCallback(function ($value) {
            return $value ? $value . ' 00:00:00' : null;
        });

        $product = $this->createPartialMock(Product::class, ['setData', 'getSpecialToDate']);
        $product->expects($this->atLeastOnce())->method('setData')->with($this->anything());
        $product->expects($this->atLeastOnce())->method('getSpecialToDate')->willReturn('2025-09-01 00:00:00');
        $productFactory = $this->createMock(ProductFactory::class);
        $productFactory->expects($this->atLeastOnce())->method('create')->willReturn($product);

        $fromAttrBackend = $this->createBackendMock(true);
        $toAttrBackend = $this->createBackendMock(false);
        $fromAttribute = $this->createDatetimeAttributeMockForExecute($fromAttrBackend);
        $toAttribute = $this->createDatetimeAttributeMockForExecute($toAttrBackend);

        $eavConfig = $this->createMock(EavConfig::class);
        $eavConfig->expects($this->atLeastOnce())->method('getAttribute')->willReturnCallback(
            function ($entity, $code) use ($fromAttribute, $toAttribute) {
                unset($entity);
                return $code === 'special_from_date' ? $fromAttribute : $toAttribute;
            }
        );

        $controller = $this->buildController([
            'context' => $context,
            'attributeHelper' => $attributeHelper,
            'bulkManagement' => $bulkManagement,
            'operationFactory' => $operationFactory,
            'identityService' => $identityService,
            'serializer' => $serializer,
            'userContext' => $userContext,
            'timezone' => $timezone,
            'eavConfig' => $eavConfig,
            'productFactory' => $productFactory,
            'dateTimeFilter' => $dateTimeFilter,
        ]);

        $result = $controller->execute();

        $this->assertSame($redirect, $result);
    }

    /**
     * Execute success path: valid attributes trigger publish and makeOperation (100% coverage).
     *
     * @covers \Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Save::execute
     * @return void
     */
    public function testExecutePublishesAndShowsSuccessWhenAttributesValid(): void
    {
        $attributesData = [
            'special_from_date' => '2025-09-01',
            'special_to_date' => '2025-09-10',
        ];
        $deps = $this->createExecuteSuccessDependencyMocks(
            $attributesData,
            [1, 2],
            1,
            null,
            '2025-09-10 00:00:00'
        );
        $okBackend = $this->createBackendMock(false);
        $fromAttribute = $this->createDatetimeAttributeMockForExecute($okBackend);
        $toAttribute = $this->createDatetimeAttributeMockForExecute($okBackend);
        $eavConfig = $this->createMock(EavConfig::class);
        $eavConfig->expects($this->atLeastOnce())->method('getAttribute')->willReturnCallback(
            function ($entity, $code) use ($fromAttribute, $toAttribute) {
                unset($entity);
                return $code === 'special_from_date' ? $fromAttribute : $toAttribute;
            }
        );
        $controller = $this->buildController(array_merge($deps, ['eavConfig' => $eavConfig]));
        $result = $controller->execute();
        $this->assertSame($deps['redirect'], $result);
    }

    /**
     * Execute early return when _validateProducts fails (no publish).
     *
     * @covers \Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Save::execute
     * @return void
     */
    public function testExecuteEarlyReturnWhenValidateProductsFails(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $messageManager = $this->createMock(ManagerInterface::class);
        $messageManager->expects($this->once())->method('addErrorMessage')->with($this->anything());

        $redirect = $this->createMock(Redirect::class);
        $redirect->expects($this->once())->method('setPath')->with('catalog/product/', ['_current' => true])
            ->willReturnSelf();
        $resultRedirectFactory = $this->createMock(RedirectFactory::class);
        $resultRedirectFactory->expects($this->once())->method('create')->willReturn($redirect);

        $objectManager = $this->createObjectManagerForValidateProducts(false);
        $context = $this->createPartialMock(
            Context::class,
            ['getRequest', 'getMessageManager', 'getResultRedirectFactory', 'getObjectManager']
        );
        $context->expects($this->atLeastOnce())->method('getRequest')->willReturn($request);
        $context->expects($this->atLeastOnce())->method('getMessageManager')->willReturn($messageManager);
        $context->expects($this->atLeastOnce())->method('getResultRedirectFactory')->willReturn($resultRedirectFactory);
        $context->expects($this->atLeastOnce())->method('getObjectManager')->willReturn($objectManager);

        $attributeHelper = $this->createMock(AttributeHelper::class);
        $attributeHelper->expects($this->atLeastOnce())->method('getProductIds')->willReturn([1, 2]);
        $bulkManagement = $this->createMock(BulkManagementInterface::class);
        $bulkManagement->expects($this->never())->method('scheduleBulk');

        $controller = $this->buildController([
            'context' => $context,
            'attributeHelper' => $attributeHelper,
            'bulkManagement' => $bulkManagement,
        ]);

        $result = $controller->execute();

        $this->assertSame($redirect, $result);
    }

    /**
     * Execute shows exception message when generic Exception is thrown (not LocalizedException).
     *
     * @covers \Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Save::execute
     * @return void
     */
    public function testExecuteAddsExceptionMessageOnGenericException(): void
    {
        $storeId = 1;
        $attributesData = ['special_from_date' => '2025-09-01', 'special_to_date' => '2025-09-10'];

        $request = $this->createMock(RequestInterface::class);
        $request->expects($this->atLeastOnce())->method('getParam')->willReturnMap([
            ['attributes', [], $attributesData],
            ['remove_website_ids', [], []],
            ['add_website_ids', [], []],
        ]);

        $messageManager = $this->createMock(ManagerInterface::class);
        $messageManager->expects($this->once())->method('addExceptionMessage')->with(
            $this->isInstanceOf(\Exception::class),
            $this->anything()
        );

        $redirect = $this->createMock(Redirect::class);
        $redirect->expects($this->atLeastOnce())->method('setPath')->willReturnSelf();
        $resultRedirectFactory = $this->createMock(RedirectFactory::class);
        $resultRedirectFactory->expects($this->once())->method('create')->willReturn($redirect);

        $objectManager = $this->createObjectManagerForValidateProducts(true);
        $context = $this->createPartialMock(
            Context::class,
            ['getRequest', 'getMessageManager', 'getResultRedirectFactory', 'getObjectManager']
        );
        $context->expects($this->atLeastOnce())->method('getRequest')->willReturn($request);
        $context->expects($this->atLeastOnce())->method('getMessageManager')->willReturn($messageManager);
        $context->expects($this->atLeastOnce())->method('getResultRedirectFactory')->willReturn($resultRedirectFactory);
        $context->expects($this->atLeastOnce())->method('getObjectManager')->willReturn($objectManager);

        $attributeHelper = $this->createMock(AttributeHelper::class);
        $attributeHelper->expects($this->atLeastOnce())->method('getSelectedStoreId')->willReturn($storeId);
        $attributeHelper->expects($this->atLeastOnce())->method('getStoreWebsiteId')->with($storeId)->willReturn(1);
        $attributeHelper->expects($this->atLeastOnce())->method('getProductIds')->willReturn([1]);

        $bulkManagement = $this->createMock(BulkManagementInterface::class);
        $operationFactory = $this->createMock(OperationInterfaceFactory::class);
        $identityService = $this->createMock(IdentityGeneratorInterface::class);
        $serializer = $this->createMock(SerializerInterface::class);
        $userContext = $this->createMock(UserContextInterface::class);
        $timezone = $this->createMock(TimezoneInterface::class);

        $dateTimeFilter = $this->createMock(DateTimeFilter::class);
        $dateTimeFilter->expects($this->atLeastOnce())->method('filter')->willReturnCallback(function ($v) {
            return $v ? $v . ' 00:00:00' : null;
        });

        $product = $this->createPartialMock(Product::class, ['setData', 'getSpecialToDate']);
        $product->expects($this->atLeastOnce())->method('setData')->with($this->anything());
        $product->expects($this->atLeastOnce())->method('getSpecialToDate')->willReturn('2025-09-10 00:00:00');
        $productFactory = $this->createMock(ProductFactory::class);
        $productFactory->expects($this->atLeastOnce())->method('create')->willReturn($product);

        $failingBackend = $this->createPartialMock(AbstractBackend::class, ['validate']);
        $failingBackend->expects($this->atLeastOnce())
            ->method('validate')
            ->willThrowException(new \RuntimeException('Generic error'));

        $okBackend = $this->createBackendMock(false);
        $fromAttribute = $this->createDatetimeAttributeMockForExecute($failingBackend);
        $toAttribute = $this->createDatetimeAttributeMockForExecute($okBackend);

        $eavConfig = $this->createMock(EavConfig::class);
        $eavConfig->expects($this->atLeastOnce())->method('getAttribute')->willReturnCallback(
            function ($entity, $code) use ($fromAttribute, $toAttribute) {
                unset($entity);
                return $code === 'special_from_date' ? $fromAttribute : $toAttribute;
            }
        );

        $controller = $this->buildController([
            'context' => $context,
            'attributeHelper' => $attributeHelper,
            'bulkManagement' => $bulkManagement,
            'operationFactory' => $operationFactory,
            'identityService' => $identityService,
            'serializer' => $serializer,
            'userContext' => $userContext,
            'timezone' => $timezone,
            'eavConfig' => $eavConfig,
            'productFactory' => $productFactory,
            'dateTimeFilter' => $dateTimeFilter,
        ]);

        $result = $controller->execute();

        $this->assertSame($redirect, $result);
    }

    /**
     * Execute when scheduleBulk returns false: LocalizedException is thrown and error shown.
     *
     * @covers \Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Save::execute
     * @return void
     */
    public function testExecuteShowsErrorWhenScheduleBulkFails(): void
    {
        $storeId = 1;
        $productIds = [1];
        $attributesData = ['special_from_date' => '2025-09-01', 'special_to_date' => '2025-09-10'];

        $request = $this->createMock(RequestInterface::class);
        $request->expects($this->atLeastOnce())->method('getParam')->willReturnMap([
            ['attributes', [], $attributesData],
            ['remove_website_ids', [], []],
            ['add_website_ids', [], []],
        ]);

        $messageManager = $this->createMock(ManagerInterface::class);
        $messageManager->expects($this->once())->method('addErrorMessage')
            ->with('Something went wrong while processing the request.');

        $redirect = $this->createMock(Redirect::class);
        $redirect->expects($this->atLeastOnce())->method('setPath')->willReturnSelf();
        $resultRedirectFactory = $this->createMock(RedirectFactory::class);
        $resultRedirectFactory->expects($this->once())->method('create')->willReturn($redirect);

        $objectManager = $this->createObjectManagerForValidateProducts(true);
        $context = $this->createPartialMock(
            Context::class,
            ['getRequest', 'getMessageManager', 'getResultRedirectFactory', 'getObjectManager']
        );
        $context->expects($this->atLeastOnce())->method('getRequest')->willReturn($request);
        $context->expects($this->atLeastOnce())->method('getMessageManager')->willReturn($messageManager);
        $context->expects($this->atLeastOnce())->method('getResultRedirectFactory')->willReturn($resultRedirectFactory);
        $context->expects($this->atLeastOnce())->method('getObjectManager')->willReturn($objectManager);

        $attributeHelper = $this->createMock(AttributeHelper::class);
        $attributeHelper->expects($this->atLeastOnce())->method('getSelectedStoreId')->willReturn($storeId);
        $attributeHelper->expects($this->atLeastOnce())->method('getStoreWebsiteId')->with($storeId)->willReturn(1);
        $attributeHelper->expects($this->atLeastOnce())->method('getProductIds')->willReturn($productIds);

        $bulkManagement = $this->createMock(BulkManagementInterface::class);
        $bulkManagement->expects($this->once())->method('scheduleBulk')->willReturn(false);

        $operation = $this->createMock(OperationInterface::class);
        $operationFactory = $this->createMock(OperationInterfaceFactory::class);
        $operationFactory->expects($this->atLeastOnce())->method('create')->willReturn($operation);
        $identityService = $this->createMock(IdentityGeneratorInterface::class);
        $identityService->expects($this->atLeastOnce())->method('generateId')->willReturn('bulk-uuid');
        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->atLeastOnce())->method('serialize')->willReturn('serialized');
        $userContext = $this->createMock(UserContextInterface::class);
        $userContext->expects($this->atLeastOnce())->method('getUserId')->willReturn(1);
        $timezone = $this->createMock(TimezoneInterface::class);

        $dateTimeFilter = $this->createMock(DateTimeFilter::class);
        $dateTimeFilter->expects($this->atLeastOnce())->method('filter')->willReturnCallback(function ($v) {
            return $v ? $v . ' 00:00:00' : null;
        });

        $product = $this->createPartialMock(Product::class, ['setData', 'getSpecialToDate']);
        $product->expects($this->atLeastOnce())->method('setData')->with($this->anything());
        $product->expects($this->atLeastOnce())->method('getSpecialToDate')->willReturn('2025-09-10 00:00:00');
        $productFactory = $this->createMock(ProductFactory::class);
        $productFactory->expects($this->atLeastOnce())->method('create')->willReturn($product);

        $okBackend = $this->createBackendMock(false);
        $fromAttribute = $this->createDatetimeAttributeMockForExecute($okBackend);
        $toAttribute = $this->createDatetimeAttributeMockForExecute($okBackend);

        $eavConfig = $this->createMock(EavConfig::class);
        $eavConfig->expects($this->atLeastOnce())->method('getAttribute')->willReturnCallback(
            function ($entity, $code) use ($fromAttribute, $toAttribute) {
                unset($entity);
                return $code === 'special_from_date' ? $fromAttribute : $toAttribute;
            }
        );

        $controller = $this->buildController([
            'context' => $context,
            'attributeHelper' => $attributeHelper,
            'bulkManagement' => $bulkManagement,
            'operationFactory' => $operationFactory,
            'identityService' => $identityService,
            'serializer' => $serializer,
            'userContext' => $userContext,
            'timezone' => $timezone,
            'eavConfig' => $eavConfig,
            'productFactory' => $productFactory,
            'dateTimeFilter' => $dateTimeFilter,
        ]);

        $result = $controller->execute();

        $this->assertSame($redirect, $result);
    }

    /**
     * Execute with website data covers publish website + attributes operations (makeOperation both branches).
     *
     * @covers \Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Save::execute
     * @return void
     */
    public function testExecutePublishWithWebsiteDataCoversBothOperations(): void
    {
        $storeId = 1;
        $websiteId = 1;
        $productIds = [1];
        $attributesData = ['special_from_date' => '2025-09-01', 'special_to_date' => '2025-09-10'];
        $websiteAddData = [1];
        $websiteRemoveData = [];
        $paramMap = [
            ['attributes', [], $attributesData],
            ['remove_website_ids', [], $websiteRemoveData],
            ['add_website_ids', [], $websiteAddData],
        ];
        $ctx = $this->createRequestContextAndRedirect($paramMap);

        $attributeHelper = $this->createMock(AttributeHelper::class);
        $attributeHelper->expects($this->atLeastOnce())->method('getSelectedStoreId')->willReturn($storeId);
        $attributeHelper->expects($this->atLeastOnce())
            ->method('getStoreWebsiteId')
            ->with($storeId)
            ->willReturn($websiteId);
        $attributeHelper->expects($this->atLeastOnce())->method('getProductIds')->willReturn($productIds);
        $attributeHelper->expects($this->atLeastOnce())->method('setProductIds')->with([]);

        $bulkManagement = $this->createMock(BulkManagementInterface::class);
        $bulkManagement->expects($this->once())->method('scheduleBulk')->willReturn(true);

        $operation = $this->createMock(OperationInterface::class);
        $operationFactory = $this->createMock(OperationInterfaceFactory::class);
        $operationFactory->expects($this->exactly(2))->method('create')->willReturn($operation);

        $bulkMocks = $this->createBulkOperationAndProductMocks('2025-09-10 00:00:00');

        $okBackend = $this->createBackendMock(false);
        $fromAttribute = $this->createDatetimeAttributeMockForExecute($okBackend);
        $toAttribute = $this->createDatetimeAttributeMockForExecute($okBackend);

        $eavConfig = $this->createMock(EavConfig::class);
        $eavConfig->expects($this->atLeastOnce())->method('getAttribute')->willReturnCallback(
            function ($entity, $code) use ($fromAttribute, $toAttribute) {
                unset($entity);
                return $code === 'special_from_date' ? $fromAttribute : $toAttribute;
            }
        );

        $controller = $this->buildController([
            'context' => $ctx['context'],
            'attributeHelper' => $attributeHelper,
            'bulkManagement' => $bulkManagement,
            'operationFactory' => $operationFactory,
            'identityService' => $bulkMocks['identityService'],
            'serializer' => $bulkMocks['serializer'],
            'userContext' => $bulkMocks['userContext'],
            'timezone' => $bulkMocks['timezone'],
            'eavConfig' => $eavConfig,
            'productFactory' => $bulkMocks['productFactory'],
            'dateTimeFilter' => $bulkMocks['dateTimeFilter'],
        ]);

        $this->assertSame($ctx['redirect'], $controller->execute());
    }

    /**
     * Execute with has_weight and unknown attribute covers sanitize branches (continue and unset).
     *
     * @covers \Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Save::execute
     * @return void
     */
    public function testExecuteSanitizeCoversHasWeightAndUnknownAttribute(): void
    {
        $attributesData = [
            ProductAttributeInterface::CODE_HAS_WEIGHT => 1,
            'unknown_attr' => 'val',
            'special_from_date' => '2025-09-01',
            'special_to_date' => '2025-09-10',
        ];
        $deps = $this->createExecuteSuccessDependencyMocks(
            $attributesData,
            [1],
            1,
            null,
            '2025-09-10 00:00:00'
        );
        $okBackend = $this->createBackendMock(false);
        $fromAttribute = $this->createDatetimeAttributeMockForExecute($okBackend);
        $toAttribute = $this->createDatetimeAttributeMockForExecute($okBackend);
        $attrNoId = $this->createPartialMock(AbstractAttribute::class, ['getAttributeId']);
        $attrNoId->expects($this->any())->method('getAttributeId')->willReturn(0);
        $hasWeightAttr = $this->createPartialMock(
            AbstractAttribute::class,
            ['getAttributeId', 'getBackendType', 'getFrontendInput', 'getBackend']
        );
        $hasWeightAttr->expects($this->any())->method('getAttributeId')->willReturn(1);
        $hasWeightAttr->expects($this->any())->method('getBackendType')->willReturn('int');
        $hasWeightAttr->expects($this->any())->method('getFrontendInput')->willReturn('select');
        $hasWeightAttr->expects($this->any())->method('getBackend')->willReturn($okBackend);
        $eavConfig = $this->createMock(EavConfig::class);
        $eavConfig->expects($this->atLeastOnce())->method('getAttribute')->willReturnCallback(
            function ($entity, $code) use ($fromAttribute, $toAttribute, $attrNoId, $hasWeightAttr) {
                unset($entity);
                if ($code === ProductAttributeInterface::CODE_HAS_WEIGHT) {
                    return $hasWeightAttr;
                }
                if ($code === 'unknown_attr') {
                    return $attrNoId;
                }
                return $code === 'special_from_date' ? $fromAttribute : $toAttribute;
            }
        );
        $controller = $this->buildController(array_merge($deps, ['eavConfig' => $eavConfig]));
        $result = $controller->execute();
        $this->assertSame($deps['redirect'], $result);
    }

    /**
     * Execute with empty attributes and no website data: publish builds no operations (scheduleBulk not called).
     *
     * @covers \Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Save::execute
     * @return void
     */
    public function testExecutePublishWithEmptyAttributesAndNoWebsiteBuildsNoOperations(): void
    {
        $storeId = 1;
        $productIds = [1];
        $attributesData = [];

        $request = $this->createMock(RequestInterface::class);
        $request->expects($this->atLeastOnce())->method('getParam')->willReturnMap([
            ['attributes', [], $attributesData],
            ['remove_website_ids', [], []],
            ['add_website_ids', [], []],
        ]);

        $messageManager = $this->createMock(ManagerInterface::class);
        $messageManager->expects($this->once())->method('addSuccessMessage')->with($this->anything());

        $redirect = $this->createMock(Redirect::class);
        $redirect->expects($this->atLeastOnce())->method('setPath')->willReturnSelf();
        $resultRedirectFactory = $this->createMock(RedirectFactory::class);
        $resultRedirectFactory->expects($this->once())->method('create')->willReturn($redirect);

        $objectManager = $this->createObjectManagerForValidateProducts(true);
        $context = $this->createPartialMock(
            Context::class,
            ['getRequest', 'getMessageManager', 'getResultRedirectFactory', 'getObjectManager']
        );
        $context->expects($this->atLeastOnce())->method('getRequest')->willReturn($request);
        $context->expects($this->atLeastOnce())->method('getMessageManager')->willReturn($messageManager);
        $context->expects($this->atLeastOnce())->method('getResultRedirectFactory')->willReturn($resultRedirectFactory);
        $context->expects($this->atLeastOnce())->method('getObjectManager')->willReturn($objectManager);

        $attributeHelper = $this->createMock(AttributeHelper::class);
        $attributeHelper->expects($this->atLeastOnce())->method('getSelectedStoreId')->willReturn($storeId);
        $attributeHelper->expects($this->atLeastOnce())->method('getStoreWebsiteId')->with($storeId)->willReturn(1);
        $attributeHelper->expects($this->atLeastOnce())->method('getProductIds')->willReturn($productIds);
        $attributeHelper->expects($this->atLeastOnce())->method('setProductIds')->with([]);

        $bulkManagement = $this->createMock(BulkManagementInterface::class);
        $bulkManagement->expects($this->never())->method('scheduleBulk');

        $operationFactory = $this->createMock(OperationInterfaceFactory::class);
        $identityService = $this->createMock(IdentityGeneratorInterface::class);
        $serializer = $this->createMock(SerializerInterface::class);
        $userContext = $this->createMock(UserContextInterface::class);
        $timezone = $this->createMock(TimezoneInterface::class);
        $dateTimeFilter = $this->createMock(DateTimeFilter::class);
        $eavConfig = $this->createMock(EavConfig::class);
        $productFactory = $this->createMock(ProductFactory::class);
        $product = $this->createPartialMock(Product::class, ['setData', 'getSpecialToDate']);
        $product->expects($this->once())->method('setData')->with([]);
        $product->expects($this->any())->method('getSpecialToDate')->willReturn(null);
        $productFactory->expects($this->atLeastOnce())->method('create')->willReturn($product);

        $controller = $this->buildController([
            'context' => $context,
            'attributeHelper' => $attributeHelper,
            'bulkManagement' => $bulkManagement,
            'operationFactory' => $operationFactory,
            'identityService' => $identityService,
            'serializer' => $serializer,
            'userContext' => $userContext,
            'timezone' => $timezone,
            'eavConfig' => $eavConfig,
            'productFactory' => $productFactory,
            'dateTimeFilter' => $dateTimeFilter,
        ]);

        $result = $controller->execute();

        $this->assertSame($redirect, $result);
    }

    /**
     * Execute with multiselect attribute covers sanitize multiselect branch (toggle checked, array imploded).
     * Also covers multiselect toggle not checked (unset and continue) via second attribute.
     *
     * @covers \Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Save::execute
     * @return void
     */
    public function testExecuteSanitizeMultiselectBranch(): void
    {
        $multiselectCode = 'multiselect_attr';
        $attributesData = [
            $multiselectCode => ['a', 'b'],
            'multiselect_attr_unchecked' => ['x'],
        ];
        $getPost = function ($key) use ($multiselectCode) {
            return $key === 'toggle_' . $multiselectCode ? '1' : null;
        };
        $deps = $this->createExecuteSuccessDependencyMocks($attributesData, [1], 1, $getPost, null);
        $okBackend = $this->createBackendMock(false);
        $multiselectAttr = $this->createPartialMock(
            AbstractAttribute::class,
            ['getAttributeId', 'getBackendType', 'getFrontendInput', 'getBackend']
        );
        $multiselectAttr->expects($this->any())->method('getAttributeId')->willReturn(1);
        $multiselectAttr->expects($this->any())->method('getBackendType')->willReturn('varchar');
        $multiselectAttr->expects($this->any())->method('getFrontendInput')->willReturn('multiselect');
        $multiselectAttr->expects($this->any())->method('getBackend')->willReturn($okBackend);
        $multiselectAttrUnchecked = $this->createPartialMock(
            AbstractAttribute::class,
            ['getAttributeId', 'getBackendType', 'getFrontendInput', 'getBackend']
        );
        $multiselectAttrUnchecked->expects($this->any())->method('getAttributeId')->willReturn(1);
        $multiselectAttrUnchecked->expects($this->any())->method('getBackendType')->willReturn('varchar');
        $multiselectAttrUnchecked->expects($this->any())->method('getFrontendInput')->willReturn('multiselect');
        $multiselectAttrUnchecked->expects($this->any())->method('getBackend')->willReturn($okBackend);
        $eavConfig = $this->createMock(EavConfig::class);
        $eavConfig->expects($this->atLeastOnce())->method('getAttribute')->willReturnCallback(
            function ($entity, $code) use ($multiselectAttr, $multiselectAttrUnchecked) {
                unset($entity);
                return $code === 'multiselect_attr_unchecked' ? $multiselectAttrUnchecked : $multiselectAttr;
            }
        );
        $controller = $this->buildController(array_merge($deps, ['eavConfig' => $eavConfig]));
        $result = $controller->execute();
        $this->assertSame($deps['redirect'], $result);
    }

    /**
     * Execute with empty date value covers filterDate empty path (returns null).
     *
     * @covers \Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Save::execute
     * @return void
     */
    public function testExecuteWithEmptyDateValueCoversFilterDateEmpty(): void
    {
        $storeId = 1;
        $productIds = [1];
        $attributesData = [
            'special_from_date' => '2025-09-01',
            'special_to_date' => '',
        ];

        $request = $this->createMock(RequestInterface::class);
        $request->expects($this->atLeastOnce())->method('getParam')->willReturnMap([
            ['attributes', [], $attributesData],
            ['remove_website_ids', [], []],
            ['add_website_ids', [], []],
        ]);

        $messageManager = $this->createMock(ManagerInterface::class);
        $messageManager->expects($this->once())->method('addSuccessMessage')->with($this->anything());

        $redirect = $this->createMock(Redirect::class);
        $redirect->expects($this->atLeastOnce())->method('setPath')->willReturnSelf();
        $resultRedirectFactory = $this->createMock(RedirectFactory::class);
        $resultRedirectFactory->expects($this->once())->method('create')->willReturn($redirect);

        $objectManager = $this->createObjectManagerForValidateProducts(true);
        $context = $this->createPartialMock(
            Context::class,
            ['getRequest', 'getMessageManager', 'getResultRedirectFactory', 'getObjectManager']
        );
        $context->expects($this->atLeastOnce())->method('getRequest')->willReturn($request);
        $context->expects($this->atLeastOnce())->method('getMessageManager')->willReturn($messageManager);
        $context->expects($this->atLeastOnce())->method('getResultRedirectFactory')->willReturn($resultRedirectFactory);
        $context->expects($this->atLeastOnce())->method('getObjectManager')->willReturn($objectManager);

        $attributeHelper = $this->createMock(AttributeHelper::class);
        $attributeHelper->expects($this->atLeastOnce())->method('getSelectedStoreId')->willReturn($storeId);
        $attributeHelper->expects($this->atLeastOnce())->method('getStoreWebsiteId')->with($storeId)->willReturn(1);
        $attributeHelper->expects($this->atLeastOnce())->method('getProductIds')->willReturn($productIds);
        $attributeHelper->expects($this->atLeastOnce())->method('setProductIds')->with([]);

        $bulkManagement = $this->createMock(BulkManagementInterface::class);
        $bulkManagement->expects($this->once())->method('scheduleBulk')->willReturn(true);

        $operationFactory = $this->createMock(OperationInterfaceFactory::class);
        $operationFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->createMock(OperationInterface::class));
        $identityService = $this->createMock(IdentityGeneratorInterface::class);
        $identityService->expects($this->atLeastOnce())->method('generateId')->willReturn('bulk-uuid');
        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->atLeastOnce())->method('serialize')->willReturn('serialized');
        $userContext = $this->createMock(UserContextInterface::class);
        $userContext->expects($this->atLeastOnce())->method('getUserId')->willReturn(1);
        $timezone = $this->createMock(TimezoneInterface::class);

        $dateTimeFilter = $this->createMock(DateTimeFilter::class);
        $dateTimeFilter->expects($this->atLeastOnce())->method('filter')->willReturnCallback(function ($value) {
            return $value !== '' && $value !== null ? $value . ' 00:00:00' : null;
        });

        $product = $this->createPartialMock(Product::class, ['setData', 'getSpecialToDate']);
        $product->expects($this->atLeastOnce())->method('setData')->with($this->anything());
        $product->expects($this->atLeastOnce())->method('getSpecialToDate')->willReturn(null);
        $productFactory = $this->createMock(ProductFactory::class);
        $productFactory->expects($this->atLeastOnce())->method('create')->willReturn($product);

        $okBackend = $this->createBackendMock(false);
        $fromAttribute = $this->createDatetimeAttributeMockForExecute($okBackend);
        $toAttribute = $this->createDatetimeAttributeMockForExecute($okBackend);

        $eavConfig = $this->createMock(EavConfig::class);
        $eavConfig->expects($this->atLeastOnce())->method('getAttribute')->willReturnCallback(
            function ($entity, $code) use ($fromAttribute, $toAttribute) {
                unset($entity);
                return $code === 'special_from_date' ? $fromAttribute : $toAttribute;
            }
        );

        $controller = $this->buildController([
            'context' => $context,
            'attributeHelper' => $attributeHelper,
            'bulkManagement' => $bulkManagement,
            'operationFactory' => $operationFactory,
            'identityService' => $identityService,
            'serializer' => $serializer,
            'userContext' => $userContext,
            'timezone' => $timezone,
            'eavConfig' => $eavConfig,
            'productFactory' => $productFactory,
            'dateTimeFilter' => $dateTimeFilter,
        ]);

        $result = $controller->execute();

        $this->assertSame($redirect, $result);
    }

    /**
     * Execute with datetime frontend input covers filterDate timezone conversion.
     *
     * @covers \Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Save::execute
     * @return void
     */
    public function testExecuteWithDatetimeAttributeCallsTimezoneConversion(): void
    {
        $storeId = 1;
        $productIds = [1];
        $attributesData = [
            'news_from_date' => '2025-06-01 12:00:00',
        ];

        $request = $this->createMock(RequestInterface::class);
        $request->expects($this->atLeastOnce())->method('getParam')->willReturnMap([
            ['attributes', [], $attributesData],
            ['remove_website_ids', [], []],
            ['add_website_ids', [], []],
        ]);

        $messageManager = $this->createMock(ManagerInterface::class);
        $messageManager->expects($this->once())->method('addSuccessMessage')->with($this->anything());

        $redirect = $this->createMock(Redirect::class);
        $redirect->expects($this->atLeastOnce())->method('setPath')->willReturnSelf();
        $resultRedirectFactory = $this->createMock(RedirectFactory::class);
        $resultRedirectFactory->expects($this->once())->method('create')->willReturn($redirect);

        $objectManager = $this->createObjectManagerForValidateProducts(true);
        $context = $this->createPartialMock(
            Context::class,
            ['getRequest', 'getMessageManager', 'getResultRedirectFactory', 'getObjectManager']
        );
        $context->expects($this->atLeastOnce())->method('getRequest')->willReturn($request);
        $context->expects($this->atLeastOnce())->method('getMessageManager')->willReturn($messageManager);
        $context->expects($this->atLeastOnce())->method('getResultRedirectFactory')->willReturn($resultRedirectFactory);
        $context->expects($this->atLeastOnce())->method('getObjectManager')->willReturn($objectManager);

        $attributeHelper = $this->createMock(AttributeHelper::class);
        $attributeHelper->expects($this->atLeastOnce())->method('getSelectedStoreId')->willReturn($storeId);
        $attributeHelper->expects($this->atLeastOnce())->method('getStoreWebsiteId')->with($storeId)->willReturn(1);
        $attributeHelper->expects($this->atLeastOnce())->method('getProductIds')->willReturn($productIds);
        $attributeHelper->expects($this->atLeastOnce())->method('setProductIds')->with([]);

        $bulkManagement = $this->createMock(BulkManagementInterface::class);
        $bulkManagement->expects($this->once())->method('scheduleBulk')->willReturn(true);

        $operationFactory = $this->createMock(OperationInterfaceFactory::class);
        $operationFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->createMock(OperationInterface::class));
        $identityService = $this->createMock(IdentityGeneratorInterface::class);
        $identityService->expects($this->atLeastOnce())->method('generateId')->willReturn('bulk-uuid');
        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->atLeastOnce())->method('serialize')->willReturn('serialized');
        $userContext = $this->createMock(UserContextInterface::class);
        $userContext->expects($this->atLeastOnce())->method('getUserId')->willReturn(1);

        $timezone = $this->createMock(TimezoneInterface::class);
        $timezone->expects($this->once())->method('convertConfigTimeToUtc')->willReturn('2025-06-01 00:00:00');

        $dateTimeFilter = $this->createMock(DateTimeFilter::class);
        $dateTimeFilter->expects($this->any())->method('filter')->willReturn('2025-06-01 12:00:00');

        $product = $this->createPartialMock(Product::class, ['setData', 'getSpecialToDate']);
        $product->expects($this->any())->method('setData')->with($this->anything());
        $product->expects($this->any())->method('getSpecialToDate')->willReturn(null);
        $productFactory = $this->createMock(ProductFactory::class);
        $productFactory->expects($this->atLeastOnce())->method('create')->willReturn($product);

        $okBackend = $this->createBackendMock(false);
        $newsFromAttribute = $this->createDatetimeAttributeMockForExecuteWithFrontendInput($okBackend, 'datetime');

        $eavConfig = $this->createMock(EavConfig::class);
        $eavConfig->expects($this->atLeastOnce())->method('getAttribute')->willReturn($newsFromAttribute);

        $controller = $this->buildController([
            'context' => $context,
            'attributeHelper' => $attributeHelper,
            'bulkManagement' => $bulkManagement,
            'operationFactory' => $operationFactory,
            'identityService' => $identityService,
            'serializer' => $serializer,
            'userContext' => $userContext,
            'timezone' => $timezone,
            'eavConfig' => $eavConfig,
            'productFactory' => $productFactory,
            'dateTimeFilter' => $dateTimeFilter,
        ]);

        $result = $controller->execute();

        $this->assertSame($redirect, $result);
    }

    /**
     * Create attribute mock for execute flow with configurable frontend input (date vs datetime).
     *
     * @param AbstractBackend $backend Backend instance returned by getBackend().
     * @param string $frontendInput Frontend input type (e.g. 'date', 'datetime').
     * @return AbstractAttribute
     */
    private function createDatetimeAttributeMockForExecuteWithFrontendInput(
        AbstractBackend $backend,
        string $frontendInput = 'date'
    ): AbstractAttribute {
        $attribute = $this->createPartialMockWithReflection(
            AbstractAttribute::class,
            ['getAttributeId', 'getBackendType', 'getFrontendInput', 'setMaxValue', 'getBackend']
        );
        $attribute->expects($this->any())->method('getAttributeId')->willReturn(1);
        $attribute->expects($this->any())->method('getBackendType')->willReturn('datetime');
        $attribute->expects($this->any())->method('getFrontendInput')->willReturn($frontendInput);
        $attribute->expects($this->any())->method('setMaxValue')->willReturnSelf();
        $attribute->expects($this->any())->method('getBackend')->willReturn($backend);
        return $attribute;
    }

    /**
     * Create attribute mock for execute flow (sanitize + validate): datetime type with backend.
     *
     * @param AbstractBackend $backend Backend instance returned by getBackend().
     * @return AbstractAttribute
     */
    private function createDatetimeAttributeMockForExecute(AbstractBackend $backend): AbstractAttribute
    {
        $attribute = $this->createPartialMockWithReflection(
            AbstractAttribute::class,
            ['getAttributeId', 'getBackendType', 'getFrontendInput', 'setMaxValue', 'getBackend']
        );
        $attribute->expects($this->any())->method('getAttributeId')->willReturn(1);
        $attribute->expects($this->any())->method('getBackendType')->willReturn('datetime');
        $attribute->expects($this->any())->method('getFrontendInput')->willReturn('date');
        $attribute->expects($this->any())->method('setMaxValue')->willReturnSelf();
        $attribute->expects($this->any())->method('getBackend')->willReturn($backend);
        return $attribute;
    }

    /**
     * Create a backend mock with validate behavior.
     *
     * @param bool $shouldThrowException When true, validate() throws EavAttributeException.
     * @return AbstractBackend
     */
    private function createBackendMock(bool $shouldThrowException): AbstractBackend
    {
        $backend = $this->createPartialMock(AbstractBackend::class, ['validate']);
        
        if ($shouldThrowException) {
            $backend->expects($this->any())->method('validate')->willThrowException(
                new EavAttributeException(__('Make sure the To Date is later than or the same as the From Date.'))
            );
        } else {
            $backend->expects($this->any())->method('validate')->willReturn(true);
        }
        
        return $backend;
    }
}
