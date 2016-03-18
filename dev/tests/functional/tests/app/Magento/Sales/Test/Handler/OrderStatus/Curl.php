<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Handler\OrderStatus;

use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Handler\Curl as AbstractCurl;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Curl handler for creating OrderStatus.
 */
class Curl extends AbstractCurl implements OrderStatusInterface
{
    /**
     * Default attribute values for fixture.
     *
     * @var array
     */
    protected $defaultAttributeValues = [
        'is_new' => 1,
        'store_labels[1]' => '',
    ];

    /**
     * Mapping values for data.
     *
     * @var array
     */
    protected $mappingData = [
        'state' => [
            'Pending' => 'new',
        ],
        'is_default' => [
            'Yes' => 1,
            'No' => 0,
        ],
        'visible_on_front' => [
            'Yes' => 1,
            'No' => 0,
        ],
    ];

    /**
     * Post request for creating OrderStatus.
     *
     * @param FixtureInterface $fixture
     * @return void
     * @throws \Exception
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $url = $_ENV['app_backend_url'] . 'sales/order_status/save/';
        $data = array_merge($this->defaultAttributeValues, $fixture->getData());
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $curl->write($url, $data);
        $response = $curl->read();
        $curl->close();

        if (!strpos($response, 'data-ui-id="messages-message-success"')) {
            throw new \Exception("OrderStatus entity creating by curl handler was not successful! Response: $response");
        }

        if (isset($data['state'])) {
            $url = $_ENV['app_backend_url'] . 'sales/order_status/assignPost/';
            $data = $this->replaceMappingData($data);
            $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
            $curl->write($url, $data);
            $response = $curl->read();
            $curl->close();

            if (!strpos($response, 'data-ui-id="messages-message-success"')) {
                throw new \Exception(
                    "Assigning OrderStatus entity by curl handler was not successful! Response: $response"
                );
            }
        }
    }
}
