<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Test\TestStep;

use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Mtf\Fixture\FixtureFactory;
use Mtf\TestStep\TestStepInterface;

/**
 * Set default attribute value.
 */
class SetDefaultAttributeValueStep implements TestStepInterface
{
    /**
     * CatalogProductAttribute fixture.
     *
     * @var CatalogProductAttribute
     */
    protected $attribute;

    /**
     * FixtureFactory object.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Catalog product index page.
     *
     * @var CatalogProductEdit
     */
    protected $catalogProductEdit;

    /**
     * @constructor
     * @param CatalogProductAttribute $attribute
     * @param CatalogProductEdit $catalogProductEdit
     * @param FixtureFactory $fixtureFactory
     */
    public function __construct(
        CatalogProductAttribute $attribute,
        CatalogProductEdit $catalogProductEdit,
        FixtureFactory $fixtureFactory
    ) {
        $this->attribute = $attribute;
        $this->catalogProductEdit = $catalogProductEdit;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Set Default Attribute Value.
     *
     * @return void
     */
    public function run()
    {
        $product = $this->fixtureFactory->createByCode(
            'catalogProductSimple',
            ['data' => ['custom_attribute' => $this->attribute]]
        );
        $this->catalogProductEdit->getProductForm()->fill($product);
    }
}
