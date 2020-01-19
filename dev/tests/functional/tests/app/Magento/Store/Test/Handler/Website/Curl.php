<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Handler\Website;

use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Handler\Curl as AbstractCurl;
use Magento\Mtf\Util\Protocol\CurlInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;
use Magento\Mtf\Config\DataInterface;
use Magento\Mtf\System\Event\EventManagerInterface;
use Magento\Mtf\Util\Command\Website;
use Magento\Store\Test\Fixture\Website as WebsiteFixture;

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
     * Website fixture.
     *
     * @var WebsiteFixture
     */
    private $fixture;

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * @constructor
     * @param DataInterface $configuration
     * @param EventManagerInterface $eventManager
     * @param Website $website
     * @param FixtureFactory $fixtureFactory
     */
    public function __construct(
        DataInterface $configuration,
        EventManagerInterface $eventManager,
        Website $website,
        FixtureFactory $fixtureFactory
    ) {
        parent::__construct($configuration, $eventManager);
        $this->website = $website;
        $this->fixtureFactory = $fixtureFactory;
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
        if (strpos($response, 'data-ui-id="messages-message-success"') === false) {
            throw new \Exception("Website entity creating by curl handler was not successful! Response: $response");
        }

        $websiteId = $this->getWebSiteIdByWebsiteName($fixture->getName());

        // Update website fixture data.
        $this->fixture = $this->fixtureFactory->createByCode(
            'website',
            ['data' => array_merge($fixture->getData(), ['website_id' => $websiteId])]
        );
        // Creates Website folder in root directory.
        $this->website->create($data['website']['code']);
        $this->setConfiguration($data);

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

        return (int)$matches[1];
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
     * @return void
     * @throws \Exception
     */
    private function setConfiguration(array $data)
    {
        $configData = [
            'web/unsecure/base_link_url' => [
                'value' => '{{unsecure_base_url}}websites/' . $data['website']['code'] . '/'
            ],
            'scope' => ['fixture' => $this->fixture, 'scope_type' => 'website', 'set_level' => 'website']
        ];

        $configFixture = $this->fixtureFactory->createByCode('configData', ['data' => $configData]);
        $configFixture->persist();
    }
}
