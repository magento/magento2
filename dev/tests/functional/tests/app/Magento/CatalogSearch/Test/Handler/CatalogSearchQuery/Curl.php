<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Handler\CatalogSearchQuery;

use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Handler\Curl as AbstractCurl;
use Magento\Mtf\Util\Protocol\CurlInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Create new search term via curl.
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
            'Main Website/Main Website Store/Default Store View' => 1,
        ],
    ];

    /**
     * Search term url.
     *
     * @var string
     */
    protected $url = 'search/term/';

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
     * Add new search term.
     *
     * @param array $data
     */
    protected function addNewSearchTerm(array $data)
    {
        $url = $_ENV['app_backend_url'] . $this->url . 'save';
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $curl->write($url, $data);
        $curl->read();
        $curl->close();
    }

    /**
     * Getting search term id.
     *
     * @param string $queryText
     * @return int
     * @throws \Exception
     */
    protected function getNewSearchTermId($queryText)
    {
        $filter = base64_encode('search_query=' . $queryText);
        $url = $_ENV['app_backend_url'] . $this->url . 'index/filter/' . $filter;
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $curl->write($url, [], CurlInterface::GET);
        $response = $curl->read();
        $curl->close();

        if (!preg_match('#' . $this->url . 'edit/id/(\d+)/"#', $response, $matches)) {
            throw new \Exception('Search term not found in grid!');
        }

        return (int)$matches[1];
    }
}
