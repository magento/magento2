<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Handler\CatalogProductSimple;

use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\Handler\Curl as AbstractCurl;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Create new simple product via curl.
 */
class Curl extends AbstractCurl implements CatalogProductSimpleInterface
{
    /**
     * Fixture instance.
     *
     * @var InjectableFixture
     */
    protected $fixture;

    /**
     * Prepared fields.
     *
     * @var array
     */
    protected $fields;

    /**
     * Temporary media path.
     *
     * @var string
     */
    protected $mediaPathTmp = '/pub/media/tmp/catalog/product/';

    /**
     * Product website.
     *
     * @var array
     */
    protected $website;

    /**
     * Mapping values for data.
     *
     * @var array
     */
    protected $mappingData = [
        'attribute_set_id' => [
            'Default' => 4
        ],
        'tax_class_id' => [
            'Taxable Goods' => 2
        ],
        'links_purchased_separately' => [
            'Yes' => 1,
            'No' => 0
        ],
        'use_config_notify_stock_qty' => [
            'Yes' => 1,
            'No' => 0
        ],
        'is_shareable' => [
            'Yes' => 1,
            'No' => 0,
            'Use config' => 2
        ],
        'required' => [
            'Yes' => 1,
            'No' => 0
        ],
        'manage_stock' => [
            'Yes' => 1,
            'No' => 0
        ],
        'use_config_manage_stock' => [
            'Yes' => 1,
            'No' => 0
        ],
        'product_has_weight' => [
            'This item has weight' => 1,
            'This item has no weight' => 0,
        ],
        'use_config_enable_qty_increments' => [
            'Yes' => 1,
            'No' => 0
        ],
        'use_config_qty_increments' => [
            'Yes' => 1,
            'No' => 0
        ],
        'is_in_stock' => [
            'In Stock' => 1,
            'Out of Stock' => 0
        ],
        'visibility' => [
            'Not Visible Individually' => 1,
            'Catalog' => 2,
            'Search' => 3,
            'Catalog, Search' => 4
        ],
        'status' => [
            'No' => 2,
            'Yes' => 1
        ],
        'is_require' => [
            'Yes' => 1,
            'No' => 0
        ],
        'msrp_display_actual_price_type' => [
            'Use config' => 0,
            'On Gesture' => 1,
            'In Cart' => 2,
            'Before Order Confirmation' => 3
        ],
        'enable_qty_increments' => [
            'Yes' => 1,
            'No' => 0,
        ],
        'use_config_min_sale_qty' => [
            'Yes' => 1,
            'No' => 0,
        ],
        'use_config_max_sale_qty' => [
            'Yes' => 1,
            'No' => 0,
        ],
    ];

    /**
     * Placeholder for price data sent Curl.
     *
     * @var array
     */
    protected $priceData = [
        'website' => [
            'name' => 'website_id',
            'data' => [
                'All Websites [USD]' => 0
            ]
        ],
        'customer_group' => [
            'name' => 'cust_group'
        ]
    ];

    /**
     * Placeholder for fpt data sent Curl.
     *
     * @var array
     */
    protected $fptData = [
        'website' => [
            'name' => 'website_id',
            'data' => [
                'All Websites [USD]' => 0
            ]
        ],
        'country_name' => [
            'name' => 'country',
            'data' => [
                'United States' => 'US'
            ]
        ],
        'state_name' => [
            'name' => 'state',
            'data' => [
                'California' => 12,
                '*' => 0
            ]
        ]
    ];

    /**
     * Default manage stock data.
     *
     * @var array
     */
    protected $manageStock = [
        'Yes' => [
            'manage_stock' => 'Yes',
            'use_config_manage_stock' => 'Yes',
            'enable_qty_increments' => 'No',
            'use_config_enable_qty_increments' => 'Yes',
        ],
        'No' => [
            'manage_stock' => 'No',
            'use_config_manage_stock' => 'No',
            'min_sale_qty' => 1,
            'use_config_min_sale_qty' => 1,
            'max_sale_qty' => 10000,
            'use_config_max_sale_qty' => 1,
            'enable_qty_increments' => 'No',
            'use_config_enable_qty_increments' => 'No',
        ]
    ];

    /**
     * Select custom options.
     *
     * @var array
     */
    protected $selectOptions = ['drop_down', 'radio', 'checkbox', 'multiple'];

    /**
     * Post request for creating simple product.
     *
     * @param FixtureInterface|null $fixture [optional]
     * @return array
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $config = $fixture->getDataConfig();
        $data = $this->prepareData($fixture);

        return $this->createProduct($data, $config);
    }

    /**
     * Create product via curl.
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
            $this->_eventManager->dispatchEvent(['curl_failed'], [$response]);
            throw new \Exception('Product creation by curl handler was not successful!');
        }

        return $this->parseResponse($response);
    }

    /**
     * Parse data in response.
     *
     * @param string $response
     * @return array
     */
    protected function parseResponse($response)
    {
        preg_match('~Location: [^\s]*\/id\/(\d+)~', $response, $matches);
        $id = isset($matches[1]) ? $matches[1] : null;
        return ['id' => $id];
    }

    /**
     * Retrieve URL for request with all necessary parameters.
     *
     * @param array $config
     * @return string
     */
    protected function getUrl(array $config)
    {
        $requestParams = isset($config['create_url_params']) ? $config['create_url_params'] : [];
        $params = '';
        foreach ($requestParams as $key => $value) {
            $params .= $key . '/' . $value . '/';
        }
        return $_ENV['app_backend_url'] . 'catalog/product/save/' . $params . 'popup/1/back/edit';
    }

    /**
     * Prepare POST data for creating product request.
     *
     * @param FixtureInterface $fixture
     * @return array
     */
    public function prepareData(FixtureInterface $fixture)
    {
        $this->fixture = $fixture;
        $this->fields = ['product' => $fixture->getData()];

        $this->prepareProductDetails();
        $this->prepareWebsitesData();
        $this->prepareWebsites();
        $this->prepareAdvancedPricing();
        $this->prepareAdvancedInventory();
        $this->prepareCustomOptionsData();
        $this->prepareAutosetting();
        $this->prepareCustomAttributes();
        if (isset($this->fields['product']['media_gallery'])) {
            $this->fields['product']['media_gallery']
                = $this->prepareMediaGallery($this->fields['product']['media_gallery']);
        }

        $this->fields['product'] = $this->replaceMappingData($this->fields['product']);
        return $this->fields;
    }

    /**
     * Preparation of "Product Details" tab data.
     *
     * @return void
     */
    protected function prepareProductDetails()
    {
        $this->prepareStatus();
        $this->preparePrice();
        $this->prepareIsVirtual();
        $this->prepareAttributeSet();
        $this->prepareTaxClass();
        $this->prepareQuantityAndStockStatus();
        $this->prepareCategory();
    }

    /**
     * Preparation of product status.
     *
     * @return void
     */
    protected function prepareStatus()
    {
        $this->fields['product']['status'] = isset($this->fields['product']['status'])
            ? $this->fields['product']['status']
            : 'Yes';
    }

    /**
     * Preparation of price value.
     *
     * @return void
     */
    protected function preparePrice()
    {
        $this->fields['product']['price'] = isset($this->fields['product']['price'])
            ? (is_array($this->fields['product']['price']) ? null : $this->fields['product']['price'])
            : null;
    }

    /**
     * Preparation wheather product 'Is Virtual'.
     *
     * @return void
     */
    protected function prepareIsVirtual()
    {
        $this->fields['product']['is_virtual'] = isset($this->fields['product']['is_virtual'])
            ? $this->fields['product']['is_virtual']
            : 'No';
    }

    /**
     * Preparation of attribute set data.
     *
     * @return void
     */
    protected function prepareAttributeSet()
    {
        if ($this->fixture->hasData('attribute_set_id')
            && !empty($this->fixture->getDataFieldConfig('attribute_set_id')['source'])
            && $this->fixture->getDataFieldConfig('attribute_set_id')['source']->getAttributeSet()
        ) {
            $this->fields['product']['attribute_set_id'] = $this->fixture
                ->getDataFieldConfig('attribute_set_id')['source']
                ->getAttributeSet()
                ->getAttributeSetId();
        } else if ($this->fixture->hasData('attribute_set_id')
            && !empty($this->fixture->getDataFieldConfig('attribute_set_id')['source'])
            && $this->fixture->getDataFieldConfig('attribute_set_id')['source']->getAttributeSet()
        ) {
            $this->fields['product']['attribute_set_id'] = $this->fixture
                ->getDataFieldConfig('attribute_set_id')['source']
                ->getAttributeSet()
                ->getAttributeSetId();
        } else {
            $this->fields['product']['attribute_set_id'] = 'Default';
        }
    }

    /**
     * Preparation of tax class data.
     *
     * @return void
     */
    protected function prepareTaxClass()
    {
        if ($this->fixture->hasData('tax_class_id')) {
            $this->fields['product']['tax_class_id'] = $this->fixture->getDataFieldConfig('tax_class_id')['source']
                ->getTaxClassId();
        } else {
            $this->fields['product']['tax_class_id'] = 'Taxable Goods';
        }
    }

    /**
     * Preparation of quantity and stock status data.
     *
     * @return void
     */
    protected function prepareQuantityAndStockStatus()
    {
        $quantityAndStockStatus = isset($this->fields['product']['quantity_and_stock_status'])
            ? $this->fields['product']['quantity_and_stock_status']
            : ['is_in_stock' => 'In Stock'];

        if (!isset($quantityAndStockStatus['is_in_stock'])) {
            $qty = isset($quantityAndStockStatus['qty']) ? (int)$quantityAndStockStatus['qty'] : null;
            $quantityAndStockStatus['is_in_stock'] = 0 === $qty ? 'Out of Stock' : 'In Stock';
        }

        $this->fields['product']['quantity_and_stock_status'] = $quantityAndStockStatus;
    }

    /**
     * Preparation of category data.
     *
     * @return void
     */
    protected function prepareCategory()
    {
        if ($this->fixture->hasData('category_ids')) {
            $this->fields['product']['category_ids'] = [];

            foreach ($this->fixture->getDataFieldConfig('category_ids')['source']->getCategories() as $category) {
                $this->fields['product']['category_ids'][] = $category->getId();
            }
        }
    }

    /**
     * Preparation of websites data.
     *
     * @return void
     */
    protected function prepareWebsites()
    {
        if (!empty($this->fields['product']['website_ids'])) {
            unset($this->fields['product']['website_ids']);
            foreach ($this->fixture->getDataFieldConfig('website_ids')['source']->getWebsites() as $key => $website) {
                $this->fields['product']['website_ids'][$key] = $website->getWebsiteId();
            }
        } else {
            $website = \Magento\Mtf\ObjectManagerFactory::getObjectManager()
                ->create(\Magento\Store\Test\Fixture\Website::class, ['dataset' => 'default']);
            $this->fields['product']['website_ids'][] = $website->getWebsiteId();
        }
    }

    /**
     * Preparation of advanced pricing data.
     *
     * @return void
     */
    protected function prepareAdvancedPricing()
    {
        if (isset($this->fields['product']['tier_price'])) {
            $this->fields['product']['tier_price'] = $this->preparePriceFields(
                $this->fields['product']['tier_price']
            );
        }
    }

    /**
     * Update product websites.
     *
     * @return void
     */
    protected function prepareWebsitesData()
    {
        if (!empty($this->fields['product']['website_ids'])) {
            foreach ($this->fixture->getDataFieldConfig('website_ids')['source']->getWebsites() as $key => $website) {
                $this->fields['product']['website_ids'][$key] = $website->getWebsiteId();
            }
        }
    }

    /**
     * Preparation of tier price data.
     *
     * @param array $fields
     * @return array
     */
    protected function preparePriceFields(array $fields)
    {
        $this->website = $this->fixture->getDataFieldConfig('tier_price')['source']->getWebsite();
        foreach ($fields as $priceKey => &$field) {
            foreach ($this->priceData as $key => $data) {
                if ($data['name'] == 'cust_group') {
                    $field[$data['name']] = $this->fixture->getDataFieldConfig('tier_price')['source']
                        ->getCustomerGroups()[$priceKey]->getCustomerGroupId();
                } else {
                    if ($this->website !== null) {
                        unset($this->priceData['website']['data']);
                        $this->priceData['website']['data'][$this->website->getCode()]
                            = $this->website->getData('website_id');
                    }

                    $field[$data['name']] = $this->priceData[$key]['data'][$field[$key]];
                }
                unset($field[$key]);
            }
            $field['delete'] = '';
        }
        return $fields;
    }

    /**
     * Preparation of advanced inventory data.
     *
     * @return void
     */
    protected function prepareAdvancedInventory()
    {
        if (!isset($this->fields['product']['stock_data']['manage_stock'])) {
            $this->fields['product']['stock_data']['manage_stock'] = 'Yes';
        }

        $this->fields['product']['stock_data']['is_in_stock'] =
            $this->fields['product']['quantity_and_stock_status']['is_in_stock'];
        $this->fields['product']['stock_data'] = array_merge(
            $this->manageStock[$this->fields['product']['stock_data']['manage_stock']],
            $this->fields['product']['stock_data']
        );
    }

    /**
     * Preparation of custom options data.
     *
     * @return void
     */
    protected function prepareCustomOptionsData()
    {
        if (!isset($this->fields['product']['custom_options'])) {
            return;
        }
        $options = [];
        foreach ($this->fields['product']['custom_options'] as $key => $customOption) {
            $options[$key] = [
                'is_delete' => '',
                'option_id' => 0,
                'type' => $this->optionNameConvert($customOption['type']),
            ];

            foreach ($customOption['options'] as $index => $option) {
                $customOption['options'][$index]['is_delete'] = '';
                $customOption['options'][$index]['price_type'] = strtolower($option['price_type']);
            }
            $options[$key] += in_array($options[$key]['type'], $this->selectOptions)
                ? ['values' => $customOption['options']]
                : $customOption['options'][0];

            unset($customOption['options']);
            $options[$key] += $customOption;
        }

        $this->fields['product']['options'] = $options;
        $this->fields['product']['affect_product_custom_options'] = 1;
        unset($this->fields['product']['custom_options']);
    }

    /**
     * Convert option name.
     *
     * @param string $optionName
     * @return string
     */
    protected function optionNameConvert($optionName)
    {
        $optionName = substr($optionName, strpos($optionName, "/") + 1);
        $optionName = str_replace(['-', ' & '], "_", trim($optionName));
        $end = strpos($optionName, ' ');
        if ($end !== false) {
            $optionName = substr($optionName, 0, $end);
        }
        return strtolower($optionName);
    }

    /**
     * Preparation of "Autosetting" tab data.
     *
     * @return void
     */
    protected function prepareAutosetting()
    {
        $this->fields['product']['visibility'] = isset($this->fields['product']['visibility'])
            ? $this->fields['product']['visibility']
            : 'Catalog, Search';
    }

    /**
     * Preparation of attributes data.
     *
     * @return void
     */
    protected function prepareCustomAttributes()
    {
        if (isset($this->fields['product']['custom_attribute'])) {
            $attrCode = $this->fields['product']['custom_attribute']['code'];
            $this->fields['product'][$attrCode] = $this->fields['product']['custom_attribute']['value'];
            unset($this->fields['product']['custom_attribute']);
        }
        if (isset($this->fields['product']['attributes'])) {
            $this->fields['product'] += $this->fields['product']['attributes'];
            unset($this->fields['product']['attributes']);
        }

        $this->prepareFpt();
    }

    /**
     * Preparation of fpt attribute data.
     *
     * @return void
     */
    protected function prepareFpt()
    {
        if (isset($this->fields['product']['fpt'])) {
            $attributeLabel = $this->fixture->getDataFieldConfig('attribute_set_id')['source']
                ->getAttributeSet()->getDataFieldConfig('assigned_attributes')['source']
                ->getAttributes()[0]->getFrontendLabel();

            foreach ($this->fields['product']['fpt'] as &$field) {
                foreach ($this->fptData as $key => $data) {
                    $field[$data['name']] = $this->fptData[$key]['data'][$field[$key]];
                    unset($field[$key]);
                }
                $field['delete'] = '';
            }

            $this->fields['product'][$attributeLabel] = $this->fields['product']['fpt'];
            unset($this->fields['product']['fpt']);
        }
    }

    /**
     * Create test image file.
     *
     * @param string $filename
     * @return array
     */
    protected function prepareMediaGallery($filename)
    {
        $filePath = $this->getFullPath($filename);

        if (!file_exists($filePath)) {
            $image = imagecreate(300, 200);
            $colorYellow = imagecolorallocate($image, 255, 255, 0);
            imagefilledrectangle($image, 50, 50, 250, 150, $colorYellow);
            $directory = dirname($filePath);
            if (!file_exists($directory)) {
                mkdir($directory, 0777, true);
            }
            imagejpeg($image, $filePath);
            imagedestroy($image);
        }

        return [
            'images' => [
                0 => [
                    'position' => 1,
                    'file' => $filename,
                    'disabled' => 0,
                    'label' => $filename,
                ],
            ],
        ];
    }

    /**
     * Gets full path based on filename.
     *
     * @param string $filename
     * @return string
     */
    private function getFullPath($filename)
    {
        return BP . $this->mediaPathTmp . $filename;
    }
}
