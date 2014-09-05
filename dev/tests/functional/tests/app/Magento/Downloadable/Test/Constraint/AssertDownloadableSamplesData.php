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
 * Class AssertDownloadableSamplesData
 *
 * Assert that Sample block for downloadable product on front-end
 */
class AssertDownloadableSamplesData extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert Sample block for downloadable product on front-end
     *
     * @param CatalogProductView $productView
     * @param CatalogProductDownloadable $product
     * @param Browser $browser
     * @return void
     */
    public function processAssert(
        CatalogProductView $productView,
        CatalogProductDownloadable $product,
        Browser $browser
    ) {
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        $sampleBlock = $productView->getDownloadableViewBlock()->getDownloadableSamplesBlock();
        $fields = $product->getData();

        // Title for for sample block
        \PHPUnit_Framework_Assert::assertEquals(
            $sampleBlock->getTitleForSampleBlock(),
            $fields['downloadable_sample']['title'],
            'Title for for Samples block for downloadable product on front-end is not correct.'
        );

        $this->sortDownloadableArray($fields['downloadable_sample']['downloadable']['sample']);

        foreach ($fields['downloadable_sample']['downloadable']['sample'] as $index => $sample) {
            // Titles for each sample
            // Samples are displaying according to Sort Order
            \PHPUnit_Framework_Assert::assertEquals(
                $sampleBlock->getItemTitle(++$index),
                $sample['title'],
                'Sample item ' . $index . ' with title "' . $sample['title'] . '" is not visible.'
            );
        }
    }

    /**
     * Sort downloadable sample array
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
     * Text of Visible in downloadable assert for sample block
     *
     * @return string
     */
    public function toString()
    {
        return 'Sample block for downloadable product on front-end is visible.';
    }
}
