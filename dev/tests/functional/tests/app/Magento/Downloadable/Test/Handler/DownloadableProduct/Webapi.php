<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Test\Handler\DownloadableProduct;

use Magento\Catalog\Test\Handler\CatalogProductSimple\Webapi as SimpleProductWebapi;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Mtf\Config\DataInterface;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\System\Event\EventManagerInterface;
use Magento\Mtf\Util\Protocol\CurlTransport\WebapiDecorator;

/**
 * Create new downloadable product via webapi.
 */
class Webapi extends SimpleProductWebapi implements DownloadableProductInterface
{
    /**
     * @constructor
     * @param DataInterface $configuration
     * @param EventManagerInterface $eventManager
     * @param WebapiDecorator $webapiTransport
     * @param Curl $handlerCurl
     */
    public function __construct(
        DataInterface $configuration,
        EventManagerInterface $eventManager,
        WebapiDecorator $webapiTransport,
        Curl $handlerCurl
    ) {
        parent::__construct($configuration, $eventManager, $webapiTransport, $handlerCurl);
    }

    /**
     * Prepare data for creating product request.
     *
     * @return void
     */
    protected function prepareData()
    {
        parent::prepareData();
        $this->prepareDownloadableProductSamples();
        $this->prepareDownloadableProductLinks();
    }

    /**
     * Parse data in response.
     *
     * @param array $response
     * @return array
     */
    protected function parseResponse(array $response)
    {
        $checkoutData = $this->fixture->hasData('checkout_data') ? $this->fixture->getData('checkout_data') : [];

        if (isset($checkoutData['options']['links'])
            && !empty($response['extension_attributes']['downloadable_product_links'])
        ) {
            foreach (array_keys($checkoutData['options']['links']) as $key) {
                $checkoutData['options']['links'][$key]['id'] =
                    $response['extension_attributes']['downloadable_product_links'][$key]['id'];
            }
        }

        return [
            'id' => $response['id'],
            'checkout_data' => $checkoutData
        ];
    }

    /**
     * Preparation of downloadable samples data.
     *
     * @return void
     */
    protected function prepareDownloadableProductSamples()
    {
        $downloadableProductSamples = [];

        if (isset($this->fields['downloadable']['sample'])) {
            foreach ($this->fields['downloadable']['sample'] as $key => $sample) {
                $downloadableProductSamples[$key] = $this->prepareSampleData($sample);
            }
            $this->fields['product']['extension_attributes']['downloadable_product_samples'] =
                $downloadableProductSamples;
            $this->fields['product']['samples_title'] = $this->fields['product']['downloadable_sample']['title'];
        }

        unset($this->fields['samples_title']);
        unset($this->fields['downloadable']['sample']);
        unset($this->fields['product']['downloadable_sample']);
    }

    /**
     * Prepare sample data.
     *
     * @param array $sample
     * @return array
     */
    protected function prepareSampleData(array $sample)
    {
        return [
            'title' => $sample['title'],
            'sort_order' => isset($sample['sort_order']) ? $sample['sort_order'] : 0,
            'sample_type' => $sample['type'],
            'sample_url' => isset($sample['sample_url']) ? $sample['sample_url'] : null,
            'sample_file' => isset($sample['sample_file']) ? $sample['sample_file'] : null,
        ];
    }

    /**
     * Preparation of downloadable links data.
     *
     * @return void
     */
    protected function prepareDownloadableProductLinks()
    {
        $downloadableProductLinks = [];

        foreach ($this->fields['downloadable']['link'] as $key => $link) {
            $downloadableProductLinks[$key] = $this->prepareLinkData($link);
        }

        $this->fields['product']['links_exist'] = 1;
        $this->fields['product']['links_purchased_separately'] =
            $this->fields['product']['downloadable_links']['links_purchased_separately'];
        $this->fields['product']['links_title'] = isset($this->fields['product']['downloadable_links']['title'])
            ? $this->fields['product']['downloadable_links']['title']
            : null;
        $this->fields['product']['extension_attributes']['downloadable_product_links'] = $downloadableProductLinks;

        unset($this->fields['downloadable']);
        unset($this->fields['product']['downloadable_links']);
    }

    /**
     * Prepare link data.
     *
     * @param array $link
     * @return array
     */
    protected function prepareLinkData(array $link)
    {
        return [
            'title' => $link['title'],
            'sort_order' => isset($link['sort_order']) ? $link['sort_order'] : 0,
            'is_shareable' => $link['is_shareable'],
            'price' => floatval($link['price']),
            'number_of_downloads' => isset($link['number_of_downloads']) ? $link['number_of_downloads'] : 0,
            'link_type' => $link['type'],
            'link_url' => isset($link['link_url']) ? $link['link_url'] : null,
            'link_file' => isset($link['link_file']) ? $link['link_file'] : null,
            'sample_type' => isset($link['sample']['type']) ? $link['sample']['type'] : null,
            'sample_url' => isset($link['sample']['url']) ? $link['sample']['url'] : null,
            'sample_file' => isset($link['sample']['file']) ? $link['sample']['file'] : null,
        ];
    }
}
