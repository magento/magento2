<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Handler\Curl;

use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Handler\Curl;
use Magento\Mtf\Util\Protocol\CurlInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Curl handler remove all tax rules
 *
 */
class RemoveTaxRule extends Curl
{
    /**
     * Default EN_US message after removing tax rule
     */
    const TAX_RULE_REMOVE_MESSAGE = 'The tax rule has been deleted';

    /**
     * @var string
     */
    protected $taxRuleGridUrl;

    /**
     * Entry point for handler
     *
     * @param FixtureInterface $fixture [optional]
     * @return mixed|string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $this->taxRuleGridUrl = $_ENV['app_backend_url'] . 'tax/rule/index/';
        $curl = $this->getCurl($this->taxRuleGridUrl);
        $response = $curl->read();
        $this->removeTaxRules($response);
        $curl->close();
        return $response;
    }

    /**
     * Prepare and return curl object
     *
     * @param string $url
     * @return BackendDecorator
     */
    protected function getCurl($url)
    {
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $curl->write($url, [], CurlInterface::GET);
        return $curl;
    }

    /**
     * Recursively remove tax rules
     *
     * @param string $data
     * @return mixed
     */
    protected function removeTaxRules($data)
    {
        preg_match_all("!tax\/rule\/edit\/rule\/([\d]+)!", $data, $result);
        if (!isset($result[1]) || empty($result[1])) {
            return null;
        }
        foreach ($result[1] as $taxRuleId) {
            $this->_deleteTaxRuleRequest((int)$taxRuleId);
            break;
        }

        $curl = $this->getCurl($this->taxRuleGridUrl);
        $response = $curl->read();
        $curl->close();
        return $this->removeTaxRules($response);
    }

    /**
     * Make request to delete tax rule
     *
     * @param int $taxRuleId
     */
    protected function deleteTaxRuleRequest($taxRuleId)
    {
        $url = $_ENV['app_backend_url'] . 'tax/rule/delete/rule/' . (int) $taxRuleId;
        $curl = $this->getCurl($url);
        $response = $curl->read();
        $this->checkMessage($response, $taxRuleId);
        $curl->close();
    }

    /**
     * Validation for successfully deleted tax rule
     *
     * @param string $data
     * @param int $taxRuleId
     * @throws \RuntimeException
     */
    protected function checkMessage($data, $taxRuleId)
    {
        preg_match_all('!(' . static::TAX_RULE_REMOVE_MESSAGE . ')!', $data, $result);
        if (!isset($result[1]) || empty($result[1])) {
            throw new \RuntimeException('Tax rule ID ' . $taxRuleId . 'not removed!');
        }
    }
}
