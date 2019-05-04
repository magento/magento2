<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Test\Constraint;

use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Downloadable\Test\Fixture\DownloadableProduct;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractAssertForm;

/**
 * Class AssertDownloadableLinksData
 *
 * Assert that Link block for downloadable product on front-end
 */
class AssertDownloadableLinksData extends AbstractAssertForm
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * List downloadable link fields for verify
     *
     * @var array
     */
    protected $downloadableLinksField = [
        'title',
        'downloadable',
    ];

    /**
     * List fields of downloadable link for verify
     *
     * @var array
     */
    protected $linkField = [
        'title',
        'links_purchased_separately',
        'price',
    ];

    /**
     * Assert Link block for downloadable product on front-end
     *
     * @param CatalogProductView $catalogProductView
     * @param DownloadableProduct $product
     * @param BrowserInterface $browser
     * @return void
     */
    public function processAssert(
        CatalogProductView $catalogProductView,
        DownloadableProduct $product,
        BrowserInterface $browser
    ) {
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');

        $fixtureDownloadableLinks = $this->prepareFixtureData($product);
        $pageOptions = $catalogProductView->getViewBlock()->getOptions($product);
        $pageDownloadableLinks = $this->preparePageData($pageOptions['downloadable_options']['downloadable_links']);
        $error = $this->verifyData($fixtureDownloadableLinks, $pageDownloadableLinks);
        \PHPUnit\Framework\Assert::assertEmpty($error, $error);
    }

    /**
     * Prepare fixture data for verify
     *
     * @param DownloadableProduct $product
     * @return array
     */
    protected function prepareFixtureData(DownloadableProduct $product)
    {
        $data = $this->sortDataByPath($product->getDownloadableLinks(), 'downloadable/link::sort_order');

        foreach ($data['downloadable']['link'] as $key => $link) {
            $link['links_purchased_separately'] = $data['links_purchased_separately'];
            $link = array_intersect_key($link, array_flip($this->linkField));

            $data['downloadable']['link'][$key] = $link;
        }
        $data = array_intersect_key($data, array_flip($this->downloadableLinksField));

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
        foreach ($data['downloadable']['link'] as $key => $link) {
            $link = array_intersect_key($link, array_flip($this->linkField));
            $data['downloadable']['link'][$key] = $link;
        }
        $data = array_intersect_key($data, array_flip($this->downloadableLinksField));

        return $data;
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
