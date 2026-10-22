<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
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
use PHPUnit\Framework\Attributes\DataProvider;
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

        $this->groupRepository = $this->createMock(GroupRepositoryInterface::class);

        $this->customerMetadata = $this->createMock(CustomerMetadataInterface::class);

        $this->storeManager = $this->createMock(StoreManagerInterface::class);

        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);

        $this->document = new Document(
            $this->attributeValueFactory,
            $this->groupRepository,
            $this->customerMetadata,
            $this->storeManager,
            $this->scopeConfig
        );
    }

    /**
     * @covers       \Magento\Customer\Ui\Component\DataProvider\Document::getCustomAttribute
     * @param int $genderId
     * @param string $attributeValue
     * @param string $attributeLabel
     */
    #[DataProvider('getGenderAttributeDataProvider')]
    public function testGetGenderAttribute(int $genderId, string $attributeValue, string $attributeLabel): void
    {
        $expectedResult = !empty($attributeValue) ? $attributeLabel : $genderId;

        $this->document->setData('gender', $genderId);

        $this->groupRepository->expects(static::never())
            ->method('getById');

        $this->storeManager->expects(static::never())
            ->method('getWebsites');

        $metadata = $this->createMock(AttributeMetadataInterface::class);

        $this->customerMetadata->expects(static::once())
            ->method('getAttributeMetadata')
            ->willReturn($metadata);

        $option = $this->createMock(OptionInterface::class);

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
    public static function getGenderAttributeDataProvider()
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

        $group1 = $this->createMock(GroupInterface::class);
        $group2 = $this->createMock(GroupInterface::class);

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

        $website = $this->createMock(WebsiteInterface::class);

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
     * The admin website (id 0) is omitted from getWebsites() unless the default is requested, so a customer
     * created in the admin must still resolve to a website name instead of an undefined array key.
     *
     * @covers \Magento\Customer\Ui\Component\DataProvider\Document::getCustomAttribute
     */
    public function testGetWebsiteAttributeForAdminWebsite()
    {
        $this->document->setData('website_id', 0);

        $this->groupRepository->expects(static::never())
            ->method('getById');

        $this->customerMetadata->expects(static::never())
            ->method('getAttributeMetadata');

        $adminWebsite = $this->createMock(WebsiteInterface::class);
        $mainWebsite = $this->createMock(WebsiteInterface::class);

        $this->storeManager->expects(static::once())
            ->method('getWebsites')
            ->with(true)
            ->willReturn([0 => $adminWebsite, 1 => $mainWebsite]);

        $adminWebsite->expects(static::once())
            ->method('getName')
            ->willReturn('Admin');

        $mainWebsite->expects(static::never())
            ->method('getName');

        $attribute = $this->document->getCustomAttribute('website_id');
        static::assertEquals('Admin', $attribute->getValue());
    }

    /**
     * A website id with no matching website, for example a NULL column or a website removed after the customer
     * was created, resolves to an empty value rather than raising an undefined array key.
     *
     * @param int|null $websiteId
     * @covers \Magento\Customer\Ui\Component\DataProvider\Document::getCustomAttribute
     */
    #[DataProvider('getUnknownWebsiteDataProvider')]
    public function testGetWebsiteAttributeForUnknownWebsite($websiteId): void
    {
        $this->document->setData('website_id', $websiteId);

        $website = $this->createMock(WebsiteInterface::class);

        $this->storeManager->expects(static::once())
            ->method('getWebsites')
            ->with(true)
            ->willReturn([0 => $website, 1 => $website]);

        $website->expects(static::never())
            ->method('getName');

        $attribute = $this->document->getCustomAttribute('website_id');
        static::assertEquals('', $attribute->getValue());
    }

    /**
     * Data provider for testGetWebsiteAttributeForUnknownWebsite
     *
     * @return array
     */
    public static function getUnknownWebsiteDataProvider()
    {
        return [
            'website that no longer exists' => [99],
            'null website id' => [null],
        ];
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
     * The website id is written into the document's data before the confirmation column is read, so a
     * falsy but valid id such as the admin website's 0 must not fall through to the website label.
     *
     * @covers \Magento\Customer\Ui\Component\DataProvider\Document::getCustomAttribute
     */
    public function testConfirmationScopeUsesTheWebsiteIdForAdminWebsite()
    {
        $website = $this->createMock(WebsiteInterface::class);
        $website->method('getName')
            ->willReturn('Admin');

        $this->storeManager->method('getWebsites')
            ->willReturn([0 => $website, 1 => $website]);

        $capturedScopeCode = false;
        $this->scopeConfig->expects(static::once())
            ->method('isSetFlag')
            ->willReturnCallback(
                function ($path, $scopeType, $scopeCode) use (&$capturedScopeCode) {
                    $capturedScopeCode = $scopeCode;
                    return false;
                }
            );

        $this->document->setData('website_id', 0);
        $this->document->setData('confirmation', null);

        // customer_listing renders website_id (sortOrder 110) before confirmation (sortOrder 130)
        $this->document->getCustomAttribute('website_id');
        $this->document->getCustomAttribute('confirmation');

        static::assertSame(0, $capturedScopeCode);
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
            ->onlyMethods(['create'])
            ->getMock();

        $attributeValue = new AttributeValue();

        $this->attributeValueFactory->expects(static::once())
            ->method('create')
            ->willReturn($attributeValue);
    }
}
