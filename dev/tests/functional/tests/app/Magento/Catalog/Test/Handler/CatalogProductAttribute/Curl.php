<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Handler\CatalogProductAttribute;

use Mtf\Fixture\FixtureInterface;
use Mtf\Handler\Curl as AbstractCurl;
use Mtf\System\Config;
use Mtf\Util\Protocol\CurlInterface;
use Mtf\Util\Protocol\CurlTransport;
use Mtf\Util\Protocol\CurlTransport\BackendDecorator;

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
        'is_configurable' => [
            'Yes' => 1,
            'No' => 0,
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
        $data = $this->replaceMappingData($fixture->getData());
        $data['frontend_label'] = [0 => $data['frontend_label']];

        if (isset($data['options'])) {
            foreach ($data['options'] as $key => $values) {
                if ($values['is_default'] == 'Yes') {
                    $data['default'][] = $values['view'];
                }
                $index = 'option_' . $key;
                $data['option']['value'][$index] = [$values['admin'], $values['view']];
                $data['option']['order'][$index] = $key;
            }
            unset($data['options']);
        }

        $url = $_ENV['app_backend_url'] . 'catalog/product_attribute/save/back/edit';
        $curl = new BackendDecorator(new CurlTransport(), new Config());
        $curl->write(CurlInterface::POST, $url, '1.0', [], $data);
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
            foreach ($matches[1] as $key => $optionId) {
                $optionsData[$key]['id'] = $optionId;
            }
            $resultData['options'] = $optionsData;
        }

        return $resultData;
    }
}
