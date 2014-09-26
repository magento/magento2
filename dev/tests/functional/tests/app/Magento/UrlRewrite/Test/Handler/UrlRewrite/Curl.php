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

namespace Magento\UrlRewrite\Test\Handler\UrlRewrite;

use Mtf\Fixture\FixtureInterface;
use Mtf\Handler\Curl as AbstractCurl;
use Mtf\Util\Protocol\CurlInterface;
use Mtf\Util\Protocol\CurlTransport;
use Mtf\Util\Protocol\CurlTransport\BackendDecorator;
use Mtf\System\Config;

/**
 * Class Curl
 * Create url rewrite
 */
class Curl extends AbstractCurl implements UrlRewriteInterface
{
    /**
     * Data mapping
     *
     * @var array
     */
    protected $mappingData = [
        'store_id' => [
            'Default Store View' => 1,
            'Main Website/Main Website Store/Default Store View' => 1,
        ],
        'redirect_type' => [
            'Temporary (302)' => 302,
            'Permanent (301)' => 301,
            'No' => 0
        ]
    ];

    /**
     * Url for save rewrite
     *
     * @var string
     */
    protected $url = 'admin/url_rewrite/save/';

    /**
     * Post request for creating url rewrite
     *
     * @param FixtureInterface $fixture
     * @throws \Exception
     * @return void
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $url = $_ENV['app_backend_url'] . $this->url . $fixture->getIdPath();
        $data = $this->replaceMappingData($fixture->getData());
        $curl = new BackendDecorator(new CurlTransport(), new Config());
        $curl->write(CurlInterface::POST, $url, '1.0', [], $data);
        $response = $curl->read();

        if (!strpos($response, 'data-ui-id="messages-message-success"')) {
            throw new \Exception("URL Rewrite creation by curl handler was not successful! Response: $response");
        }
        $curl->close();
    }
}
