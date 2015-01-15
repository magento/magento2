<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Test\Constraint;

use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Downloadable\Test\Fixture\DownloadableProductInjectable;
use Mtf\Client\Browser;
use Mtf\Constraint\AbstractAssertForm;

/**
 * Class AssertDownloadableSamplesData
 *
 * Assert that Sample block for downloadable product on front-end
 */
class AssertDownloadableSamplesData extends AbstractAssertForm
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * List downloadable sample links fields for verify
     *
     * @var array
     */
    protected $downloadableSampleField = [
        'title',
        'downloadable',
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
