<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Setup\AttributeConfiguration;

use Magento\Catalog\Setup\AttributeConfiguration\CatalogConfiguration;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class CatalogConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CatalogConfiguration|\PHPUnit_Framework_MockObject_MockObject
     */
    private $builder;

    protected function setUp()
    {
        $this->builder = (new ObjectManager($this))->getObject(CatalogConfiguration::class);
    }

    public function testBuilderReturnsACompatibleArrayAndChangingStateReturnsANewInstance()
    {
        $builder = $this->builder;

        foreach ($this->getMethodsThatChangeState() as $methodInfo) {
            $this->builder = call_user_func_array([$this->builder, $methodInfo[0]], $methodInfo[1]);
            $this->assertNotSame($builder, $this->builder);
        }

        $this->assertEquals(
            [
                'apply_to' => 'simple,custom',
                'comparable' => true,
                'filterable' => true,
                'is_filterable_in_grid' => true,
                'filterable_in_search' => true,
                'searchable' => true,
                'used_for_promo_rules' => true,
                'used_for_sort_by' => true,
                'is_used_in_grid' => true,
                'used_in_product_listing' => true,
                'visible' => true,
                'visible_in_advanced_search' => true,
                'is_visible_in_grid' => false,
                'visible_on_front' => true,
                'input_renderer' => 'FrontendInputRendererClass',
                'is_html_allowed_on_front' => true,
                'position' => 3,
                'wysiwyg_enabled' => true,
            ],
            $this->builder->toArray()
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testBuilderThrowsOnNonIntegerPosition()
    {
        $this->builder->withPosition('3');
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testBuilderThrowsOnEmptyApplyTo()
    {
        $this->builder->applyingTo([]);
    }

    public function getMethodsThatChangeState()
    {
        return [
            ['applyingTo', [['simple', 'custom']]],
            ['comparable', []],
            ['filterable', []],
            ['filterableInGrid', []],
            ['filterableInSearch', []],
            ['searchable', []],
            ['usedForPromoRules', []],
            ['usedForSortBy', []],
            ['usedInGrid', []],
            ['usedInProductListing', []],
            ['usedInProductListing', []],
            ['visible', []],
            ['visibleInAdvancedSearch', []],
            ['visibleInGrid', [false]],
            ['visibleOnFront', []],
            ['withFrontendInputRenderer', ['FrontendInputRendererClass']],
            ['withHtmlAllowedOnFrontend', []],
            ['withPosition', [3]],
            ['wysiwygEnabled', []],
        ];
    }
}
