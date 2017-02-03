<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Handler\BundleProduct;

use Magento\Bundle\Test\Fixture\BundleProduct;
use Magento\Catalog\Test\Handler\CatalogProductSimple\Curl as ProductCurl;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Config\DataInterface;
use Magento\Mtf\System\Event\EventManagerInterface;

/**
 * Create new bundle product via curl.
 */
class Curl extends ProductCurl implements BundleProductInterface
{
    /**
     * Fixture product.
     *
     * @var BundleProduct
     */
    protected $fixture;

    /**
     * @constructor
     * @param DataInterface $configuration
     * @param EventManagerInterface $eventManager
     */
    public function __construct(DataInterface $configuration, EventManagerInterface $eventManager)
    {
        parent::__construct($configuration, $eventManager);

        $this->mappingData += [
            'selection_can_change_qty' => [
                'Yes' => 1,
                'No' => 0,
            ],
            'sku_type' => [
                'Dynamic' => 0,
                'Fixed' => 1,
            ],
            'price_type' => [
                'Dynamic' => 0,
                'Fixed' => 1,
            ],
            'weight_type' => [
                'Dynamic' => 0,
                'Fixed' => 1,
            ],
            'shipment_type' => [
                'Together' => 0,
                'Separately' => 1,
            ],
            'type' => [
                'Drop-down' => 'select',
                'Radio Buttons' => 'radio',
                'Checkbox' => 'checkbox',
                'Multiple Select' => 'multi',
            ],
            'selection_price_type' => [
                'Fixed' => 0,
                'Percent' => 1,
            ]
        ];
    }

    /**
     * Post request for creating bundle product product.
     *
     * @param FixtureInterface|null $fixture [optional]
     * @return array
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $this->fixture = $fixture;
        return parent::persist($fixture);
    }

    /**
     * Prepare POST data for creating product request.
     *
     * @param FixtureInterface $fixture
     * @param string|null $prefix [optional]
     * @return array
     */
    protected function prepareData(FixtureInterface $fixture, $prefix = null)
    {
        $data = parent::prepareData($fixture, null);

        $selections = [];
        $bundleSelections = [];
        if (!empty($data['bundle_selections'])) {
            $selections = $data['bundle_selections'];
            $products = $selections['products'];
            unset($data['selections'], $selections['products']);

            foreach ($selections['bundle_options'] as $key => &$option) {
                $option['delete'] = '';
                $option['position'] = $key;
                foreach ($option['assigned_products'] as $productKey => $assignedProduct) {
                    $assignedProduct['data'] += [
                        'product_id' => $products[$key][$productKey]->getId(),
                        'delete' => '',
                        'position' => $productKey
                    ];
                    $bundleSelections[$key][] = $assignedProduct['data'];
                }
                unset($option['assigned_products']);
            }
        }
        $data = $prefix ? [$prefix => $data] : $data;
        $data = array_merge($data, $selections);
        $data['bundle_selections'] = $bundleSelections;

        return $this->replaceMappingData($data);
    }

    /**
     * Parse response.
     *
     * @param string $response
     * @return array
     */
    protected function parseResponse($response)
    {
        return array_replace_recursive(parent::parseResponse($response), $this->parseResponseSelections($response));
    }

    /**
     * Parse bundle selections in response.
     *
     * @param string $response
     * @return array
     */
    protected function parseResponseSelections($response)
    {
        $selectionIdKey = 1;
        $optionIdKey = 2;
        $productNameKey = 3;
        $responseSelections = [];
        $bundleSelections = $this->fixture->getBundleSelections();

        preg_match_all(
            '/{.*"selection_id":"(\d+)".*"option_id":"(\d+)".*"name":"([^"]+)".*}/',
            $response,
            $matches,
            PREG_SET_ORDER
        );
        foreach ($matches as $match) {
            $productName = $match[$productNameKey];
            $responseSelections[$productName] = [
                'selection_id' => $match[$selectionIdKey],
                'option_id' => $match[$optionIdKey],
            ];
        }

        foreach ($bundleSelections['bundle_options'] as $optionKey => $option) {
            foreach ($option['assigned_products'] as $assignedKey => $optionValue) {
                $productName = $optionValue['search_data']['name'];
                $bundleSelections['bundle_options'][$optionKey]['assigned_products'][$assignedKey] +=
                    $responseSelections[$productName];
            }
        }

        return ['bundle_selections' => $bundleSelections];
    }
}
