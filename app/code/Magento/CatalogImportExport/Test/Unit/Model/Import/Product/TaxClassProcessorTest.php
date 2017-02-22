<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Test\Unit\Model\Import\Product;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\CatalogImportExport\Model\Import\Product\Validator;

class TaxClassProcessorTest extends \PHPUnit_Framework_TestCase
{
    const TEST_TAX_CLASS_NAME = 'className';

    const TEST_TAX_CLASS_ID = 1;

    const TEST_JUST_CREATED_TAX_CLASS_ID = 2;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product\TaxClassProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $taxClassProcessor;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     */
    protected $product;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $taxClass = $this->getMockBuilder('Magento\Tax\Model\ClassModel')
            ->disableOriginalConstructor()
            ->getMock();
        $taxClass->method('getClassName')->will($this->returnValue(self::TEST_TAX_CLASS_NAME));
        $taxClass->method('getId')->will($this->returnValue(self::TEST_TAX_CLASS_ID));

        $taxClassCollection =
            $this->objectManagerHelper->getCollectionMock(
                'Magento\Tax\Model\ResourceModel\TaxClass\Collection',
                [$taxClass]
            );

        $taxClassCollectionFactory = $this->getMock(
            'Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );

        $taxClassCollectionFactory->method('create')->will($this->returnValue($taxClassCollection));

        $anotherTaxClass = $this->getMockBuilder('Magento\Tax\Model\ClassModel')
            ->disableOriginalConstructor()
            ->getMock();
        $anotherTaxClass->method('getClassName')->will($this->returnValue(self::TEST_TAX_CLASS_NAME));
        $anotherTaxClass->method('getId')->will($this->returnValue(self::TEST_JUST_CREATED_TAX_CLASS_ID));

        $taxClassFactory = $this->getMock(
            'Magento\Tax\Model\ClassModelFactory',
            ['create'],
            [],
            '',
            false
        );

        $taxClassFactory->method('create')->will($this->returnValue($anotherTaxClass));

        $this->taxClassProcessor =
            new \Magento\CatalogImportExport\Model\Import\Product\TaxClassProcessor(
                $taxClassCollectionFactory,
                $taxClassFactory
            );

        $this->product =
            $this->getMockForAbstractClass(
                'Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType',
                [],
                '',
                false
            );
    }

    public function testUpsertTaxClassExist()
    {
        $taxClassId = $this->taxClassProcessor->upsertTaxClass(self::TEST_TAX_CLASS_NAME, $this->product);
        $this->assertEquals(self::TEST_TAX_CLASS_ID, $taxClassId);
    }

    public function testUpsertTaxClassNotExist()
    {
        $taxClassId = $this->taxClassProcessor->upsertTaxClass('noExistClassName', $this->product);
        $this->assertEquals(self::TEST_JUST_CREATED_TAX_CLASS_ID, $taxClassId);
    }
}
