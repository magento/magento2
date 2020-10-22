<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Filter\Template\Tokenizer;

use Magento\Catalog\Block\Product\Widget\NewWidget;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Framework\Filter\Template\Tokenizer\Parameter.
 */
class ParameterTest extends TestCase
{
    /**
     * Test for getValue
     *
     * @dataProvider getValueDataProvider
     *
     * @param string $string
     * @param array $values
     * @return void
     */
    public function testGetValue($string, $values): void
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var Parameter $parameter */
        $parameter = $objectManager->create(Parameter::class);
        $parameter->setString($string);

        foreach ($values as $value) {
            $this->assertEquals($value, $parameter->getValue());
        }
    }

    /**
     * Test for tokenize
     *
     * @dataProvider tokenizeDataProvider
     *
     * @param string $string
     * @param array $params
     * @return void
     */
    public function testTokenize(string $string, array $params): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $parameter = $objectManager->create(Parameter::class);
        $parameter->setString($string);

        $this->assertEquals($params, $parameter->tokenize());
    }

    /**
     * DataProvider for testTokenize
     *
     * @return array
     */
    public function tokenizeDataProvider(): array
    {
        return [
            [
                ' type="Magento\\Catalog\\Block\\Product\\Widget\\NewWidget" display_type="all_products"'
                . ' products_count="10" template="product/widget/new/content/new_grid.phtml"',
                [
                    'type' => NewWidget::class,
                    'display_type' => 'all_products',
                    'products_count' => 10,
                    'template' => 'product/widget/new/content/new_grid.phtml'
                ],
            ],
            [
                ' type="Magento\Catalog\Block\Product\Widget\NewWidget" display_type="all_products"'
                . ' products_count="10" template="product/widget/new/content/new_grid.phtml"',
                [
                    'type' => NewWidget::class,
                    'display_type' => 'all_products',
                    'products_count' => 10,
                    'template' => 'product/widget/new/content/new_grid.phtml'
                ]
            ],
            [
                sprintf(
                    'type="%s" display_type="all_products" products_count="1" template="content/new_grid.phtml"',
                    NewWidget::class
                ),
                [
                    'type' => NewWidget::class,
                    'display_type' => 'all_products',
                    'products_count' => 1,
                    'template' => 'content/new_grid.phtml'
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function getValueDataProvider()
    {
        return [
            [
                ' type="Magento\\Catalog\\Block\\Product\\Widget\\NewWidget" display_type="all_products"'
                . ' products_count="10" template="product/widget/new/content/new_grid.phtml"',
                [
                    'type="Magento\Catalog\Block\Product\Widget\NewWidget"',
                    'display_type="all_products"',
                    'products_count="10"'
                ],
            ],
            [
                ' type="Magento\Catalog\Block\Product\Widget\NewWidget" display_type="all_products"'
                . ' products_count="10" template="product/widget/new/content/new_grid.phtml"',
                [
                    'type="Magento\Catalog\Block\Product\Widget\NewWidget"',
                    'display_type="all_products"',
                    'products_count="10"'
                ]
            ]
        ];
    }
}
