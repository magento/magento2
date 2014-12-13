<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Customer\Test\Handler\Curl;

use Mtf\Fixture\FixtureInterface;
use Mtf\Handler\Curl;
use Mtf\System\Config;
use Mtf\Util\Protocol\CurlInterface;
use Mtf\Util\Protocol\CurlTransport;
use Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Class RemoveCustomerGroup
 * Curl handler for remove customer group
 */
class RemoveCustomerGroup extends Curl
{
    /**
     * Url for saving data
     *
     * @var string
     */
    protected $deleteUrl = 'customer/group/delete/id/%s/';

    /**
     * Execute handler
     *
     * @param FixtureInterface $fixture [optional]
     * @return mixed
     */
    public function persist(FixtureInterface $fixture = null)
    {
        /** @var \Magento\Customer\Test\Fixture\VatGroup $fixture */
        $groups = $fixture->getGroupsIds();
        $curl = new BackendDecorator(new CurlTransport(), new Config());
        $curl->addOption(CURLOPT_HEADER, 1);
        $response = '';
        foreach ($groups as $groupId) {
            $url = sprintf($_ENV['app_backend_url'] . $this->deleteUrl, $groupId);
            $curl->write(CurlInterface::GET, $url, '1.0', []);
            $response = $curl->read();
        }
        $curl->close();
        return $response;
    }
}
