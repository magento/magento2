<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Test\Handler\DownloadableProduct;

use Magento\Catalog\Test\Handler\CatalogProductSimple\Curl as ProductCurl;
use Magento\Downloadable\Test\Fixture\DownloadableProduct;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Config\DataInterface;
use Magento\Mtf\System\Event\EventManagerInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Class Curl
 * Create new downloadable product via curl
 */
class Curl extends ProductCurl implements DownloadableProductInterface
{
    /**
     * Constructor
     *
     * @param DataInterface $configuration
     * @param EventManagerInterface $eventManager
     */
    public function __construct(DataInterface $configuration, EventManagerInterface $eventManager)
    {
        parent::__construct($configuration, $eventManager);

        $this->mappingData += [
            'links_purchased_separately' => [
                'Yes' => 1,
                'No' => 0,
            ],
            'is_shareable' => [
                'Yes' => 1,
                'No' => 0,
                'Use config' => 2,
            ],
        ];
    }

    /**
     * Prepare POST data for creating product request
     *
     * @param FixtureInterface $fixture
     * @param string|null $prefix [optional]
     * @return array
     */
    protected function prepareData(FixtureInterface $fixture, $prefix = null)
    {
        /** @var DownloadableProduct $fixture */
        $fixtureData = parent::prepareData($fixture, $prefix);
        $downloadableData = [
            'downloadable_sample' => $fixture->getDownloadableSample(),
            'downloadable_links' => $fixture->getDownloadableLinks()
        ];
        $data = [];

        if (!empty($downloadableData['downloadable_sample'])) {
            foreach ($downloadableData['downloadable_sample']['downloadable']['sample'] as $key => $sample) {
                $data['downloadable']['sample'][$key]['title'] = $sample['title'];
                // only url type
                $data['downloadable']['sample'][$key]['type'] = 'url';
                $data['downloadable']['sample'][$key]['sample_url'] = $sample['sample_url'];
                $data['downloadable']['sample'][$key]['sort_order'] = $sample['sort_order'];
            }

            $sampleTitle = $downloadableData['downloadable_sample']['title'];
            $data['samples_title'] = $prefix ? [$prefix => $sampleTitle] : $sampleTitle;

            unset($data['downloadable_sample']);
        }

        if (!empty($downloadableData['downloadable_links'])) {
            foreach ($downloadableData['downloadable_links']['downloadable']['link'] as $key => $link) {
                $data['downloadable']['link'][$key]['title'] = $link['title'];
                // only url type
                $data['downloadable']['link'][$key]['type'] = 'url';
                $data['downloadable']['link'][$key]['link_url'] = $link['file_link_url'];
                $data['downloadable']['link'][$key]['price'] = $link['price'];
                $data['downloadable']['link'][$key]['number_of_downloads'] = $link['number_of_downloads'];
                $data['downloadable']['link'][$key]['is_shareable'] = $link['is_shareable'];
                $data['downloadable']['link'][$key]['sample']['type'] = 'url';
                $data['downloadable']['link'][$key]['sample']['url'] = $link['sample']['sample_url'];
            }

            $links = [
                'links_title' => $downloadableData['downloadable_links']['title'],
                'links_purchased_separately' => $downloadableData['downloadable_links']['links_purchased_separately']
            ];
            $data = array_merge($data, $prefix ? [$prefix => $links] : $links);

            unset($downloadableData['downloadable_links']);
        }

        $data = array_merge_recursive($fixtureData, $data);
        return $this->replaceMappingData($data);
    }

    /**
     * Create product via curl
     *
     * @param array $data
     * @param array $config
     * @return array
     * @throws \Exception
     */
    protected function createProduct(array $data, array $config)
    {
        $url = $this->getUrl($config);
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $curl->addOption(CURLOPT_HEADER, 1);
        $curl->write($url, $data);
        $response = $curl->read();
        $curl->close();

        if (!strpos($response, 'data-ui-id="messages-message-success"')) {
            throw new \Exception("Product creation by curl handler was not successful! Response: $response");
        }
        preg_match("~Location: [^\s]*\/id\/(\d+)~", $response, $matches);
        $checkoutData = isset($data['product']['checkout_data']) ? $data['product']['checkout_data'] : null;
        if (isset($data['downloadable']['link'])) {
            foreach ($data['downloadable']['link'] as $key => $link) {
                preg_match('`"link_id":"(\d*?)","title":"' . $link['title'] . '"`', $response, $linkId);
                if (isset($checkoutData['options']['links'][$key]['label'])) {
                    $checkoutData['options']['links'][$key]['id'] = $linkId[1];
                }
            }
        }

        return ['id' => $matches[1], 'checkout_data' => $checkoutData];
    }
}
