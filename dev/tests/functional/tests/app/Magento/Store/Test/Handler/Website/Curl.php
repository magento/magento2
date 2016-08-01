<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Handler\Website;

use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Handler\Curl as AbstractCurl;
use Magento\Mtf\Util\Protocol\CurlInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;
use Magento\Mtf\Config\DataInterface;
use Magento\Mtf\System\Event\EventManagerInterface;
use Magento\Mtf\Util\Command\Website;

/**
 * Curl handler for creating Website.
 */
class Curl extends AbstractCurl implements WebsiteInterface
{
    /**
     * Website folder creation class instance.
     *
     * @var Website
     */
    private $website;

    /**
     * @constructor
     * @param DataInterface $configuration
     * @param EventManagerInterface $eventManager
     * @param Website $website
     */
    public function __construct(
        DataInterface $configuration,
        EventManagerInterface $eventManager,
        Website $website
    ) {
        parent::__construct($configuration, $eventManager);
        $this->website = $website;
    }

    /**
     * POST request for creating Website.
     *
     * @param FixtureInterface|null $fixture [optional]
     * @return array
     * @throws \Exception
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $data = $this->prepareData($fixture);
        $url = $_ENV['app_backend_url'] . 'admin/system_store/save/';
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $curl->write($url, $data);
        $response = $curl->read();
        $curl->close();
        if (!strpos($response, 'data-ui-id="messages-message-success"')) {
            throw new \Exception("Website entity creating by curl handler was not successful! Response: $response");
        }

        $websiteId = $this->getWebSiteIdByWebsiteName($fixture->getName());

        // Creates Website folder in root directory.
        $this->website->create($data['website']['code']);
        $this->setConfiguration($data, $websiteId);

        return ['website_id' => $websiteId];
    }

    /**
     * Get website id by website name.
     *
     * @param string $websiteName
     * @return int
     * @throws \Exception
     */
    protected function getWebSiteIdByWebsiteName($websiteName)
    {
        // Set pager limit to 2000 in order to find created website by name
        $url = $_ENV['app_backend_url'] . 'admin/system_store/index/sort/group_title/dir/asc/limit/2000';
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $curl->addOption(CURLOPT_HEADER, 1);
        $curl->write($url, [], CurlInterface::GET);
        $response = $curl->read();

        $expectedUrl = '/admin/system_store/editWebsite/website_id/';
        $expectedUrl = preg_quote($expectedUrl);
        $expectedUrl = str_replace('/', '\/', $expectedUrl);
        preg_match('/' . $expectedUrl . '([0-9]*)\/(.)*>' . $websiteName . '<\/a>/', $response, $matches);

        if (empty($matches)) {
            throw new \Exception('Cannot find website id.');
        }

        return intval($matches[1]);
    }

    /**
     * Prepare data from text to values.
     *
     * @param FixtureInterface $fixture
     * @return array
     */
    protected function prepareData(FixtureInterface $fixture)
    {
        $data = [
            'website' => $fixture->getData(),
            'store_action' => 'add',
            'store_type' => 'website',
        ];
        $data['website']['website_id'] = isset($data['website']['website_id']) ? $data['website']['website_id'] : '';

        return $data;
    }

    /**
     * Set Website configuration Base url.
     *
     * @param array $data
     * @param int $websiteId
     * @return void
     * @throws \Exception
     */
    private function setConfiguration($data, $websiteId)
    {
        $configData = [
            'groups' => [
                'unsecure' => [
                    'fields' => [
                        'base_link_url' =>
                            [
                                'value' => '{{unsecure_base_url}}websites/' . $data['website']['code'] . '/',
                            ]
                    ]
                ]
            ]
        ];

        $url = $_ENV['app_backend_url'] .
            'admin/system_config/save/section/web/website/' . $websiteId . '/';
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $curl->write($url, $configData);
        $curl->read();
        $curl->close();
    }
}
