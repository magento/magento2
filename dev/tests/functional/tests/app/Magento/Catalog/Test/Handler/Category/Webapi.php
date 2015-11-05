<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Handler\Category;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Mtf\Config\DataInterface;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\System\Event\EventManagerInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\WebapiDecorator;
use Magento\Mtf\Handler\Webapi as AbstractWebapi;

/**
 * Create new category via webapi.
 */
class Webapi extends AbstractWebapi implements CategoryInterface
{
    /**
     * Curl handler instance.
     *
     * @var Curl
     */
    protected $handlerCurl;

    /**
     * List basic fields of category.
     *
     * @var array
     */
    protected $basicFields = [
        'path',
        'parent_id',
        'name',
        'is_active',
        'position',
        'level',
        'children',
        'path',
        'available_sort_by',
        'include_in_menu'
    ];

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
        parent::__construct($configuration, $eventManager, $webapiTransport);
        $this->handlerCurl = $handlerCurl;
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
        $url = $_ENV['app_frontend_url'] . 'rest/V1/categories';

        $this->webapiTransport->write($url, $data);
        $response = json_decode($this->webapiTransport->read(), true);
        $this->webapiTransport->close();

        if (empty($response['id'])) {
            $this->eventManager->dispatchEvent(['webapi_failed'], [$response]);
            throw new \Exception('Category creation by webapi handler was not successful!');
        }

        $this->assignProducts($response['id'], $fixture);

        return ['id' => $response['id']];
    }

    /**
     * Prepare category data for webapi.
     *
     * @param FixtureInterface $fixture
     * @return array
     */
    public function prepareData(FixtureInterface $fixture)
    {
        $fixtureData = $this->handlerCurl->prepareData($fixture);
        $data = [];

        $fixtureData = array_merge($fixtureData, $fixtureData['general']);
        unset($fixtureData['general']);
        unset($fixtureData['category_products']);

        foreach ($this->basicFields as $fieldName) {
            if (isset($fixtureData[$fieldName])) {
                $data[$fieldName] = $fixtureData[$fieldName];
            }
        }
        $fixtureData = array_diff_key($fixtureData, array_flip($this->basicFields));

        foreach ($fixtureData as $attribute => $value) {
            $data['custom_attributes'][] = [
                'attribute_code' => $attribute,
                'value' => $value
            ];
        }

        return ['category' => $data];
    }

    /**
     * Assign products to category.
     *
     * @param int $categoryId
     * @param FixtureInterface $fixture
     * @return void
     * @throws \Exception
     */
    protected function assignProducts($categoryId, FixtureInterface $fixture)
    {
        /** @var Category $fixture */
        if ($fixture->hasData('category_products')) {
            $products = $fixture->getDataFieldConfig('category_products')['source']->getProducts();
            $url = $_ENV['app_frontend_url'] . 'rest/V1/categories/' . $categoryId . '/products';

            foreach ($products as $product) {
                $data = [
                    'productLink' => [
                        'sku' => $product->getSku(),
                        'category_id' => $categoryId
                    ]
                ];

                $this->webapiTransport->write($url, $data);
                $response = $this->webapiTransport->read();
                $this->webapiTransport->close();

                if ('true' != $response) {
                    $this->eventManager->dispatchEvent(['webapi_failed'], [$response]);
                    throw new \Exception('Assign product to category was not successful!');
                }
            }
        }
    }
}
