<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product\Action\Attribute;

use Magento\Backend\App\Action\Context;
use Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Save;
use Magento\Catalog\Helper\Product\Edit\Action\Attribute as AttributeHelper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Bulk\BulkManagementInterface;
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
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    private function buildController(
        Context $context,
        AttributeHelper $attributeHelper,
        BulkManagementInterface $bulkManagement,
        OperationInterfaceFactory $operationFactory,
        IdentityGeneratorInterface $identityService,
        SerializerInterface $serializer,
        UserContextInterface $userContext,
        TimezoneInterface $timezone,
        EavConfig $eavConfig,
        ProductFactory $productFactory,
        DateTimeFilter $dateTimeFilter
    ): Save {
        return new Save(
            $context,
            $attributeHelper,
            $bulkManagement,
            $operationFactory,
            $identityService,
            $serializer,
            $userContext,
            100,
            $timezone,
            $eavConfig,
            $productFactory,
            $dateTimeFilter
        );
    }

    public function testValidateProductAttributesSetsMaxValueAndConvertsEavException(): void
    {
        $context = $this->createMock(Context::class);
        $attributeHelper = $this->createMock(AttributeHelper::class);
        $bulkManagement = $this->createMock(BulkManagementInterface::class);
        $operationFactory = $this->createMock(OperationInterfaceFactory::class);
        $identityService = $this->createMock(IdentityGeneratorInterface::class);
        $serializer = $this->createMock(SerializerInterface::class);
        $userContext = $this->createMock(UserContextInterface::class);
        $timezone = $this->createMock(TimezoneInterface::class);
        $eavConfig = $this->createMock(EavConfig::class);
        $productFactory = $this->createMock(ProductFactory::class);
        $dateTimeFilter = $this->createMock(DateTimeFilter::class);

        $product = $this->createPartialMock(Product::class, ['setData', 'getSpecialToDate']);
        $product->method('setData')->with([
            'special_from_date' => '2025-09-10 00:00:00',
            'special_to_date' => '2025-09-01 00:00:00',
        ]);
        $product->method('getSpecialToDate')->willReturn('2025-09-01 00:00:00');

        $productFactory->method('create')->willReturn($product);

        // Attribute for special_from_date
        $fromAttrBackend = $this->createBackendMock(true);
        $fromAttribute = $this->createAttributeMock('2025-09-01 00:00:00', $fromAttrBackend);

        // Attribute for special_to_date
        $toAttrBackend = $this->createBackendMock(false);
        $toAttribute = $this->createAttributeMock(null, $toAttrBackend);

        // eavConfig should return attributes for 'special_from_date' and 'special_to_date'
        $eavConfig->method('getAttribute')
        ->willReturnCallback(function ($entity, $code) use ($fromAttribute, $toAttribute) {
            unset($entity);
            return $code === 'special_from_date' ? $fromAttribute : $toAttribute;
        });

        $controller = $this->buildController(
            $context,
            $attributeHelper,
            $bulkManagement,
            $operationFactory,
            $identityService,
            $serializer,
            $userContext,
            $timezone,
            $eavConfig,
            $productFactory,
            $dateTimeFilter
        );

        $method = new \ReflectionMethod($controller, 'validateProductAttributes');
        $method->setAccessible(true);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Make sure the To Date is later than or the same as the From Date.');

        $method->invoke($controller, [
            'special_from_date' => '2025-09-10 00:00:00',
            'special_to_date'   => '2025-09-01 00:00:00',
        ]);
    }

    public function testValidateProductAttributesPassesWhenDatesValid(): void
    {
        $context = $this->createMock(Context::class);
        $attributeHelper = $this->createMock(AttributeHelper::class);
        $bulkManagement = $this->createMock(BulkManagementInterface::class);
        $operationFactory = $this->createMock(OperationInterfaceFactory::class);
        $identityService = $this->createMock(IdentityGeneratorInterface::class);
        $serializer = $this->createMock(SerializerInterface::class);
        $userContext = $this->createMock(UserContextInterface::class);
        $timezone = $this->createMock(TimezoneInterface::class);
        $eavConfig = $this->createMock(EavConfig::class);
        $productFactory = $this->createMock(ProductFactory::class);
        $dateTimeFilter = $this->createMock(DateTimeFilter::class);

        $product = $this->createPartialMock(Product::class, ['setData', 'getSpecialToDate']);
        $product->method('setData')->with([
            'special_from_date' => '2025-09-01 00:00:00',
            'special_to_date' => '2025-09-10 00:00:00',
        ]);
        $product->method('getSpecialToDate')->willReturn('2025-09-10 00:00:00');
        $productFactory->method('create')->willReturn($product);

        $okBackend = $this->createBackendMock(false);
        $fromAttribute = $this->createAttributeMock('2025-09-10 00:00:00', $okBackend);
        $toAttribute = $this->createAttributeMock(null, $okBackend);

        $eavConfig->method('getAttribute')
        ->willReturnCallback(function ($entity, $code) use ($fromAttribute, $toAttribute) {
            unset($entity);
            return $code === 'special_from_date' ? $fromAttribute : $toAttribute;
        });

        $controller = $this->buildController(
            $context,
            $attributeHelper,
            $bulkManagement,
            $operationFactory,
            $identityService,
            $serializer,
            $userContext,
            $timezone,
            $eavConfig,
            $productFactory,
            $dateTimeFilter
        );

        $method = new \ReflectionMethod($controller, 'validateProductAttributes');
        $method->setAccessible(true);

        // Should not throw
        $method->invoke($controller, [
            'special_from_date' => '2025-09-01 00:00:00',
            'special_to_date'   => '2025-09-10 00:00:00',
        ]);

        $this->addToAssertionCount(1);
    }

    /**
     * Create a backend mock with validate behavior
     */
    private function createBackendMock(bool $shouldThrowException)
    {
        $backend = $this->createPartialMockWithReflection(
            AbstractBackend::class,
            ['validate']
        );
        
        if ($shouldThrowException) {
            $backend->method('validate')->willThrowException(
                new EavAttributeException(__('Make sure the To Date is later than or the same as the From Date.'))
            );
        } else {
            $backend->method('validate')->willReturn(true);
        }
        
        return $backend;
    }

    /**
     * Create an attribute mock with maxValue and backend
     */
    private function createAttributeMock(?string $maxValue, $backend)
    {
        $attribute = $this->createPartialMockWithReflection(
            AbstractAttribute::class,
            ['setMaxValue', 'getMaxValue', 'getBackend']
        );
        
        $attribute->method('setMaxValue')->willReturnSelf();
        $attribute->method('getMaxValue')->willReturn($maxValue);
        $attribute->method('getBackend')->willReturn($backend);
        
        return $attribute;
    }
}
