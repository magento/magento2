<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Handler\Category;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Handler\Curl as AbstractCurl;
use Magento\Mtf\Util\Protocol\CurlInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Create new category via curl.
 */
class Curl extends AbstractCurl implements CategoryInterface
{
    /**
     * Data use config for category.
     *
     * @var array
     */
    protected $dataUseConfig = [
        'available_sort_by',
        'default_sort_by',
        'filter_price_range',
    ];

    /**
     * Mapping values for data.
     *
     * @var array
     */
    protected $mappingData = [
        'is_active' => [
            'Yes' => 1,
            'No' => 0,
        ],
        'is_anchor' => [
            'Yes' => 1,
            'No' => 0,
        ],
        'include_in_menu' => [
            'Yes' => 1,
            'No' => 0,
        ],
        'display_mode' => [
            'Static block and products' => 'PRODUCTS_AND_PAGE',
            'Static block only' => 'PAGE',
            'Products only' => 'PRODUCTS',
        ],
    ];

    /**
     * Post request for creating Subcategory.
     *
     * @param FixtureInterface|null $fixture [optional]
     * @return array
     * @throws \Exception
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $data = $this->prepareData($fixture);
        $url = $_ENV['app_backend_url'] . 'catalog/category/save/store/0/parent/' . $data['general']['parent_id'] . '/';
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $curl->write(CurlInterface::POST, $url, '1.0', [], $data);
        $response = $curl->read();
        $curl->close();

        if (!strpos($response, 'data-ui-id="messages-message-success"')) {
            $this->_eventManager->dispatchEvent(['curl_failed'], [$response]);
            throw new \Exception('Category creation by curl handler was not successful!');
        }

        preg_match('#http://.+/id/(\d+).+store/#m', $response, $matches);
        $id = isset($matches[1]) ? (int)$matches[1] : null;
        return ['id' => $id];
    }

    /**
     * Prepare category data for curl.
     *
     * @param FixtureInterface $fixture
     * @return array
     */
    protected function prepareData(FixtureInterface $fixture)
    {
        $data = ['general' => $this->replaceMappingData($fixture->getData())];
        $data['general']['is_anchor'] = isset($data['general']['is_anchor']) ? $data['general']['is_anchor'] : 0;

        if ($fixture->hasData('landing_page')) {
            $data['general']['landing_page'] = $this->getBlockId($fixture->getLandingPage());
        }

        $data['category_products'] = $this->prepareCategoryProducts($fixture);
        unset($data['general']['category_products']);

        $diff = array_diff($this->dataUseConfig, array_keys($data['general']));
        if (!empty($diff)) {
            $data['use_config'] = $diff;
        }

        return $data;
    }

    /**
     * Prepare category products data for curl.
     *
     * @param FixtureInterface $category
     * @return array
     */
    protected function prepareCategoryProducts(FixtureInterface $category)
    {
        $categoryProducts = [];
        $defaultPosition = 0;

        /** @var Category $category */
        if ($category->hasData('category_products')) {
            $products = $category->getDataFieldConfig('category_products')['source']->getProducts();
            foreach ($products as $product) {
                $categoryProducts[$product->getId()] = $defaultPosition;
            }
        }

        return json_encode($categoryProducts);
    }

    /**
     * Getting block id by name.
     *
     * @param string $landingName
     * @return int|null
     */
    protected function getBlockId($landingName)
    {
        $url = $_ENV['app_backend_url'] . 'catalog/category';
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $curl->write(CurlInterface::POST, $url, '1.0', [], []);
        $response = $curl->read();
        $curl->close();
        preg_match('~<option.*value="(\d+)".*>' . preg_quote($landingName) . '</option>~', $response, $matches);
        $id = isset($matches[1]) ? (int)$matches[1] : null;

        return $id;
    }
}
