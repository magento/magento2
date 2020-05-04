<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Model\Search;

use Magento\Catalog\Model\Product;
use Magento\CatalogSearch\Model\Search\CustomAttributeFilterCheck;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Search\Request\Filter\Term;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomAttributeFilterCheckTest extends TestCase
{
    /** @var Config|MockObject */
    private $eavConfig;

    /** @var CustomAttributeFilterCheck */
    private $customAttributeFilterCheck;

    protected function setUp(): void
    {
        $this->eavConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttribute'])
            ->getMock();

        $objectManagerHelper = new ObjectManager($this);
        $this->customAttributeFilterCheck = $objectManagerHelper->getObject(
            CustomAttributeFilterCheck::class,
            [
                'eavConfig' => $this->eavConfig
            ]
        );
    }

    /**
     * @param $attributeFrontEndType
     * @dataProvider dataProviderForIsCustomPositive
     */
    public function testIsCustomPositive($attributeFrontEndType)
    {
        $filterField = 'someField';

        $filter = $this->getMockBuilder(Term::class)
            ->disableOriginalConstructor()
            ->setMethods(['getType', 'getField'])
            ->getMock();

        $filter
            ->method('getField')
            ->willReturn($filterField);

        $filter
            ->method('getType')
            ->willReturn(FilterInterface::TYPE_TERM);

        $attribute = $this->getMockBuilder(AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFrontendInput'])
            ->getMockForAbstractClass();

        $attribute
            ->method('getFrontendInput')
            ->willReturn($attributeFrontEndType);

        $this->eavConfig
            ->method('getAttribute')
            ->with(Product::ENTITY, $filterField)
            ->willReturn($attribute);

        $this->assertTrue(
            $this->customAttributeFilterCheck->isCustom($filter),
            'Filter must be custom!'
        );
    }

    /**
     * @return array
     */
    public function dataProviderForIsCustomPositive()
    {
        return [
            ['select'],
            ['multiselect']
        ];
    }

    public function testIsCustomNegative1()
    {
        $filterField = 'someField';

        $filter = $this->getMockBuilder(Term::class)
            ->disableOriginalConstructor()
            ->setMethods(['getType', 'getField'])
            ->getMock();

        $filter
            ->method('getField')
            ->willReturn($filterField);

        $filter
            ->method('getType')
            ->willReturn(FilterInterface::TYPE_TERM);

        $this->eavConfig
            ->method('getAttribute')
            ->with(Product::ENTITY, $filterField)
            ->willReturn(null);

        $this->assertFalse(
            $this->customAttributeFilterCheck->isCustom($filter),
            'Filter must not be custom because attribute is null!'
        );
    }

    public function testIsCustomNegative2()
    {
        $filterField = 'someField';

        $filter = $this->getMockBuilder(Term::class)
            ->disableOriginalConstructor()
            ->setMethods(['getType', 'getField'])
            ->getMock();

        $filter
            ->method('getField')
            ->willReturn($filterField);

        $filter
            ->method('getType')
            ->willReturn(FilterInterface::TYPE_BOOL);

        $attribute = $this->getMockBuilder(AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFrontendInput'])
            ->getMockForAbstractClass();

        $attribute
            ->method('getFrontendInput')
            ->willReturn('select');

        $this->eavConfig
            ->method('getAttribute')
            ->with(Product::ENTITY, $filterField)
            ->willReturn($attribute);

        $this->assertFalse(
            $this->customAttributeFilterCheck->isCustom($filter),
            'Filter must not be custom because filter type is not termFilter!'
        );
    }

    public function testIsCustomNegative3()
    {
        $filterField = 'someField';

        $filter = $this->getMockBuilder(Term::class)
            ->disableOriginalConstructor()
            ->setMethods(['getType', 'getField'])
            ->getMock();

        $filter
            ->method('getField')
            ->willReturn($filterField);

        $filter
            ->method('getType')
            ->willReturn(FilterInterface::TYPE_TERM);

        $attribute = $this->getMockBuilder(AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFrontendInput'])
            ->getMockForAbstractClass();

        $attribute
            ->method('getFrontendInput')
            ->willReturn('any-random-type');

        $this->eavConfig
            ->method('getAttribute')
            ->with(Product::ENTITY, $filterField)
            ->willReturn($attribute);

        $this->assertFalse(
            $this->customAttributeFilterCheck->isCustom($filter),
            'Filter must not be custom because attribute frontend type is not select or multiselect!'
        );
    }
}
