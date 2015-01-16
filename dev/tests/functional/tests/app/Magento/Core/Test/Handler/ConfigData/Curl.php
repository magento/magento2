<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Core\Test\Handler\ConfigData;

use Mtf\Fixture\FixtureInterface;
use Mtf\Handler\Curl as AbstractCurl;
use Mtf\System\Config;
use Mtf\Util\Protocol\CurlInterface;
use Mtf\Util\Protocol\CurlTransport;
use Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Class Curl
 * Setting config
 */
class Curl extends AbstractCurl implements ConfigDataInterface
{
    /**
     * Mapping values for data
     *
     * @var array
     */
    protected $mappingData = [
        'scope' => [
            'Website' => 'website',
            'Store' => 'group',
            'Store View' => 'store',
        ],
    ];

    /**
     * Post request for setting configuration
     *
     * @param FixtureInterface|null $fixture [optional]
     * @return void
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $data = $this->prepareData($fixture);
        foreach ($data as $scope => $item) {
            $this->applyConfigSettings($item, $scope);
        }
    }

    /**
     * Prepare POST data for setting configuration
     *
     * @param FixtureInterface $fixture
     * @return array
     */
    protected function prepareData(FixtureInterface $fixture)
    {
        $result = [];
        $fields = $fixture->getData();
        if (isset($fields['section'])) {
            foreach ($fields['section'] as $itemSection) {
                list($scope, $group, $field) = explode('/', $itemSection['path']);
                $value = isset($this->mappingData[$field])
                    ? $this->mappingData[$field][$itemSection['value']]
                    : $itemSection['value'];
                $result[$scope]['groups'][$group]['fields'][$field]['value'] = $value;
            }
        }
        return $result;
    }

    /**
     * Apply config settings via curl
     *
     * @param array $data
     * @param string $section
     * @throws \Exception
     */
    protected function applyConfigSettings(array $data, $section)
    {
        $url = $this->getUrl($section);
        $curl = new BackendDecorator(new CurlTransport(), new Config());
        $curl->addOption(CURLOPT_HEADER, 1);
        $curl->write(CurlInterface::POST, $url, '1.0', [], $data);
        $response = $curl->read();
        $curl->close();

        if (strpos($response, 'data-ui-id="messages-message-success"') === false) {
            throw new \Exception("Settings are not applied! Response: $response");
        }
    }

    /**
     * Retrieve URL for request
     *
     * @param string $section
     * @return string
     */
    protected function getUrl($section)
    {
        return $_ENV['app_backend_url'] . 'admin/system_config/save/section/' . $section;
    }
}
