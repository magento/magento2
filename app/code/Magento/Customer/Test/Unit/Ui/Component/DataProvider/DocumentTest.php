<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class DocumentTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DocumentTest extends \PHPUnit\Framework\TestCase
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

    protected function setUp()
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
     * @covers \Magento\Customer\Ui\Component\DataProvider\Document::getCustomAttribute
     */
    public function testGetGenderAttribute()
    {
        $genderId = 1;
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
            ->method('getLabel')
            ->willReturn('Male');

        $attribute = $this->document->getCustomAttribute('gender');
        static::assertEquals('Male', $attribute->getValue());
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

        $group = $this->getMockForAbstractClass(GroupInterface::class);

        $this->groupRepository->expects(static::once())
            ->method('getById')
            ->willReturn($group);

        $group->expects(static::once())
            ->method('getCode')
            ->willReturn('General');

        $attribute = $this->document->getCustomAttribute('group_id');
        static::assertEquals('General', $attribute->getValue());
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
            ->method('getValue')
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
