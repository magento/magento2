<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Unit\Block\Adminhtml\Export;

use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class Filter extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Model\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $modelContext;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Api\ExtensionAttributesFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $extensionFactory;

    /**
     * @var \Magento\Framework\Api\AttributeValueFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customAttributeFactory;

    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavConfig;

    /**
     * @var \Magento\Eav\Model\Entity\TypeFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavTypeFactory;

    /**
     * @var \Magento\Store\Model\StoreManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\Eav\Model\Resource\Helper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceHelper;

    /**
     * @var \Magento\Framework\Validator\UniversalFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $universalFactory;

    /**
     * @var \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionDataFactory;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObjectProcessor;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObjectHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeDate;

    /**
     * @var \Magento\Catalog\Model\Product\ReservedAttributeList|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $reservedAttributeList;

    /**
     * @var \Magento\Framework\Locale\Resolver|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeResolver;

    /**
     * @var \Magento\Catalog\Model\Resource\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var \Magento\Framework\Data\Collection\Db|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceCollection;

    /**
     * @var \Magento\Backend\Block\Template\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Backend\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $backendHelper;

    /**
     * @var \Magento\ImportExport\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $importExportData;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\ImportExport\Block\Adminhtml\Export\Filter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filter;

    public function setUp()
    {
        $this->modelContext = $this->getMock('Magento\Framework\Model\Context', ['_construct'], [], '', false);
        //$this->modelContext->expects($this->any())->method('_construct')->will($this->returnValue(true));
        $this->registry = $this->getMock('Magento\Framework\Registry', [], [], '', false);
        $this->extensionFactory = $this->getMock(
            'Magento\Framework\Api\ExtensionAttributesFactory',
            [],
            [],
            '',
            false
        );
        $this->customAttributeFactory = $this->getMock(
            'Magento\Framework\Api\AttributeValueFactory',
            [],
            [],
            '',
            false);
        $this->eavConfig = $this->getMock('Magento\Eav\Model\Config', [], [], '', false);
        $this->eavTypeFactory = $this->getMock('Magento\Eav\Model\Entity\TypeFactory', [], [], '', false);
        $this->storeManager = $this->getMock('Magento\Store\Model\StoreManager', [], [], '', false);
        $this->resourceHelper = $this->getMock('Magento\Eav\Model\Resource\Helper', [], [], '', false);
        $this->universalFactory = $this->getMock('Magento\Framework\Validator\UniversalFactory', [], [], '', false);
        $this->optionDataFactory = $this->getMock(
            'Magento\Eav\Api\Data\AttributeOptionInterfaceFactory',
            [],
            [],
            '',
            false
        );
        $this->dataObjectProcessor = $this->getMock(
            'Magento\Framework\Reflection\DataObjectProcessor',
            [],
            [],
            '',
            false
        );
        $this->dataObjectHelper = $this->getMock('Magento\Framework\Api\DataObjectHelper', [], [], '', false);
        $this->localeDate = $this->getMock('Magento\Framework\Stdlib\DateTime\Timezone', [], [], '', false);
        $this->localeDate->expects($this->any())->method('getDateFormat')->will($this->returnValue('12-12-2012'));

        $this->reservedAttributeList = $this->getMock(
            'Magento\Catalog\Model\Product\ReservedAttributeList',
            [],
            [],
            '',
            false
        );
        $this->localeResolver = $this->getMock('Magento\Framework\Locale\Resolver', [], [], '', false);
        $this->resource = $this->getMock('Magento\Catalog\Model\Resource\Product', [], [], '', false);
        $this->resourceCollection = $this->getMock('Magento\Framework\Data\Collection\Db', [], [], '', false);

        $this->context = $this->getMock(
            'Magento\Backend\Block\Template\Context',
            ['getFileSystem', 'getEscaper', 'getLocaleDate'],
            [],
            '',
            false
        );
        $filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->context->expects($this->any())->method('getFileSystem')->will($this->returnValue($filesystem));
        $escaper = $this->getMock('Magento\Framework\Escaper', ['escapeHtml'], [], '', false);
        $escaper->expects($this->any())->method('escapeHtml')->will($this->returnValue(''));
        $this->context->expects($this->any())->method('getEscaper')->will($this->returnValue($escaper));
        $timeZone = $this->getMock('Magento\Framework\Stdlib\DateTime\TimeZone', [], [], '', false);
        $timeZone->expects($this->any())->method('getDateFormat')->will($this->returnValue('M/d/yy'));
        $this->context->expects($this->any())->method('getLocaleDate')->will($this->returnValue($timeZone));
        $this->backendHelper = $this->getMock('Magento\Backend\Helper\Data', [], [], '', false);
        $this->importExportData = $this->getMock('Magento\ImportExport\Helper\Data', [], [], '', false);
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->filter = $this->objectManagerHelper->getObject(
            'Magento\ImportExport\Block\Adminhtml\Export\Filter',
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
     * @param string $attributeCode
     * @param array  $columnValue
     * @dataProvider decorateFilterDataProvider
     */
    public function testDecorateFilter($attributeCode, $backendType, $columnValue)
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
            $this->resource,
            $this->resourceCollection
        );

        $attribute->setAttributeCode($attributeCode);
        $attribute->setBackendType($backendType);
        $column = new \Magento\Framework\Object();
        $column->setData($columnValue, 'value');
        $isExport = true;

        $this->filter->decorateFilter($value, $attribute, $column, $isExport);
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
                'attributeCode' => 'custom_design_from',
                'backendType' => 'datetime',
                'columnValue' => ['values' => ['custom_design_from' => 'custom_design_from']]
            ],
            [
                'attributeCode' => 'category_ids',
                'backendType' => 'varchar',
                'columnValue' => ['values' => ['category_ids' => '1']]
            ],
            [
                'attributeCode' => 'cost',
                'backendType' => 'decimal',
                'columnValue' => ['values' => ['cost' => 'cost']]
            ],
            [
                'attributeCode' => 'color',
                'backendType' => 'text',
                'columnValue' => ['values' => ['color' => 'red']]
            ]
        ];
    }

    /**
     * Test for protected method prepareForm()
     *
     * @todo to implement it.
     */
    public function test_prepareForm()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }
}
