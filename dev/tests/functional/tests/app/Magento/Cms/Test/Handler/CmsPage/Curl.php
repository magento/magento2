<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\Handler\CmsPage;

use Magento\Backend\Test\Handler\Conditions;
use Mtf\Fixture\FixtureInterface;
use Mtf\System\Config;
use Mtf\Util\Protocol\CurlInterface;
use Mtf\Util\Protocol\CurlTransport;
use Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Class Curl
 * Curl handler for creating Cms page
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
            'Published' => 1,
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
        ],
        'under_version_control' => [
            'Yes' => 1,
            'No' => 0,
        ],
    ];

    /**
     * Url for save cms page
     *
     * @var string
     */
    protected $url = 'admin/cms_page/save/back/edit/active_tab/main_section/';

    /**
     * Post request for creating a cms page
     *
     * @param FixtureInterface $fixture
     * @return array
     * @throws \Exception
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $url = $_ENV['app_backend_url'] . $this->url;
        $data = $this->prepareData($this->replaceMappingData($fixture->getData()));
        $curl = new BackendDecorator(new CurlTransport(), new Config());
        $curl->addOption(CURLOPT_HEADER, 1);
        $curl->write(CurlInterface::POST, $url, '1.0', [], $data);
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
     * Prepare data
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
