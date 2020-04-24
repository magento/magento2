<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Test\Unit\Block\Adminhtml\Export;

use Magento\Backend\Helper\Data;
use Magento\Catalog\Model\Product\ReservedAttributeList;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Eav\Api\Data\AttributeOptionInterfaceFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\TypeFactory;
use Magento\Eav\Model\ResourceModel\Helper;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\Filesystem;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\Model\Context;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Validator\UniversalFactory;
use Magento\Framework\View\Element\Html\Date;
use Magento\Framework\View\Layout;
use Magento\ImportExport\Block\Adminhtml\Export\Filter;
use Magento\Store\Model\StoreManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD)
 */
class FilterTest extends TestCase
{
    /**
     * @var Context|MockObject
     */
    protected $modelContext;

    /**
     * @var Registry|MockObject
     */
    protected $registry;

    /**
     * @var ExtensionAttributesFactory|MockObject
     */
    protected $extensionFactory;

    /**
     * @var AttributeValueFactory|MockObject
     */
    protected $customAttributeFactory;

    /**
     * @var Config|MockObject
     */
    protected $eavConfig;

    /**
     * @var TypeFactory|MockObject
     */
    protected $eavTypeFactory;

    /**
     * @var StoreManager|MockObject
     */
    protected $storeManager;

    /**
     * @var Helper|MockObject
     */
    protected $resourceHelper;

    /**
     * @var UniversalFactory|MockObject
     */
    protected $universalFactory;

    /**
     * @var AttributeOptionInterfaceFactory|MockObject
     */
    protected $optionDataFactory;

    /**
     * @var DataObjectProcessor|MockObject
     */
    protected $dataObjectProcessor;

    /**
     * @var DataObjectHelper|MockObject
     */
    protected $dataObjectHelper;

    /**
     * @var Timezone|MockObject
     */
    protected $localeDate;

    /**
     * @var ReservedAttributeList|MockObject
     */
    protected $reservedAttributeList;

    /**
     * @var Resolver|MockObject
     */
    protected $localeResolver;

    /**
     * @var Product|MockObject
     */
    protected $resource;

    /**
     * @var MockObject
     */
    protected $resourceCollection;

    /**
     * @var \Magento\Backend\Block\Template\Context|MockObject
     */
    protected $context;

    /**
     * @var Data|MockObject
     */
    protected $backendHelper;

    /**
     * @var \Magento\ImportExport\Helper\Data|MockObject
     */
    protected $importExportData;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var Filter|MockObject
     */
    protected $filter;

    /**
     * @var DateTimeFormatterInterface|MockObject
     */
    private $dateTimeFormatter;

    protected function setUp(): void
    {
        $this->modelContext = $this->createMock(Context::class);
        $this->registry = $this->createMock(Registry::class);
        $this->extensionFactory = $this->createMock(ExtensionAttributesFactory::class);
        $this->customAttributeFactory = $this->createMock(AttributeValueFactory::class);
        $this->eavConfig = $this->createMock(Config::class);
        $this->eavTypeFactory = $this->createMock(TypeFactory::class);
        $this->storeManager = $this->createMock(StoreManager::class);
        $this->resourceHelper = $this->createMock(Helper::class);
        $this->universalFactory = $this->createMock(UniversalFactory::class);
        $this->optionDataFactory = $this->createMock(AttributeOptionInterfaceFactory::class);
        $this->dataObjectProcessor = $this->createMock(DataObjectProcessor::class);
        $this->dataObjectHelper = $this->createMock(DataObjectHelper::class);
        $this->localeDate = $this->createMock(Timezone::class);
        $this->localeDate->expects($this->any())->method('getDateFormat')->willReturn('12-12-2012');
        $this->reservedAttributeList = $this->createMock(ReservedAttributeList::class);
        $this->localeResolver = $this->createMock(Resolver::class);
        $this->resource = $this->createMock(Product::class);
        $this->resourceCollection = $this->getMockForAbstractClass(
            AbstractDb::class,
            [],
            '',
            false
        );
        $this->context = $this->getMockBuilder(\Magento\Backend\Block\Template\Context::class)
            ->onlyMethods(['getFileSystem', 'getEscaper', 'getLocaleDate', 'getLayout'])
            ->disableOriginalConstructor()
            ->getMock();
        $filesystem = $this->createMock(Filesystem::class);
        $this->context->expects($this->any())->method('getFileSystem')->willReturn($filesystem);
        $escaper = $this->createPartialMock(Escaper::class, ['escapeHtml']);
        $escaper->expects($this->any())->method('escapeHtml')->willReturn('');
        $this->context->expects($this->any())->method('getEscaper')->willReturn($escaper);
        $timeZone = $this->createMock(Timezone::class);
        $timeZone->expects($this->any())->method('getDateFormat')->willReturn('M/d/yy');
        $this->context->expects($this->any())->method('getLocaleDate')->willReturn($timeZone);
        $dateBlock = $this->getMockBuilder(Date::class)
            ->addMethods(['setValue', 'setId', 'getId'])
            ->onlyMethods(['getHtml'])
            ->disableOriginalConstructor()
            ->getMock();
        $dateBlock->expects($this->any())->method('setValue')->willReturnSelf();
        $dateBlock->expects($this->any())->method('getHtml')->willReturn('');
        $dateBlock->expects($this->any())->method('setId')->willReturnSelf();
        $dateBlock->expects($this->any())->method('getId')->willReturn(1);
        $layout = $this->createMock(Layout::class);
        $layout->expects($this->any())->method('createBlock')->willReturn($dateBlock);
        $this->context->expects($this->any())->method('getLayout')->willReturn($layout);
        $this->backendHelper = $this->createMock(Data::class);
        $this->importExportData = $this->createMock(\Magento\ImportExport\Helper\Data::class);
        $this->dateTimeFormatter = $this->createMock(
            DateTimeFormatterInterface::class
        );
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->filter = $this->objectManagerHelper->getObject(
            Filter::class,
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
        $attribute = new Attribute(
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
        $column = new DataObject();
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
