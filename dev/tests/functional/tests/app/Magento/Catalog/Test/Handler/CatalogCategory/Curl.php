<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Handler\CatalogCategory;

use Mtf\Fixture\FixtureInterface;
use Mtf\Handler\Curl as AbstractCurl;
use Mtf\System\Config;
use Mtf\Util\Protocol\CurlInterface;
use Mtf\Util\Protocol\CurlTransport;
use Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Class Curl
 * Create new category via curl
 */
class Curl extends AbstractCurl implements CatalogCategoryInterface
{
    /**
     * Data use config for category
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
     * Post request for creating Subcategory
     *
     * @param FixtureInterface|null $fixture [optional]
     * @return array
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $data['general'] = $this->replaceMappingData($fixture->getData());
        if ($fixture->hasData('landing_page')) {
            $data['general']['landing_page'] = $this->getBlockId($fixture->getLandingPage());
        }

        $diff = array_diff($this->dataUseConfig, array_keys($data['general']));
        if (!empty($diff)) {
            $data['use_config'] = $diff;
        }
        $parentCategoryId = $data['general']['parent_id'];

        $url = $_ENV['app_backend_url'] . 'catalog/category/save/store/0/parent/' . $parentCategoryId . '/';
        $curl = new BackendDecorator(new CurlTransport(), new Config());
        $curl->write(CurlInterface::POST, $url, '1.0', [], $data);
        $response = $curl->read();
        $curl->close();

        preg_match('#http://.+/id/(\d+).+store/#m', $response, $matches);
        $id = isset($matches[1]) ? (int)$matches[1] : null;

        return ['id' => $id];
    }

    /**
     * Getting block id by name
     *
     * @param string $landingName
     * @return int|null
     */
    public function getBlockId($landingName)
    {
        $url = $_ENV['app_backend_url'] . 'catalog/category';
        $curl = new BackendDecorator(new CurlTransport(), new Config());
        $curl->write(CurlInterface::POST, $url, '1.0', [], []);
        $response = $curl->read();
        $curl->close();
        preg_match('~<option.*value="(\d+)".*>' . preg_quote($landingName) . '</option>~', $response, $matches);
        $id = isset($matches[1]) ? (int)$matches[1] : null;

        return $id;
    }
}
