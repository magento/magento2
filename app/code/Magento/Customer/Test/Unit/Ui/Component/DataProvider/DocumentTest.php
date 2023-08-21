<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Ui\Component\DataProvider;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\Data\OptionInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Ui\Component\DataProvider\Document;
use Magento\Framework\Api\AttributeValue;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Phrase;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DocumentTest extends TestCase
{
    /**
     * @var GroupRepositoryInterface|MockObject
     */
    private $groupRepository;

    /**
     * @var AttributeValueFactory|MockObject
     */
    private $attributeValueFactory;

    /**
     * @var CustomerMetadataInterface|MockObject
     */
    private $customerMetadata;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfig;

    /**
     * @var Document
     */
    private $document;

    protected function setUp(): void
    {
        $this->initAttributeValueFactoryMock();

        $this->groupRepository = $this->getMockForAbstractClass(GroupRepositoryInterface::class);

        $this->customerMetadata = $this->getMockForAbstractClass(CustomerMetadataInterface::class);

        $this->storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);

        $this->scopeConfig = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $this->document = new Document(
            $this->attributeValueFactory,
            $this->groupRepository,
            $this->customerMetadata,
            $this->storeManager,
            $this->scopeConfig
        );
    }

    /**
     * @dataProvider getGenderAttributeDataProvider
     * @covers       \Magento\Customer\Ui\Component\DataProvider\Document::getCustomAttribute
     * @param int $genderId
     * @param string $attributeValue
     * @param string $attributeLabel
     */
    public function testGetGenderAttribute(int $genderId, string $attributeValue, string $attributeLabel): void
    {
        $expectedResult = !empty($attributeValue) ? $attributeLabel : $genderId;

        $this->document->setData('gender', $genderId);

        $this->groupRepository->expects(static::never())
            ->method('getById');

        $this->storeManager->expects(static::never())
            ->method('getWebsites');

        $metadata = $this->getMockForAbstractClass(AttributeMetadataInterface::class);

        $this->customerMetadata->expects(static::once())
            ->method('getAttributeMetadata')
            ->willReturn($metadata);

        $option = $this->getMockForAbstractClass(OptionInterface::class);

        $metadata->expects(static::once())
            ->method('getOptions')
            ->willReturn([$genderId => $option]);

        $option->expects(static::once())
            ->method('getValue')
            ->willReturn($attributeValue);

        $option->expects(static::any())
            ->method('getLabel')
            ->willReturn($attributeLabel);

        $attribute = $this->document->getCustomAttribute('gender');
        static::assertEquals($expectedResult, $attribute->getValue());
    }

    /**
     * Data provider for testGetGenderAttribute
     * @return array
     */
    public function getGenderAttributeDataProvider()
    {
        return [
            'with valid gender label and value' => [
                1, '1', 'Male'
            ],
            'with empty gender label' => [
                2, '2', ''
            ],
            'with empty gender value' => [
                3, '', 'test'
            ],
            'with empty gender label and value' => [
                4, '', ''
            ]
        ];
    }

    /**
     * @covers \Magento\Customer\Ui\Component\DataProvider\Document::getCustomAttribute
     */
    public function testGetGroupAttribute()
    {
        $this->document->setData('group_id', 1);

        $this->customerMetadata->expects(static::never())
            ->method('getAttributeMetadata');

        $this->storeManager->expects(static::never())
            ->method('getWebsites');

        $group1 = $this->getMockForAbstractClass(GroupInterface::class);
        $group2 = $this->getMockForAbstractClass(GroupInterface::class);

        $this->groupRepository->expects(static::exactly(2))
            ->method('getById')
            ->willReturnMap([[1, $group1], [2, $group2]]);

        $group1->expects(static::once())
            ->method('getCode')
            ->willReturn('General');

        $group2->expects(static::once())
            ->method('getCode')
            ->willReturn('Wholesale');

        $attribute = $this->document->getCustomAttribute('group_id');
        static::assertEquals('General', $attribute->getValue());

        // Check that the group code is resolved from cache
        $this->document->setData('group_id', 1);
        $attribute = $this->document->getCustomAttribute('group_id');
        static::assertEquals('General', $attribute->getValue());

        // Check that the group code is resolved from repository if missing in the cache
        $this->document->setData('group_id', 2);
        $attribute = $this->document->getCustomAttribute('group_id');
        static::assertEquals('Wholesale', $attribute->getValue());
    }

    /**
     * @covers \Magento\Customer\Ui\Component\DataProvider\Document::getCustomAttribute
     */
    public function testGetWebsiteAttribute()
    {
        $websiteId = 1;
        $this->document->setData('website_id', $websiteId);

        $this->groupRepository->expects(static::never())
            ->method('getById');

        $this->customerMetadata->expects(static::never())
            ->method('getAttributeMetadata');

        $website = $this->getMockForAbstractClass(WebsiteInterface::class);

        $this->storeManager->expects(static::once())
            ->method('getWebsites')
            ->willReturn([$websiteId => $website]);

        $website->expects(static::once())
            ->method('getName')
            ->willReturn('Main Website');

        $attribute = $this->document->getCustomAttribute('website_id');
        static::assertEquals('Main Website', $attribute->getValue());
    }

    /**
     * @covers \Magento\Customer\Ui\Component\DataProvider\Document::getCustomAttribute
     */
    public function testGetConfirmationAttribute()
    {
        $websiteId = 1;
        $this->document->setData('original_website_id', $websiteId);

        $this->scopeConfig->expects(static::once())
            ->method('isSetFlag')
            ->with()
            ->willReturn(true);

        $this->document->setData('confirmation', null);
        $attribute = $this->document->getCustomAttribute('confirmation');

        $value = $attribute->getValue();
        static::assertInstanceOf(Phrase::class, $value);
        static::assertEquals('Confirmed', (string)$value);
    }

    /**
     * @covers \Magento\Customer\Ui\Component\DataProvider\Document::getCustomAttribute
     */
    public function testGetAccountLockValue()
    {
        $this->document->setData('lock_expires', null);

        $attribute = $this->document->getCustomAttribute('lock_expires');

        $value = $attribute->getValue();
        static::assertInstanceOf(Phrase::class, $value);
        static::assertEquals('Unlocked', (string)$value);
    }

    /**
     * Create mock for attribute value factory
     * @return void
     */
    private function initAttributeValueFactoryMock()
    {
        $this->attributeValueFactory = $this->getMockBuilder(AttributeValueFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $attributeValue = new AttributeValue();

        $this->attributeValueFactory->expects(static::once())
            ->method('create')
            ->willReturn($attributeValue);
    }
}
