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

namespace Magento\Downloadable\Test\Constraint;

use Mtf\Client\Browser;
use Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Downloadable\Test\Fixture\CatalogProductDownloadable;

/**
 * Class AssertDownloadableLinksData
 *
 * Assert that Link block for downloadable product on front-end
 */
class AssertDownloadableLinksData extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert Link block for downloadable product on front-end
     *
     * @param CatalogProductView $downloadableProductView
     * @param CatalogProductDownloadable $product
     * @param Browser $browser
     * @return void
     */
    public function processAssert(
        CatalogProductView $downloadableProductView,
        CatalogProductDownloadable $product,
        Browser $browser
    ) {
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        $linksBlock = $downloadableProductView->getDownloadableViewBlock()->getDownloadableLinksBlock();
        $fields = $product->getData();
        // Title for for Link block
        \PHPUnit_Framework_Assert::assertEquals(
            $linksBlock->getTitleForLinkBlock(),
            $fields['downloadable_links']['title'],
            'Title for for Link block for downloadable product on front-end is not correct.'
        );

        $this->sortDownloadableArray($fields['downloadable_links']['downloadable']['link']);

        foreach ($fields['downloadable_links']['downloadable']['link'] as $index => $link) {
            $index++;
            // Titles for each links
            // Links are displaying according to Sort Order
            \PHPUnit_Framework_Assert::assertEquals(
                $linksBlock->getItemTitle($index),
                $link['title'],
                'Link item ' . $index . ' with title "' . $link['title'] . '" is not visible.'
            );

            // If Links can be Purchase Separately, check-nob is presented near each link
            // If Links CANNOT be Purchase Separately, check-nob is not presented near each link
            if ($fields['downloadable_links']['links_purchased_separately'] == "Yes") {
                \PHPUnit_Framework_Assert::assertTrue(
                    $linksBlock->isVisibleItemCheckbox($index),
                    'Item ' . $index . ' link block CANNOT be Purchase Separately.'
                );
                // Price is equals passed according to fixture
                $link['price'] = sprintf('$%1.2f', $link['price']);
                \PHPUnit_Framework_Assert::assertEquals(
                    $linksBlock->getItemPrice($index),
                    $link['price'],
                    'Link item ' . $index . ' price is not visible.'
                );
            } elseif ($fields['downloadable_links']['links_purchased_separately'] == "No") {
                \PHPUnit_Framework_Assert::assertFalse(
                    $linksBlock->isVisibleItemCheckbox($index),
                    'Item ' . $index . ' link block can be Purchase Separately.'
                );
            }
        }
    }

    /**
     * Sort downloadable links array
     *
     * @param array $fields
     * @return array
     */
    protected function sortDownloadableArray(&$fields)
    {
        usort(
            $fields,
            function ($a, $b) {
                if ($a['sort_order'] == $b['sort_order']) {
                    return 0;
                }
                return ($a['sort_order'] < $b['sort_order']) ? -1 : 1;
            }
        );
    }

    /**
     * Text of Visible in downloadable assert for link block
     *
     * @return string
     */
    public function toString()
    {
        return 'Link block for downloadable product on front-end is visible.';
    }
}
