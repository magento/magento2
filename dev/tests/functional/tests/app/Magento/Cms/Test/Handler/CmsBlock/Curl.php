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

namespace Magento\Cms\Test\Handler\CmsBlock;

use Mtf\System\Config;
use Mtf\Fixture\FixtureInterface;
use Mtf\Util\Protocol\CurlInterface;
use Mtf\Util\Protocol\CurlTransport;
use Magento\Backend\Test\Handler\Extractor;
use Mtf\Util\Protocol\CurlTransport\BackendDecorator;
use Mtf\Handler\Curl as AbstractCurl;

/**
 * Class Curl
 * Curl handler for creating CMS Block
 */
class Curl extends AbstractCurl implements CmsBlockInterface
{
    /**
     * Url for saving data
     *
     * @var string
     */
    protected $saveUrl = 'cms/block/save';

    /**
     * Mapping values for data
     *
     * @var array
     */
    protected $mappingData = [
        'is_active' => [
            'Enabled' => 1,
            'Disabled' => 0,
        ],
    ];

    /**
     * Mapping values for Stores
     *
     * @var array
     */
    protected $stores = [
        'All Store Views' => 0
    ];

    /**
     * POST request for creating CMS Block
     *
     * @param FixtureInterface|null $fixture [optional]
     * @return array
     * @throws \Exception
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $data = $this->prepareData($fixture);
        $url = $_ENV['app_backend_url'] . $this->saveUrl;
        $curl = new BackendDecorator(new CurlTransport(), new Config());
        $curl->write(CurlInterface::POST, $url, '1.0', [], $data);
        $response = $curl->read();
        $curl->close();
        if (!strpos($response, 'data-ui-id="messages-message-success"')) {
            throw new \Exception("CMS Block entity creating by curl handler was not successful! Response: $response");
        }

        $url = 'cms/block/index/sort/creation_time/dir/desc';
        $regExpPattern = '@^.*block_id\/(\d+)\/.*' . $fixture->getTitle() . '@ms';
        $extractor = new Extractor($url, $regExpPattern);

        return ['block_id' => $extractor->getData()[1]];
    }

    /**
     * Prepare data from text to values
     *
     * @param FixtureInterface $fixture
     * @return array
     */
    protected function prepareData($fixture)
    {
        $data = $this->replaceMappingData($fixture->getData());
        if (isset($data['stores'])) {
            $stores = [];
            foreach ($data['stores'] as $store) {
                $stores[] = isset($this->stores[$store]) ? $this->stores[$store] : $store;
            }
            $data['stores'] = $stores;
        }

        return $data;
    }
}
