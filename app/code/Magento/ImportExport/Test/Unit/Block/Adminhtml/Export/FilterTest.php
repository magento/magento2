<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Unit\Block\Adminhtml\Export;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD)
 */
class FilterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Model\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $modelContext;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Api\ExtensionAttributesFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $extensionFactory;

    /**
     * @var \Magento\Framework\Api\AttributeValueFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $customAttributeFactory;

    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $eavConfig;

    /**
     * @var \Magento\Eav\Model\Entity\TypeFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $eavTypeFactory;

    /**
     * @var \Magento\Store\Model\StoreManager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Helper|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resourceHelper;

    /**
     * @var \Magento\Framework\Validator\UniversalFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $universalFactory;

    /**
     * @var \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $optionDataFactory;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $dataObjectProcessor;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $dataObjectHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $localeDate;

    /**
     * @var \Magento\Catalog\Model\Product\ReservedAttributeList|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $reservedAttributeList;

    /**
     * @var \Magento\Framework\Locale\Resolver|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $localeResolver;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resource;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $resourceCollection;

    /**
     * @var \Magento\Backend\Block\Template\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $context;

    /**
     * @var \Magento\Backend\Helper\Data|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $backendHelper;

    /**
     * @var \Magento\ImportExport\Helper\Data|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $importExportData;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\ImportExport\Block\Adminhtml\Export\Filter|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $filter;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $dateTimeFormatter;

    protected function setUp(): void
    {
        $this->modelContext = $this->createMock(\Magento\Framework\Model\Context::class);
        $this->registry = $this->createMock(\Magento\Framework\Registry::class);
        $this->extensionFactory = $this->createMock(\Magento\Framework\Api\ExtensionAttributesFactory::class);
        $this->customAttributeFactory = $this->createMock(\Magento\Framework\Api\AttributeValueFactory::class);
        $this->eavConfig = $this->createMock(\Magento\Eav\Model\Config::class);
        $this->eavTypeFactory = $this->createMock(\Magento\Eav\Model\Entity\TypeFactory::class);
        $this->storeManager = $this->createMock(\Magento\Store\Model\StoreManager::class);
        $this->resourceHelper = $this->createMock(\Magento\Eav\Model\ResourceModel\Helper::class);
        $this->universalFactory = $this->createMock(\Magento\Framework\Validator\UniversalFactory::class);
        $this->optionDataFactory = $this->createMock(\Magento\Eav\Api\Data\AttributeOptionInterfaceFactory::class);
        $this->dataObjectProcessor = $this->createMock(\Magento\Framework\Reflection\DataObjectProcessor::class);
        $this->dataObjectHelper = $this->createMock(\Magento\Framework\Api\DataObjectHelper::class);
        $this->localeDate = $this->createMock(\Magento\Framework\Stdlib\DateTime\Timezone::class);
        $this->localeDate->expects($this->any())->method('getDateFormat')->willReturn('12-12-2012');
        $this->reservedAttributeList = $this->createMock(\Magento\Catalog\Model\Product\ReservedAttributeList::class);
        $this->localeResolver = $this->createMock(\Magento\Framework\Locale\Resolver::class);
        $this->resource = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product::class);
        $this->resourceCollection = $this->getMockForAbstractClass(
            \Magento\Framework\Data\Collection\AbstractDb::class,
            [],
            '',
            false
        );
        $this->context = $this->createPartialMock(
            \Magento\Backend\Block\Template\Context::class,
            ['getFileSystem', 'getEscaper', 'getLocaleDate', 'getLayout']
        );
        $filesystem = $this->createMock(\Magento\Framework\Filesystem::class);
        $this->context->expects($this->any())->method('getFileSystem')->willReturn($filesystem);
        $escaper = $this->createPartialMock(\Magento\Framework\Escaper::class, ['escapeHtml']);
        $escaper->expects($this->any())->method('escapeHtml')->willReturn('');
        $this->context->expects($this->any())->method('getEscaper')->willReturn($escaper);
        $timeZone = $this->createMock(\Magento\Framework\Stdlib\DateTime\Timezone::class);
        $timeZone->expects($this->any())->method('getDateFormat')->willReturn('M/d/yy');
        $this->context->expects($this->any())->method('getLocaleDate')->willReturn($timeZone);
        $dateBlock = $this->createPartialMock(
            \Magento\Framework\View\Element\Html\Date::class,
            ['setValue', 'getHtml', 'setId', 'getId']
        );
        $dateBlock->expects($this->any())->method('setValue')->willReturnSelf();
        $dateBlock->expects($this->any())->method('getHtml')->willReturn('');
        $dateBlock->expects($this->any())->method('setId')->willReturnSelf();
        $dateBlock->expects($this->any())->method('getId')->willReturn(1);
        $layout = $this->createMock(\Magento\Framework\View\Layout::class);
        $layout->expects($this->any())->method('createBlock')->willReturn($dateBlock);
        $this->context->expects($this->any())->method('getLayout')->willReturn($layout);
        $this->backendHelper = $this->createMock(\Magento\Backend\Helper\Data::class);
        $this->importExportData = $this->createMock(\Magento\ImportExport\Helper\Data::class);
        $this->dateTimeFormatter = $this->createMock(
            \Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface::class
        );
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->filter = $this->objectManagerHelper->getObject(
            \Magento\ImportExport\Block\Adminhtml\Export\Filter::class,
            [
                'context' => $this->context,
                'backendHelper' => $this->backendHelper,
                'importExportData' => $this->importExportData
            ]
        );
    }

    /**
     * Test decorateFilter()
     *
     * @param array $attributeData
     * @param string $backendType
     * @param array $columnValue
     * @dataProvider decorateFilterDataProvider
     */
    public function testDecorateFilter($attributeData, $backendType, $columnValue)
    {
        $value = '';
        $attribute = new \Magento\Eav\Model\Entity\Attribute(
            $this->modelContext,
            $this->registry,
            $this->extensionFactory,
            $this->customAttributeFactory,
            $this->eavConfig,
            $this->eavTypeFactory,
            $this->storeManager,
            $this->resourceHelper,
            $this->universalFactory,
            $this->optionDataFactory,
            $this->dataObjectProcessor,
            $this->dataObjectHelper,
            $this->localeDate,
            $this->reservedAttributeList,
            $this->localeResolver,
            $this->dateTimeFormatter,
            $this->resource,
            $this->resourceCollection
        );
        $attribute->setAttributeCode($attributeData['code']);
        $attribute->setFrontendInput($attributeData['input']);
        $attribute->setOptions($attributeData['options']);
        $attribute->setFilterOptions($attributeData['filter_options']);
        $attribute->setBackendType($backendType);
        $column = new \Magento\Framework\DataObject();
        $column->setData($columnValue, 'value');
        $isExport = true;
        $result = $this->filter->decorateFilter($value, $attribute, $column, $isExport);
        $this->assertNotNull($result);
    }

    /**
     * Dataprovider for testDecorateFilter()
     *
     * @return array
     */
    public function decorateFilterDataProvider()
    {
        return [
            [
                'attributeCode' => [
                    'code' =>'updated_at',
                    'input' => '',
                    'options' => [],
                    'filter_options' => []
                ],
                'backendType' => 'datetime',
                'columnValue' => ['values' => ['updated_at' => '12/12/12']]
            ],
            [
                'attributeCode' => [
                    'code' => 'category_ids',
                    'input' => '',
                    'options' => [],
                    'filter_options' => []
                ],
                'backendType' => 'varchar',
                'columnValue' => ['values' => ['category_ids' => '1']]
            ],
            [
                'attributeCode' => [
                    'code' => 'cost',
                    'input' => '',
                    'options' => [],
                    'filter_options' => []
                ],
                'backendType' => 'decimal',
                'columnValue' => ['values' => ['cost' => 'cost']]
            ],
            [
                'attributeCode' => [
                    'code' => 'color',
                    'input' => 'select',
                    'options' => ['red' => 'red'],
                    'filter_options' => ['opt' => 'val']
                ],
                'backendType' => 'select',
                'columnValue' => ['values' => ['color' => 'red']]
            ]
        ];
    }

    /**
     * Test for protected method prepareForm()
     *
     * @todo to implement it.
     */
    public function testPrepareForm()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }
}
