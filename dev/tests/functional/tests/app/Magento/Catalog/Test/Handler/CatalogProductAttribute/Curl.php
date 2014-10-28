<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Test\Handler\CatalogProductAttribute;

use Mtf\System\Config;
use Mtf\Fixture\FixtureInterface;
use Mtf\Util\Protocol\CurlInterface;
use Mtf\Util\Protocol\CurlTransport;
use Mtf\Handler\Curl as AbstractCurl;
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
        $curl = new BackendDecorator(new CurlTransport(), new Config);
        $curl->write(CurlInterface::POST, $url, '1.0', [], $data);
        $response = $curl->read();
        $curl->close();

        if (!strpos($response, 'data-ui-id="messages-message-success"')) {
            throw new \Exception("Product Attribute creating by curl handler was not successful!");
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
