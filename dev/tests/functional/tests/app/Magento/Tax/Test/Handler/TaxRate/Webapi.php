<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Handler\TaxRate;

use Magento\Tax\Test\Fixture\TaxRate;
use Magento\Mtf\Config\DataInterface;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Handler\Webapi as AbstractWebapi;
use Magento\Mtf\System\Event\EventManagerInterface;
use Magento\Mtf\Util\Protocol\CurlTransport\WebapiDecorator;

/**
 * Create Tax Rate via Web API.
 */
class Webapi extends AbstractWebapi implements TaxRateInterface
{
    /**
     * Tax Rate cUrl handler.
     *
     * @var Curl
     */
    protected $taxRateCurl;

    /**
     * @constructor
     * @param DataInterface $configuration
     * @param EventManagerInterface $eventManager
     * @param WebapiDecorator $webapiTransport
     * @param Curl $taxRateCurl
     */
    public function __construct(
        DataInterface $configuration,
        EventManagerInterface $eventManager,
        WebapiDecorator $webapiTransport,
        Curl $taxRateCurl
    ) {
        parent::__construct($configuration, $eventManager, $webapiTransport);
        $this->taxRateCurl = $taxRateCurl;
    }

    /**
     * Persist Tax Rate using Web API handler.
     *
     * @param FixtureInterface $fixture
     * @return array
     * @throws \Exception
     */
    public function persist(FixtureInterface $fixture = null)
    {
        /** @var TaxRate $fixture */
        $data['tax_rate'] = $this->taxRateCurl->prepareData($fixture);

        $url = $_ENV['app_frontend_url'] . 'rest/V1/taxRates';
        $this->webapiTransport->write($url, $data);
        $response = json_decode($this->webapiTransport->read(), true);
        $this->webapiTransport->close();

        if (empty($response['id'])) {
            $this->eventManager->dispatchEvent(['webapi_failed'], [$response]);
            throw new \Exception('Tax rate creation by Web API handler was not successful!');
        }

        return ['id' => $response['id']];
    }
}
