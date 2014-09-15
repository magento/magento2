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

namespace Magento\Cms\Test\Handler\CmsPage;

use Mtf\Fixture\FixtureInterface;
use Magento\Backend\Test\Handler\Conditions;
use Mtf\Util\Protocol\CurlInterface;
use Mtf\Util\Protocol\CurlTransport;
use Mtf\Util\Protocol\CurlTransport\BackendDecorator;
use Mtf\System\Config;

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
            'Disabled' => 0
        ],
        'store_id' => [
            'All Store Views' => 0,
        ],
        'page_layout' => [
            '1 column' => '1column',
            '2 columns with left bar' => '2columns-left',
            '2 columns with right bar' => '2columns-right',
            '3 columns' => '3columns'
        ],
        'under_version_control' => [
            'Yes' => 1,
            'No' => 0
        ]
    ];

    /**
     * Url for save cms page
     *
     * @var string
     */
    protected $url = 'cms/page/save/back/edit/active_tab/content_section/';

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
