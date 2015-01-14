<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Handler\Curl;

use Mtf\Fixture\FixtureInterface;
use Mtf\Handler\Curl;
use Mtf\System\Config;
use Mtf\Util\Protocol\CurlInterface;
use Mtf\Util\Protocol\CurlTransport;
use Mtf\Util\Protocol\CurlTransport\BackendDecorator;

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
    protected $_taxRuleGridUrl;

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
        $this->_taxRuleGridUrl = $_ENV['app_backend_url'] . 'tax/rule/index/';
        $curl = $this->_getCurl($this->_taxRuleGridUrl);
        $response = $curl->read();
        $this->_removeTaxRules($response);
        $curl->close();
        return $response;
    }

    /**
     * Prepare and return curl object
     *
     * @param string $url
     * @return BackendDecorator
     */
    protected function _getCurl($url)
    {
        $curl = new BackendDecorator(new CurlTransport(), new Config());
        $curl->write(CurlInterface::POST, $url, '1.0');
        return $curl;
    }

    /**
     * Recursively remove tax rules
     *
     * @param string $data
     */
    protected function _removeTaxRules($data)
    {
        preg_match_all("!tax\/rule\/edit\/rule\/([\d]+)!", $data, $result);
        if (!isset($result[1]) || empty($result[1])) {
            return null;
        }
        foreach ($result[1] as $taxRuleId) {
            $this->_deleteTaxRuleRequest((int)$taxRuleId);
            break;
        }

        $curl = $this->_getCurl($this->_taxRuleGridUrl);
        $response = $curl->read();
        $curl->close();
        return $this->_removeTaxRules($response);
    }

    /**
     * Make request to delete tax rule
     *
     * @param int $taxRuleId
     */
    protected function _deleteTaxRuleRequest($taxRuleId)
    {
        $url = $_ENV['app_backend_url'] . 'tax/rule/delete/rule/' . (int) $taxRuleId;
        $curl = $this->_getCurl($url);
        $response = $curl->read();
        $this->_checkMessage($response, $taxRuleId);
        $curl->close();
    }

    /**
     * Validation for successfully deleted tax rule
     *
     * @param string $data
     * @param int $taxRuleId
     * @throws \RuntimeException
     */
    protected function _checkMessage($data, $taxRuleId)
    {
        preg_match_all('!(' . static::TAX_RULE_REMOVE_MESSAGE . ')!', $data, $result);
        if (!isset($result[1]) || empty($result[1])) {
            throw new \RuntimeException('Tax rule ID ' . $taxRuleId . 'not removed!');
        }
    }
}
