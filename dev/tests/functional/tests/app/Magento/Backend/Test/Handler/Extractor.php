<?php

/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Handler;

use Magento\Mtf\Util\Protocol\CurlInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Class Extractor
 * Used to omit possible issue, when searched Id is not on the same page in cURL response
 */
class Extractor
{
    /**
     * Pattern for searching grid table in cURL response
     *
     * @var string
     */
    protected $regExpPattern;

    /**
     * Url of cURL request
     *
     * @var string
     */
    protected $url;

    /**
     * Flag is search all match
     *
     * @var bool
     */
    protected $isAll;

    /**
     * Setting all Pagination params for Pagination object.
     * Required url for cURL request and regexp pattern for searching in cURL response.
     *
     * @param string $url
     * @param string $regExpPattern
     * @param bool $isAll
     */
    public function __construct($url, $regExpPattern, $isAll = false)
    {
        $this->url = $url;
        $this->regExpPattern = $regExpPattern;
        $this->isAll = $isAll;
    }

    /**
     * Retrieves data from cURL response
     *
     * @throws \Exception
     * @return array
     */
    public function getData()
    {
        /** @var \Magento\Mtf\Config\DataInterface $config */
        $config = \Magento\Mtf\ObjectManagerFactory::getObjectManager()->get('Magento\Mtf\Config\DataInterface');
        $url = $_ENV['app_backend_url'] . $this->url;
        $curl = new BackendDecorator(new CurlTransport(), $config);
        $curl->addOption(CURLOPT_HEADER, 1);
        $curl->write($url, [], CurlInterface::GET);
        $response = $curl->read();
        $curl->close();
        if ($this->isAll) {
            preg_match_all($this->regExpPattern, $response, $matches);
        } else {
            preg_match($this->regExpPattern, $response, $matches);
        }

        $countMatches = $this->isAll ? count($matches[1]) : count($matches);
        if ($countMatches == 0) {
            throw new \Exception('Matches array can\'t be empty.');
        }
        return $matches;
    }
}
