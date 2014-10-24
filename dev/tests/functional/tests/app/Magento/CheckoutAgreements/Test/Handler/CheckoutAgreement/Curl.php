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

namespace Magento\CheckoutAgreements\Test\Handler\CheckoutAgreement;

use Mtf\System\Config;
use Mtf\Fixture\FixtureInterface;
use Mtf\Util\Protocol\CurlInterface;
use Mtf\Util\Protocol\CurlTransport;
use Mtf\Handler\Curl as AbstractCurl;
use Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Class Curl
 * Curl handler for creating Checkout Agreement
 */
class Curl extends AbstractCurl implements CheckoutAgreementInterface
{
    /**
     * Mapping values for data.
     *
     * @var array
     */
    protected $mappingData = [
        'is_active' => [
            'Enabled' => 1,
            'Disabled' => 0
        ],
        'is_html' => [
            'HTML' => 1,
            'Text' => 0
        ],
    ];

    /**
     * Url for save checkout agreement
     *
     * @var string
     */
    protected $url = 'checkout/agreement/save/';

    /**
     * Post request for creating new checkout agreement
     *
     * @param FixtureInterface|null $fixture
     * @return array
     * @throws \Exception
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $url = $_ENV['app_backend_url'] . $this->url;
        $data = $this->prepareData($fixture);
        $curl = new BackendDecorator(new CurlTransport(), new Config());
        $curl->write(CurlInterface::POST, $url, '1.1', [], $data);
        $response = $curl->read();
        $curl->close();
        if (!strpos($response, 'data-ui-id="messages-message-success"')) {
            throw new \Exception("Checkout agreement creating by curl handler was not successful! Response: $response");
        }
        preg_match('~id\/(\d*?)\/~', $response, $matches);
        $id = isset($matches[1]) ? $matches[1] : null;

        return ['agreement_id' => $id];
    }

    /**
     * Prepare data
     *
     * @param FixtureInterface $fixture
     * @return array
     */
    protected function prepareData($fixture)
    {
        $data = [];
        /** @var \Magento\CheckoutAgreements\Test\Fixture\CheckoutAgreement $fixture */
        $stores = $fixture->getDataFieldConfig('stores')['source']->getStores();
        foreach ($stores as $store) {
            /** @var \Magento\Store\Test\Fixture\Store $store */
            $data['stores'][] = $store->getStoreId();
        }
        $data = $this->replaceMappingData(array_merge($fixture->getData(), $data));

        return $data;
    }
}
