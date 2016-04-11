<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\Handler\CmsPage;

use Magento\Backend\Test\Handler\Conditions;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Config\DataInterface;
use Magento\Mtf\System\Event\EventManagerInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Curl handler for creating Cms page.
 */
class Curl extends Conditions implements CmsPageInterface
{
    /**
     * Mapping values for data.
     *
     * @var array
     */
    protected $mappingData = [
        'is_active' => [
            'Enabled' => 1,
            'Disabled' => 0,
        ],
        'store_id' => [
            'All Store Views' => 0,
        ],
        'page_layout' => [
            '1 column' => '1column',
            '2 columns with left bar' => '2columns-left',
            '2 columns with right bar' => '2columns-right',
            '3 columns' => '3columns',
        ]
    ];

    /**
     * Url for save cms page.
     *
     * @var string
     */
    protected $url = 'cms/page/save/back/edit/active_tab/main_section/';

    /**
     * Mapping values for data.
     *
     * @var array
     */
    protected $additionalMappingData = [];

    /**
     * @constructor
     * @param DataInterface $configuration
     * @param EventManagerInterface $eventManager
     */
    public function __construct(DataInterface $configuration, EventManagerInterface $eventManager)
    {
        $this->mappingData = array_merge($this->mappingData, $this->additionalMappingData);
        parent::__construct($configuration, $eventManager);
    }

    /**
     * Post request for creating a cms page.
     *
     * @param FixtureInterface $fixture
     * @return array
     * @throws \Exception
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $url = $_ENV['app_backend_url'] . $this->url;
        $data = $this->prepareData($this->replaceMappingData($fixture->getData()));
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $curl->addOption(CURLOPT_HEADER, 1);
        $curl->write($url, $data);
        $response = $curl->read();
        $curl->close();
        if (!strpos($response, 'data-ui-id="messages-message-success"')) {
            throw new \Exception("Cms page entity creating by curl handler was not successful! Response: $response");
        }
        preg_match("~page_id\/(\d*?)\/~", $response, $matches);
        $id = isset($matches[1]) ? $matches[1] : null;

        return ['page_id' => $id];
    }

    /**
     * Prepare data.
     *
     * @param array $data
     * @return array
     */
    protected function prepareData(array $data)
    {
        $data['stores'] = [$data['store_id']];
        unset($data['store_id']);
        $data['content'] = $data['content']['content'];
        return $data;
    }
}
