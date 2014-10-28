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
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;

/**
 * Class AssertProductDuplicateForm
 */
class AssertProductDuplicateForm extends AssertProductForm
{
    /**
     * Formatting options for numeric values
     *
     * @var array
     */
    protected $formattingOptions = [
        'price' => [
            'decimals' => 2,
            'dec_point' => '.',
            'thousands_sep' => ''
        ],
        'qty' => [
            'decimals' => 4,
            'dec_point' => '.',
            'thousands_sep' => ''
        ],
        'weight' => [
            'decimals' => 4,
            'dec_point' => '.',
            'thousands_sep' => ''
        ]
    ];

    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert form data equals fixture data
     *
     * @param FixtureInterface $product
     * @param CatalogProductIndex $productGrid
     * @param CatalogProductEdit $productPage
     * @return void
     */
    public function processAssert(
        FixtureInterface $product,
        CatalogProductIndex $productGrid,
        CatalogProductEdit $productPage
    ) {
        $filter = ['sku' => $product->getSku() . '-1'];
        $productGrid->open()->getProductGrid()->searchAndOpen($filter);

        $formData = $productPage->getProductForm()->getData($product);
        $fixtureData = $this->prepareFixtureData($product->getData());

        $errors = $this->verifyData($fixtureData, $formData);
        \PHPUnit_Framework_Assert::assertEmpty($errors, $errors);
    }

    /**
     * Prepares fixture data for comparison
     *
     * @param array $data
     * @param array $sortFields [optional]
     * @return array
     */
    protected function prepareFixtureData(array $data, array $sortFields = [])
    {
        $compareData = array_filter($data);

        array_walk_recursive(
            $compareData,
            function (&$item, $key, $formattingOptions) {
                if (isset($formattingOptions[$key])) {
                    $item = number_format(
                        $item,
                        $formattingOptions[$key]['decimals'],
                        $formattingOptions[$key]['dec_point'],
                        $formattingOptions[$key]['thousands_sep']
                    );
                }
            },
            $this->formattingOptions
        );

        if (isset($compareData['status'])) {
            $compareData['status'] = 'Product offline';
        }
        if (isset($compareData['quantity_and_stock_status']['qty'])) {
            $compareData['quantity_and_stock_status']['qty'] = 0;
        }
        if (isset($compareData['special_price'])) {
            $compareData['special_price'] = ['special_price' => $compareData['special_price']];
        }
        $compareData['sku'] .= '-1';

        return parent::prepareFixtureData($compareData, $sortFields);
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Form data equals to fixture data of duplicated product.';
    }
}
