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

namespace Magento\Sitemap\Test\Handler\Sitemap;

use Magento\Sitemap\Test\Handler\Sitemap;
use Mtf\Fixture\FixtureInterface;
use Mtf\Handler\Curl as AbstractCurl;
use Mtf\Util\Protocol\CurlInterface;
use Mtf\Util\Protocol\CurlTransport;
use Mtf\Util\Protocol\CurlTransport\BackendDecorator;
use Mtf\System\Config;
use Magento\Backend\Test\Handler\Extractor;

/**
 * Class Curl
 * Curl handler for creating sitemap
 */
class Curl extends AbstractCurl implements SitemapInterface
{
    /**
     * Default attribute values for fixture
     *
     * @var array
     */
    protected $defaultAttributeValues = ['store_id' => 1];

    /**
     * Post request for creating sitemap
     *
     * @param FixtureInterface $fixture
     * @return array
     * @throws \Exception
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $url = $_ENV['app_backend_url'] . 'admin/sitemap/save/generate/';
        $data = array_merge($this->defaultAttributeValues, $fixture->getData());
        $curl = new BackendDecorator(new CurlTransport(), new Config);
        $curl->addOption(CURLOPT_HEADER, 1);
        $curl->write(CurlInterface::POST, $url, '1.0', array(), $data);
        $response = $curl->read();
        $curl->close();

        if (!strpos($response, 'data-ui-id="messages-message-success"')) {
            throw new \Exception("Sitemap entity creating by curl handler was not successful! Response: $response");
        }

        return ['sitemap_id' => $this->getSitemapId($data)];
    }

    /**
     * Get id after created sitemap
     *
     * @param array $data
     * @return string|null
     */
    protected function getSitemapId(array $data)
    {
        //Sort data in grid to define sitemap id if more than 20 items in grid
        $url = 'admin/sitemap/index/sort/sitemap_id/dir/desc';
        $pattern = '/class=\" col\-id col\-sitemap_id\W*>\W+(\d+)\W+<\/td>\W+<td[\w\s\"=\-]*?>\W+?'
            . $data['sitemap_filename'] . '/siu';
        $extractor = new Extractor($url, $pattern);
        $match = $extractor->getData();

        return empty($match[1]) ? null : $match[1];
    }
}
