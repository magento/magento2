<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Core\Test\Handler\ConfigData;

use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Handler\Curl as AbstractCurl;
use Magento\Mtf\Config;
use Magento\Mtf\Util\Protocol\CurlInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Class Curl
 * Setting config
 */
class Curl extends AbstractCurl implements ConfigDataInterface
{
    /**
     * Mapping values for data.
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
     * Post request for setting configuration.
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
     * Prepare POST data for setting configuration.
     *
     * @param FixtureInterface $fixture
     * @return array
     */
    protected function prepareData(FixtureInterface $fixture)
    {
        $configPath = [];
        $result = [];
        $fields = $fixture->getData();
        if (isset($fields['section'])) {
            foreach ($fields['section'] as $itemSection) {
                parse_str($this->prepareConfigPath($itemSection), $configPath);
                $result = array_merge_recursive($result, $configPath);
            }
        }
        return $result;
    }

    /**
     * Prepare config path.
     *
     * From payment/cashondelivery/active to ['payment']['groups']['cashondelivery']['fields']['active']
     *
     * @param array $input
     * @return string
     */
    protected function prepareConfigPath(array $input)
    {
        $resultArray = '';
        $path = explode('/', $input['path']);
        foreach ($path as $position => $subPath) {
            if ($position === 0) {
                $resultArray.= $subPath;
                continue;
            } elseif ($position === (count($path) - 1)) {
                $resultArray.= '[fields]';
            } else {
                $resultArray.= '[groups]';
            }
            $resultArray.= '[' . $subPath .']';
        }
        $resultArray.= '[value]';
        if (is_array($input['value'])) {
            $resultArray.= '[' . key($input['value']) . ']';
            $resultArray.= '=' . current($input['value']);
        } else {
            $resultArray.= '=' . $input['value'];
        }
        return $resultArray;
    }

    /**
     * Apply config settings via curl.
     *
     * @param array $data
     * @param string $section
     * @throws \Exception
     */
    protected function applyConfigSettings(array $data, $section)
    {
        $url = $this->getUrl($section);
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $curl->addOption(CURLOPT_HEADER, 1);
        $curl->write(CurlInterface::POST, $url, '1.0', [], $data);
        $response = $curl->read();
        $curl->close();

        if (strpos($response, 'data-ui-id="messages-message-success"') === false) {
            throw new \Exception("Settings are not applied! Response: $response");
        }
    }

    /**
     * Retrieve URL for request.
     *
     * @param string $section
     * @return string
     */
    protected function getUrl($section)
    {
        return $_ENV['app_backend_url'] .
        'admin/system_config/save/section/' . $section;
    }
}
