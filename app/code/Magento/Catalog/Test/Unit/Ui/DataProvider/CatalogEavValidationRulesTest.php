<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Ui\DataProvider;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Catalog\Ui\DataProvider\CatalogEavValidationRules;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class CatalogEavValidationRulesTest extends \PHPUnit_Framework_TestCase
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
    protected function setUp()
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
        /** @var \Magento\Catalog\Api\Data\ProductAttributeInterface|MockObject $attribute */
        $attribute = $this->getMock(\Magento\Catalog\Api\Data\ProductAttributeInterface::class);

        $attribute->expects($this->once())
            ->method('getFrontendInput')
            ->willReturn($frontendInput);
        $attribute->expects($this->once())
            ->method('getFrontendClass')
            ->willReturn($frontendClass);

        $this->assertEquals($expectedResult, $this->catalogEavValidationRules->build($attribute, $eavConfig));
    }

    public function buildDataProvider()
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
