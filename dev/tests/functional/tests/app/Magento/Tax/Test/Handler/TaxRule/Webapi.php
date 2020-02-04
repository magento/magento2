<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Handler\TaxRule;

use Magento\Tax\Test\Fixture\TaxRule;
use Magento\Mtf\Config\DataInterface;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Handler\Webapi as AbstractWebapi;
use Magento\Mtf\System\Event\EventManagerInterface;
use Magento\Mtf\Util\Protocol\CurlTransport\WebapiDecorator;

/**
 * Create Tax Rule via Web API handler.
 */
class Webapi extends AbstractWebapi implements TaxRuleInterface
{
    /**
     * Tax Rule cUrl handler.
     *
     * @var Curl
     */
    protected $taxRuleCurl;

    /**
     * @constructor
     * @param DataInterface $configuration
     * @param EventManagerInterface $eventManager
     * @param WebapiDecorator $webapiTransport
     * @param Curl $taxRuleCurl
     */
    public function __construct(
        DataInterface $configuration,
        EventManagerInterface $eventManager,
        WebapiDecorator $webapiTransport,
        Curl $taxRuleCurl
    ) {
        parent::__construct($configuration, $eventManager, $webapiTransport);
        $this->taxRuleCurl = $taxRuleCurl;
    }

    /**
     * Web API request for creating Tax Rule.
     *
     * @param FixtureInterface $fixture
     * @return array
     * @throws \Exception
     */
    public function persist(FixtureInterface $fixture = null)
    {
        /** @var TaxRule $fixture */
        $data = $this->prepareData($fixture);

        $url = $_ENV['app_frontend_url'] . 'rest/V1/taxRules';
        $this->webapiTransport->write($url, $data);
        $response = json_decode($this->webapiTransport->read(), true);
        $this->webapiTransport->close();

        if (empty($response['id'])) {
            $this->eventManager->dispatchEvent(['webapi_failed'], [$response]);
            throw new \Exception('Tax rule creation by Web API handler was not successful!');
        }

        return ['id' => $response['id']];
    }

    /**
     * Returns data for Web API params.
     *
     * @param TaxRule $fixture
     * @return array
     */
    protected function prepareData(TaxRule $fixture)
    {
        $data = $fixture->getData();
        $data = $this->taxRuleCurl->prepareFieldData($fixture, $data, 'tax_rate', 'tax_rate_ids');
        $data = $this->taxRuleCurl->prepareFieldData($fixture, $data, 'tax_product_class', 'product_tax_class_ids');
        $data = $this->taxRuleCurl->prepareFieldData($fixture, $data, 'tax_customer_class', 'customer_tax_class_ids');

        return ['rule' => $data];
    }
}
