<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Edit\Tab\Attributes;

use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes\Search;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DB\Helper;
use Magento\Framework\Escaper;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Search block
 *
 * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes\Search
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SearchTest extends TestCase
{
    /**
     * @var Search
     */
    private Search $block;

    /**
     * @var Context|MockObject
     */
    private MockObject $contextMock;

    /**
     * @var Helper|MockObject
     */
    private MockObject $resourceHelperMock;

    /**
     * @var CollectionFactory|MockObject
     */
    private MockObject $collectionFactoryMock;

    /**
     * @var Registry|MockObject
     */
    private MockObject $registryMock;

    /**
     * @var JsonHelper|MockObject
     */
    private MockObject $jsonHelperMock;

    /**
     * @var UrlInterface|MockObject
     */
    private MockObject $urlBuilderMock;

    /**
     * @var RequestInterface|MockObject
     */
    private MockObject $requestMock;

    /**
     * @var Escaper|MockObject
     */
    private MockObject $escaperMock;

    /**
     * @var ObjectManager
     */
    private ObjectManager $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->objectManager->prepareObjectManager();

        $this->contextMock = $this->createMock(Context::class);
        $this->resourceHelperMock = $this->createMock(Helper::class);
        $this->collectionFactoryMock = $this->createPartialMock(CollectionFactory::class, ['create']);
        $this->registryMock = $this->createMock(Registry::class);
        $this->jsonHelperMock = $this->createMock(JsonHelper::class);
        $this->urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->escaperMock = $this->createMock(Escaper::class);

        $this->contextMock->expects($this->any())
            ->method('getUrlBuilder')
            ->willReturn($this->urlBuilderMock);

        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);

        $this->contextMock->expects($this->any())
            ->method('getEscaper')
            ->willReturn($this->escaperMock);

        $this->block = $this->objectManager->getObject(
            Search::class,
            [
                'context' => $this->contextMock,
                'resourceHelper' => $this->resourceHelperMock,
                'collectionFactory' => $this->collectionFactoryMock,
                'registry' => $this->registryMock,
                'jsonHelper' => $this->jsonHelperMock
            ]
        );
    }

    /**
     * Data provider for testing getSelectorOptions required keys
     *
     * @return array
     */
    public static function selectorOptionsRequiredKeysDataProvider(): array
    {
        return [
            'source key exists' => ['source'],
            'minLength key exists' => ['minLength'],
            'ajaxOptions key exists' => ['ajaxOptions'],
            'template key exists' => ['template'],
            'data key exists' => ['data']
        ];
    }

    /**
     * Test getSelectorOptions returns array with required key
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes\Search::getSelectorOptions
     * @dataProvider selectorOptionsRequiredKeysDataProvider
     * @param string $expectedKey
     * @return void
     */
    public function testGetSelectorOptionsReturnsArrayWithRequiredKey(string $expectedKey): void
    {
        $templateId = 4;
        $groupId = 10;
        $suggestedAttributes = [
            ['id' => 1, 'label' => 'Color', 'code' => 'color'],
            ['id' => 2, 'label' => 'Size', 'code' => 'size']
        ];

        $this->setupProductRegistryMock($templateId);
        $this->setupUrlMocks('http://example.com/catalog/product/suggestAttributes');
        $this->block->setGroupId($groupId);

        $collectionMock = $this->createMock(Collection::class);
        $this->setupCollectionMock($collectionMock, '', $templateId, $suggestedAttributes);

        $result = $this->block->getSelectorOptions();

        $this->assertIsArray($result);
        $this->assertArrayHasKey($expectedKey, $result);
    }

    /**
     * Test getSelectorOptions returns correct source URL
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes\Search::getSelectorOptions
     * @return void
     */
    public function testGetSelectorOptionsReturnsCorrectSourceUrl(): void
    {
        $templateId = 4;
        $expectedUrl = 'http://example.com/catalog/product/suggestAttributes';
        $escapedUrl = 'http://example.com/catalog/product/suggestAttributes';

        $this->setupProductRegistryMock($templateId);

        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('catalog/product/suggestAttributes')
            ->willReturn($expectedUrl);

        $this->escaperMock->expects($this->once())
            ->method('escapeUrl')
            ->with($expectedUrl)
            ->willReturn($escapedUrl);

        $collectionMock = $this->createMock(Collection::class);
        $this->setupCollectionMock($collectionMock, '', $templateId, []);

        $result = $this->block->getSelectorOptions();

        $this->assertSame($escapedUrl, $result['source']);
    }

    /**
     * Test getSelectorOptions returns correct min length
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes\Search::getSelectorOptions
     * @return void
     */
    public function testGetSelectorOptionsReturnsCorrectMinLength(): void
    {
        $templateId = 4;

        $this->setupProductRegistryMock($templateId);
        $this->setupUrlMocks();

        $collectionMock = $this->createMock(Collection::class);
        $this->setupCollectionMock($collectionMock, '', $templateId, []);

        $result = $this->block->getSelectorOptions();

        $this->assertSame(0, $result['minLength']);
    }

    /**
     * Test getSelectorOptions returns correct AJAX options with template ID
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes\Search::getSelectorOptions
     * @return void
     */
    public function testGetSelectorOptionsReturnsCorrectAjaxOptions(): void
    {
        $templateId = 4;

        $this->setupProductRegistryMock($templateId);
        $this->setupUrlMocks();

        $collectionMock = $this->createMock(Collection::class);
        $this->setupCollectionMock($collectionMock, '', $templateId, []);

        $result = $this->block->getSelectorOptions();

        $this->assertSame(['data' => ['template_id' => $templateId]], $result['ajaxOptions']);
    }

    /**
     * Test getSelectorOptions returns correct template selector
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes\Search::getSelectorOptions
     * @return void
     */
    public function testGetSelectorOptionsReturnsCorrectTemplateSelector(): void
    {
        $templateId = 4;
        $groupId = 10;

        $this->setupProductRegistryMock($templateId);
        $this->setupUrlMocks();
        $this->block->setGroupId($groupId);

        $collectionMock = $this->createMock(Collection::class);
        $this->setupCollectionMock($collectionMock, '', $templateId, []);

        $result = $this->block->getSelectorOptions();

        $this->assertSame(
            '[data-template-for="product-attribute-search-' . $groupId . '"]',
            $result['template']
        );
    }

    /**
     * Test getSelectorOptions returns suggested attributes data
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes\Search::getSelectorOptions
     * @return void
     */
    public function testGetSelectorOptionsReturnsSuggestedAttributesData(): void
    {
        $templateId = 4;
        $suggestedAttributes = [
            ['id' => 1, 'label' => 'Color', 'code' => 'color'],
            ['id' => 2, 'label' => 'Size', 'code' => 'size']
        ];

        $this->setupProductRegistryMock($templateId);
        $this->setupUrlMocks();

        $collectionMock = $this->createMock(Collection::class);
        $this->setupCollectionMock($collectionMock, '', $templateId, $suggestedAttributes);

        $result = $this->block->getSelectorOptions();

        $this->assertSame($suggestedAttributes, $result['data']);
    }

    /**
     * Data provider for testing getSuggestedAttributes method
     *
     * @return array
     */
    public static function suggestedAttributesDataProvider(): array
    {
        return [
            'with label part' => [
                'labelPart' => 'col',
                'escapedLabelPart' => '%col%',
                'templateId' => 4,
                'expectedAttributes' => [
                    ['id' => 1, 'label' => 'Color', 'code' => 'color'],
                    ['id' => 2, 'label' => 'Column', 'code' => 'column']
                ]
            ],
            'with empty label part' => [
                'labelPart' => '',
                'escapedLabelPart' => '%%',
                'templateId' => 4,
                'expectedAttributes' => [
                    ['id' => 1, 'label' => 'Color', 'code' => 'color'],
                    ['id' => 2, 'label' => 'Size', 'code' => 'size'],
                    ['id' => 3, 'label' => 'Material', 'code' => 'material']
                ]
            ]
        ];
    }

    /**
     * Test getSuggestedAttributes method returns expected attributes
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes\Search::getSuggestedAttributes
     * @dataProvider suggestedAttributesDataProvider
     * @param string $labelPart
     * @param string $escapedLabelPart
     * @param int $templateId
     * @param array $expectedAttributes
     * @return void
     */
    public function testGetSuggestedAttributesReturnsExpectedAttributes(
        string $labelPart,
        string $escapedLabelPart,
        int $templateId,
        array $expectedAttributes
    ): void {
        // Mock resource helper
        $this->resourceHelperMock->expects($this->once())
            ->method('addLikeEscape')
            ->with($labelPart, ['position' => 'any'])
            ->willReturn($escapedLabelPart);

        // Setup collection mock
        $collectionMock = $this->createMock(Collection::class);
        $this->setupCollectionMock($collectionMock, $escapedLabelPart, $templateId, $expectedAttributes);

        $result = $this->block->getSuggestedAttributes($labelPart, $templateId);

        $this->assertSame($expectedAttributes, $result);
    }

    /**
     * Test getSuggestedAttributes method without template ID uses request parameter
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes\Search::getSuggestedAttributes
     * @return void
     */
    public function testGetSuggestedAttributesWithoutTemplateIdUsesRequestParam(): void
    {
        $labelPart = 'test';
        $escapedLabelPart = '%test%';
        $templateId = 5;
        $expectedAttributes = [
            ['id' => 10, 'label' => 'Test Attribute', 'code' => 'test_attribute']
        ];

        // Mock resource helper
        $this->resourceHelperMock->expects($this->once())
            ->method('addLikeEscape')
            ->with($labelPart, ['position' => 'any'])
            ->willReturn($escapedLabelPart);

        // Mock request to return template_id parameter
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('template_id')
            ->willReturn($templateId);

        // Setup collection mock
        $collectionMock = $this->createMock(Collection::class);
        $this->setupCollectionMock($collectionMock, $escapedLabelPart, $templateId, $expectedAttributes);

        // Call without template ID
        $result = $this->block->getSuggestedAttributes($labelPart);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame($expectedAttributes, $result);
    }

    /**
     * Test getSuggestedAttributes method returns empty array when no attributes found
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes\Search::getSuggestedAttributes
     * @return void
     */
    public function testGetSuggestedAttributesReturnsEmptyArrayWhenNoAttributesFound(): void
    {
        $labelPart = 'nonexistent';
        $escapedLabelPart = '%nonexistent%';
        $templateId = 4;

        // Mock resource helper
        $this->resourceHelperMock->expects($this->once())
            ->method('addLikeEscape')
            ->with($labelPart, ['position' => 'any'])
            ->willReturn($escapedLabelPart);

        // Setup collection mock with no results
        $collectionMock = $this->createMock(Collection::class);
        $this->setupCollectionMock($collectionMock, $escapedLabelPart, $templateId, []);

        $result = $this->block->getSuggestedAttributes($labelPart, $templateId);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test getAddAttributeUrl method
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes\Search::getAddAttributeUrl
     * @return void
     */
    public function testGetAddAttributeUrl(): void
    {
        $expectedUrl = 'http://example.com/catalog/product/addAttributeToTemplate';

        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('catalog/product/addAttributeToTemplate')
            ->willReturn($expectedUrl);

        $result = $this->block->getAddAttributeUrl();

        $this->assertSame($expectedUrl, $result);
    }

    /**
     * Setup product and registry mocks for getSelectorOptions tests
     *
     * @param int $templateId
     * @return void
     */
    private function setupProductRegistryMock(int $templateId): void
    {
        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->once())
            ->method('getAttributeSetId')
            ->willReturn($templateId);

        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with('product')
            ->willReturn($productMock);
    }

    /**
     * Setup URL builder and escaper mocks for getSelectorOptions tests
     *
     * @param string $url
     * @return void
     */
    private function setupUrlMocks(string $url = 'http://example.com/url'): void
    {
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->willReturn($url);

        $this->escaperMock->expects($this->once())
            ->method('escapeUrl')
            ->willReturn($url);
    }

    /**
     * Setup collection mock helper
     *
     * @param Collection|MockObject $collectionMock
     * @param string $escapedLabelPart
     * @param int $templateId
     * @param array $attributesData
     * @return void
     */
    private function setupCollectionMock(
        MockObject $collectionMock,
        string $escapedLabelPart,
        int $templateId,
        array $attributesData
    ): void {
        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        $collectionMock->expects($this->once())
            ->method('addFieldToFilter')
            ->with('frontend_label', ['like' => $escapedLabelPart])
            ->willReturnSelf();

        $collectionMock->expects($this->once())
            ->method('setExcludeSetFilter')
            ->with($templateId)
            ->willReturnSelf();

        $collectionMock->expects($this->once())
            ->method('setPageSize')
            ->with(20)
            ->willReturnSelf();

        // Create attribute mocks
        $attributeMocks = [];
        foreach ($attributesData as $attrData) {
            $attributeMock = $this->getMockBuilder(Attribute::class)
                ->disableOriginalConstructor()
                ->addMethods(['getFrontendLabel'])
                ->onlyMethods(['getId', 'getAttributeCode'])
                ->getMock();
            $attributeMock->expects($this->once())
                ->method('getId')
                ->willReturn($attrData['id']);
            $attributeMock->expects($this->once())
                ->method('getFrontendLabel')
                ->willReturn($attrData['label']);
            $attributeMock->expects($this->once())
                ->method('getAttributeCode')
                ->willReturn($attrData['code']);
            $attributeMocks[] = $attributeMock;
        }

        $collectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn($attributeMocks);
    }
}
