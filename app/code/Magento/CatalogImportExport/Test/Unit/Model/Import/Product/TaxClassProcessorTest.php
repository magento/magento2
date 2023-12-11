<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Test\Unit\Model\Import\Product;

use Magento\CatalogImportExport\Model\Import\Product\TaxClassProcessor;
use Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Tax\Model\ClassModel;
use Magento\Tax\Model\ResourceModel\TaxClass\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TaxClassProcessorTest extends TestCase
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
     * @var TaxClassProcessor|MockObject
     */
    protected $taxClassProcessor;

    /**
     * @var AbstractType
     */
    protected $product;

    protected function setUp(): void
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $taxClass = $this->getMockBuilder(ClassModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $taxClass->method('getClassName')->willReturn(self::TEST_TAX_CLASS_NAME);
        $taxClass->method('getId')->willReturn(self::TEST_TAX_CLASS_ID);

        $taxClassCollection =
            $this->objectManagerHelper->getCollectionMock(
                Collection::class,
                [$taxClass]
            );

        $taxClassCollectionFactory = $this->createPartialMock(
            \Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory::class,
            ['create']
        );

        $taxClassCollectionFactory->method('create')->willReturn($taxClassCollection);

        $anotherTaxClass = $this->getMockBuilder(ClassModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $anotherTaxClass->method('getClassName')->willReturn(self::TEST_TAX_CLASS_NAME);
        $anotherTaxClass->method('getId')->willReturn(self::TEST_JUST_CREATED_TAX_CLASS_ID);

        $taxClassFactory = $this->createPartialMock(\Magento\Tax\Model\ClassModelFactory::class, ['create']);

        $taxClassFactory->method('create')->willReturn($anotherTaxClass);

        $this->taxClassProcessor =
            new TaxClassProcessor(
                $taxClassCollectionFactory,
                $taxClassFactory
            );

        $this->product =
            $this->getMockForAbstractClass(
                AbstractType::class,
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

    public function testUpsertTaxClassExistCaseInsensitive()
    {
        $taxClassId = $this->taxClassProcessor->upsertTaxClass(strtoupper(self::TEST_TAX_CLASS_NAME), $this->product);
        $this->assertEquals(self::TEST_TAX_CLASS_ID, $taxClassId);
    }

    public function testUpsertTaxClassNone()
    {
        $taxClassId = $this->taxClassProcessor->upsertTaxClass('none', $this->product);
        $this->assertEquals(0, $taxClassId);
    }

    public function testUpsertTaxClassZero()
    {
        $taxClassId = $this->taxClassProcessor->upsertTaxClass(0, $this->product);
        $this->assertEquals(0, $taxClassId);
    }
}
