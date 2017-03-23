<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Handler\TaxClass;

use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Handler\Webapi as AbstractWebapi;

/**
 * Create Tax Class via Web API.
 */
class Webapi extends AbstractWebapi implements TaxClassInterface
{
    /**
     * Persist Tax Class using Web API handler.
     *
     * @param FixtureInterface $fixture
     * @return array
     * @throws \Exception
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $data['taxClass'] = $fixture->getData();

        $url = $_ENV['app_frontend_url'] . 'rest/V1/taxClasses';
        $this->webapiTransport->write($url, $data);
        $response = json_decode($this->webapiTransport->read(), true);
        $this->webapiTransport->close();

        if (!is_numeric($response)) {
            $this->eventManager->dispatchEvent(['webapi_failed'], [$response]);
            throw new \Exception('Tax class creation by Web API handler was not successful!');
        }

        return ['id' => $response];
    }
}
