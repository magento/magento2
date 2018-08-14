<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Handler\Category;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Mtf\Config\DataInterface;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Handler\Curl as AbstractCurl;
use Magento\Mtf\System\Event\EventManagerInterface;
use Magento\Mtf\Util\Protocol\CurlInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Create new category via curl.
 */
class Curl extends AbstractCurl implements CategoryInterface
{
    /**
     * Curl transport for send request via backend.
     *
     * @var BackendDecorator
     */
    protected $backendTransport;

    /**
     * Category instance.
     *
     * @var Category
     */
    protected $fixture;

    /**
     * Prepared data for creating category.
     *
     * @var array
     */
    protected $fields;

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
        'include_in_menu' => [
            'Yes' => 1,
            'No' => 0,
        ],
        'display_mode' => [
            'Static block and products' => 'PRODUCTS_AND_PAGE',
            'Static block only' => 'PAGE',
            'Products only' => 'PRODUCTS',
        ],
        'is_anchor' => [
            'Yes' => 1,
            'No' => 0,
        ],
        'available_product_listing_config' => [
            'Yes' => 1,
            'No' => 0,
        ],
        'custom_use_parent_settings' => [
            'Yes' => 1,
            'No' => 0,
        ],
        'custom_apply_to_products' => [
            'Yes' => 1,
            'No' => 0,
        ],
        'page_layout' => [
            '1 column' => '1column',
            '2 columns with left bar' => '2columns-left',
            '2 columns with right bar' => '2columns-right',
            '3 columns' => '3columns',
            'Empty' => 'empty',
        ]
    ];

    /**
     * Mapping values for "available_sort_by" field.
     *
     * @var array
     */
    protected $availableSortBy = [
        'Position' => 'position',
        'Name' => 'name',
        'Price' => 'price',
    ];

    /**
     * @constructor
     * @param DataInterface $configuration
     * @param EventManagerInterface $eventManager
     * @param BackendDecorator $backendTransport
     */
    public function __construct(
        DataInterface $configuration,
        EventManagerInterface $eventManager,
        BackendDecorator $backendTransport
    ) {
        parent::__construct($configuration, $eventManager);
        $this->backendTransport = $backendTransport;
    }

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

        $this->backendTransport->write($url, $data);
        $response = $this->backendTransport->read();
        $this->backendTransport->close();

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
    public function prepareData(FixtureInterface $fixture)
    {
        $this->fixture = $fixture;
        $this->fields = ['general' => $fixture->getData()];

        $this->prepareGeneralInformation();
        $this->prepareDisplaySetting();
        $this->prepareCategoryProducts();

        $this->fields['general'] = $this->replaceMappingData($this->fields['general']);
        return $this->fields;
    }

    /**
     * Prepare data for "General Information" tab.
     *
     * @return void
     */
    protected function prepareGeneralInformation()
    {
        $this->fields['general']['is_anchor'] = isset($this->fields['general']['is_anchor'])
            ? $this->fields['general']['is_anchor']
            : 'No';

        $this->fields['general']['include_in_menu'] = isset($this->fields['general']['include_in_menu'])
            ? $this->fields['general']['include_in_menu']
            : 'Yes';
    }

    /**
     * Prepare data for "Display Setting" tab.
     *
     * @return void
     */
    protected function prepareDisplaySetting()
    {
        if ($this->fixture->hasData('landing_page')) {
            $this->fields['general']['landing_page'] = $this->getBlockId($this->fixture->getLandingPage());
        }

        $this->prepareAvailableSortBy();

        $useConfig = array_diff($this->dataUseConfig, array_keys($this->fields['general']));
        if (!empty($useConfig)) {
            $this->fields['use_config'] = $useConfig;
        }
        unset($this->fields['general']['use_config']);
    }

    /**
     * Prepare data for "available_sort_by" field.
     *
     * @return void
     */
    protected function prepareAvailableSortBy()
    {
        if (isset($this->fields['general']['available_sort_by'])) {
            foreach ($this->fields['general']['available_sort_by'] as $key => $value) {
                $this->fields['general']['available_sort_by'][$key] = $this->availableSortBy[$value];
            }
        }
    }

    /**
     * Prepare category products data for curl.
     *
     * @return void
     */
    protected function prepareCategoryProducts()
    {
        $categoryProducts = [];
        $defaultPosition = 0;

        if ($this->fixture->hasData('category_products')) {
            $products = $this->fixture->getDataFieldConfig('category_products')['source']->getProducts();
            foreach ($products as $product) {
                $categoryProducts[$product->getId()] = $defaultPosition;
            }
        }

        $this->fields['category_products'] = json_encode($categoryProducts);
        unset($this->fields['general']['category_products']);
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
        $curl->write($url, [], CurlInterface::GET);
        $response = $curl->read();
        $curl->close();
        preg_match('~\{"value":"(\d+)","label":"' . preg_quote($landingName) . '"\}~', $response, $matches);
        $id = isset($matches[1]) ? (int)$matches[1] : null;

        return $id;
    }
}
