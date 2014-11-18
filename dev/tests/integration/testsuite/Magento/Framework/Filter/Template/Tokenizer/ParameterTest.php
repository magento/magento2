<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Filter\Template\Tokenizer;

class ParameterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $string
     * @param array $values
     * @dataProvider getValueDataProvider
     */
    public function testGetValue($string, $values)
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Framework\Filter\Template\Tokenizer\Parameter $parameter */
        $parameter = $objectManager->create('Magento\Framework\Filter\Template\Tokenizer\Parameter');
        $parameter->setString($string);

        foreach ($values as $value) {
            $this->assertEquals($value, $parameter->getValue());
        }
    }

    /**
     * @dataProvider tokenizeDataProvider
     * @param string $string
     * @param array $params
     */
    public function testTokenize($string, $params)
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Framework\Filter\Template\Tokenizer\Parameter $parameter */
        $parameter = $objectManager->create('Magento\Framework\Filter\Template\Tokenizer\Parameter');
        $parameter->setString($string);
        $this->assertEquals($params, $parameter->tokenize());
    }

    /**
     * @return array
     */
    public function tokenizeDataProvider()
    {
        return [
            [
                ' type="Magento\\Catalog\\Block\\Product\\Widget\\NewWidget" display_type="all_products"'
                . ' products_count="10" template="product/widget/new/content/new_grid.phtml"',
                [
                    'type' => 'Magento\Catalog\Block\Product\Widget\NewWidget',
                    'display_type' => 'all_products',
                    'products_count' => 10,
                    'template' => 'product/widget/new/content/new_grid.phtml'
                ]
            ],
            [
                ' type="Magento\Catalog\Block\Product\Widget\NewWidget" display_type="all_products"'
                . ' products_count="10" template="product/widget/new/content/new_grid.phtml"',
                [
                    'type' => 'Magento\Catalog\Block\Product\Widget\NewWidget',
                    'display_type' => 'all_products',
                    'products_count' => 10,
                    'template' => 'product/widget/new/content/new_grid.phtml'
                ]
            ]
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
                ]
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