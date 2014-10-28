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

namespace Magento\CatalogSearch\Test\Handler\CatalogSearchQuery;

use Mtf\System\Config;
use Mtf\Fixture\FixtureInterface;
use Mtf\Util\Protocol\CurlInterface;
use Mtf\Util\Protocol\CurlTransport;
use Mtf\Util\Protocol\CurlTransport\BackendDecorator;
use Mtf\Handler\Curl as AbstractCurl;

/**
 * Class Curl
 * Create new search term via curl
 */
class Curl extends AbstractCurl implements CatalogSearchQueryInterface
{
    /**
     * Mapping values for data.
     *
     * @var array
     */
    protected $mappingData = [
        'display_in_terms' => [
            'No' => 0,
        ],
        'store_id' => [
            'Main Website/Main Website Store/Default Store View' => 1
        ]
    ];

    /**
     * Post request for creating search term
     *
     * @param FixtureInterface $fixture|null [optional]
     * @return array
     * @throws \Exception
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $data = $this->replaceMappingData($fixture->getData());
        $this->addNewSearchTerm($data);

        return ['id' => $this->getNewSearchTermId($data['query_text'])];
    }

    /**
     * Add new search term
     *
     * @param array $data
     */
    protected function addNewSearchTerm(array $data)
    {
        $url = $_ENV['app_backend_url'] . 'catalog/search/save';
        $curl = new BackendDecorator(new CurlTransport(), new Config);
        $curl->write(CurlInterface::POST, $url, '1.0', [], $data);
        $curl->read();
        $curl->close();
    }

    /**
     * Getting search term id
     *
     * @param string $queryText
     * @return int
     * @throws \Exception
     */
    protected function getNewSearchTermId($queryText)
    {
        $filter = base64_encode('search_query=' . $queryText);
        $url = $_ENV['app_backend_url'] . 'catalog/search/index/filter/' . $filter;
        $curl = new BackendDecorator(new CurlTransport(), new Config);
        $curl->write(CurlInterface::GET, $url, '1.0');
        $response = $curl->read();
        $curl->close();

        if (!preg_match('#search/edit/id/(\d+)/"#', $response, $matches)) {
            throw new \Exception('Search term not found in grid!');
        }

        return (int)$matches[1];
    }
}
