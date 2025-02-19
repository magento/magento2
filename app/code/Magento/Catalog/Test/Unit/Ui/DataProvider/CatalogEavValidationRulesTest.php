<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Ui\DataProvider;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Ui\DataProvider\CatalogEavValidationRules;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CatalogEavValidationRulesTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var CatalogEavValidationRules
     */
    protected $catalogEavValidationRules;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->catalogEavValidationRules = $this->objectManagerHelper->getObject(CatalogEavValidationRules::class);
    }

    /**
     * @param $frontendInput
     * @param $frontendClass
     * @param array $eavConfig
     * @param array $expectedResult
     * @return void
     * @dataProvider buildDataProvider
     */
    public function testBuild($frontendInput, $frontendClass, array $eavConfig, array $expectedResult)
    {
        /** @var ProductAttributeInterface|MockObject $attribute */
        $attribute = $this->getMockForAbstractClass(ProductAttributeInterface::class);

        $attribute->expects($this->once())
            ->method('getFrontendInput')
            ->willReturn($frontendInput);
        $attribute->method('getFrontendClass')
            ->willReturn($frontendClass);

        $this->assertEquals($expectedResult, $this->catalogEavValidationRules->build($attribute, $eavConfig));
    }

    /**
     * @return array
     */
    public static function buildDataProvider()
    {
        $data['required'] = true;

        return [
            [
                'frontendInput' => 'input',
                'frontendClass' => '',
                'eavConfig' => [],
                'expectedResult' => [],
            ],
            [
                'frontendInput' => 'price',
                'frontendClass' => '',
                'eavConfig' => $data,
                'expectedResult' => [
                    'required-entry' => true,
                    'validate-zero-or-greater' => true,
                ],
            ],
            [
                'frontendInput' => 'input',
                'frontendClass' => 'maximum-length-20 minimum-length-10 validate-number validate-digits'
                    . ' validate-email validate-url validate-alpha validate-alphanum',
                'eavConfig' => [],
                'expectedResult' => [
                    'max_text_length' => 20,
                    'min_text_length' => 10,
                    'validate-number' => true,
                    'validate-digits' => true,
                    'validate-email' => true,
                    'validate-url' => true,
                    'validate-alpha' => true,
                    'validate-alphanum' => true,
                ],
            ],
        ];
    }
}
