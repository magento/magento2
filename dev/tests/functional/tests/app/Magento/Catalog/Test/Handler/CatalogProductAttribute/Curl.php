<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Handler\CatalogProductAttribute;

use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Handler\Curl as AbstractCurl;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Class Curl
 * Create new Product Attribute via curl
 */
class Curl extends AbstractCurl implements CatalogProductAttributeInterface
{
    /**
     * Mapping values for data.
     *
     * @var array
     */
    protected $mappingData = [
        'frontend_input' => [
            'Text Field' => 'text',
            'Text Area' => 'textarea',
            'Date' => 'date',
            'Yes/No' => 'boolean',
            'Multiple Select' => 'multiselect',
            'Dropdown' => 'select',
            'Price' => 'price',
            'Media Image' => 'media_image',
            'Fixed Product Tax' => 'weee',
        ],
        'is_required' => [
            'Yes' => 1,
            'No' => 0,
        ],
        'is_filterable' => [
            'No' => 0,
            'Filterable (with results)' => 1,
            'Filterable (no results)' => 2
        ],
        'is_used_for_promo_rules' => [
            'No' => 0,
            'Yes' => 1,
        ],
    ];

    /**
     * Post request for creating Product Attribute
     *
     * @param FixtureInterface|null $fixture [optional]
     * @return array
     * @throws \Exception
     */
    public function persist(FixtureInterface $fixture = null)
    {
        if ($fixture->hasData('attribute_id')) {
            return ['attribute_id' => $fixture->getData('attribute_id')];
        }
        $data = $this->replaceMappingData($fixture->getData());
        $data['frontend_label'] = [0 => $data['frontend_label']];

        if (isset($data['options'])) {
            foreach ($data['options'] as $key => $values) {
                $index = 'option_' . $key;
                if ($values['is_default'] == 'Yes') {
                    $data['default'][] = $index;
                }
                $data['option']['value'][$index] = [$values['admin'], $values['view']];
                $data['option']['order'][$index] = $key;
            }
            unset($data['options']);
        }

        $url = $_ENV['app_backend_url'] . 'catalog/product_attribute/save/back/edit';
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $curl->write($url, $data);
        $response = $curl->read();
        $curl->close();

        if (!strpos($response, 'data-ui-id="messages-message-success"')) {
            throw new \Exception("Product Attribute creating by curl handler was not successful! \n" . $response);
        }

        $resultData = [];
        $matches = [];
        preg_match('#attribute_id[^>]+value="(\d+)"#', $response, $matches);
        $resultData['attribute_id'] = $matches[1];

        $matches = [];
        preg_match_all('#"id":"(\d+)"#Umi', $response, $matches);

        if ($fixture->hasData('options')) {
            $optionsData = $fixture->getData()['options'];
            foreach (array_unique($matches[1]) as $key => $optionId) {
                $optionsData[$key]['id'] = $optionId;
            }
            $resultData['options'] = $optionsData;
        }

        return $resultData;
    }
}
