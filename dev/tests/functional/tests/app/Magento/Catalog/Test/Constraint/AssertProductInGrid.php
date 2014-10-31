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

namespace Magento\Catalog\Test\Constraint;

use Mtf\Fixture\FixtureInterface;
use Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;

/**
 * Class AssertProductInGrid
 * Assert that product is present in products grid.
 */
class AssertProductInGrid extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'high';

    /**
     * Product fixture
     *
     * @var FixtureInterface $product
     */
    protected $product;

    /**
     * Assert that product is present in products grid and can be found by sku, type, status and attribute set.
     *
     * @param FixtureInterface $product
     * @param CatalogProductIndex $productGrid
     * @return void
     */
    public function processAssert(FixtureInterface $product, CatalogProductIndex $productGrid)
    {
        $this->product = $product;
        $productGrid->open();
        \PHPUnit_Framework_Assert::assertTrue(
            $productGrid->getProductGrid()->isRowVisible($this->prepareFilter()),
            'Product \'' . $this->product->getName() . '\' is absent in Products grid.'
        );
    }

    /**
     * Prepare filter for product grid.
     *
     * @return array
     */
    protected function prepareFilter()
    {
        $productStatus = ($this->product->getStatus() === null || $this->product->getStatus() === 'Product online')
            ? 'Enabled'
            : 'Disabled';
        $filter = [
            'type' => $this->getProductType(),
            'sku' => $this->product->getSku(),
            'status' => $productStatus,
        ];
        if ($this->product->hasData('attribute_set_id')) {
            $filter['set_name'] = $this->product->getAttributeSetId();
        }

        return $filter;
    }

    /**
     * Get product type
     *
     * @return string
     */
    protected function getProductType()
    {
        $config = $this->product->getDataConfig();

        return ucfirst($config['type_id']) . ' Product';
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Product is present in products grid.';
    }
}
