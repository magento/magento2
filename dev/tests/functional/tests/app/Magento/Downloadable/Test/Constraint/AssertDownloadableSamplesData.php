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
use Mtf\Constraint\AbstractAssertForm;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Downloadable\Test\Fixture\DownloadableProductInjectable;

/**
 * Class AssertDownloadableSamplesData
 *
 * Assert that Sample block for downloadable product on front-end
 */
class AssertDownloadableSamplesData extends AbstractAssertForm
{
    /**
     * List downloadable sample links fields for verify
     *
     * @var array
     */
    protected $downloadableSampleField = [
        'title',
        'downloadable'
    ];

    /**
     * List fields of downloadable sample link for verify
     *
     * @var array
     */
    protected $linkField = [
        'title',
    ];

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
     * @param DownloadableProductInjectable $product
     * @param Browser $browser
     * @return void
     */
    public function processAssert(
        CatalogProductView $productView,
        DownloadableProductInjectable $product,
        Browser $browser
    ) {
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');

        $fixtureSampleLinks = $this->prepareFixtureData($product);
        $pageOptions = $productView->getViewBlock()->getOptions($product);
        $pageSampleLinks = isset($pageOptions['downloadable_options']['downloadable_sample'])
            ? $this->preparePageData($pageOptions['downloadable_options']['downloadable_sample'])
            : [];
        $error = $this->verifyData($fixtureSampleLinks, $pageSampleLinks);
        \PHPUnit_Framework_Assert::assertEmpty($error, $error);
    }

    /**
     * Prepare fixture data for verify
     *
     * @param DownloadableProductInjectable $product
     * @return array
     */
    protected function prepareFixtureData(DownloadableProductInjectable $product)
    {
        $data = $this->sortDataByPath($product->getDownloadableSample(), 'downloadable/sample::sort_order');

        foreach ($data['downloadable']['sample'] as $key => $link) {
            $link = array_intersect_key($link, array_flip($this->linkField));
            $data['downloadable']['sample'][$key] = $link;
        }
        $data = array_intersect_key($data, array_flip($this->downloadableSampleField));

        return $data;
    }

    /**
     * Prepare page data for verify
     *
     * @param array $data
     * @return array
     */
    protected function preparePageData(array $data)
    {
        foreach ($data['downloadable']['sample'] as $key => $link) {
            $link = array_intersect_key($link, array_flip($this->linkField));
            $data['downloadable']['sample'][$key] = $link;
        }
        $data = array_intersect_key($data, array_flip($this->downloadableSampleField));

        return $data;
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
