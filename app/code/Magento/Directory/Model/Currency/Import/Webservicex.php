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
 * @category    Magento
 * @package     Magento_Directory
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Currency rate import model (From www.webservicex.net)
 */
namespace Magento\Directory\Model\Currency\Import;

class Webservicex extends \Magento\Directory\Model\Currency\Import\AbstractImport
{
    /**
     * @var string
     */
    protected $_url = 'http://www.webservicex.net/CurrencyConvertor.asmx/ConversionRate?FromCurrency={{CURRENCY_FROM}}&ToCurrency={{CURRENCY_TO}}';

    /**
     * HTTP client
     *
     * @var \Magento\HTTP\ZendClient
     */
    protected $_httpClient;

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;

    /**
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     */
    public function __construct(
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Core\Model\Store\Config $coreStoreConfig
    ) {
        parent::__construct($currencyFactory);
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_httpClient = new \Magento\HTTP\ZendClient();
    }

    /**
     * @param string $currencyFrom
     * @param string $currencyTo
     * @param int $retry
     * @return float|null
     */
    protected function _convert($currencyFrom, $currencyTo, $retry = 0)
    {
        $url = str_replace('{{CURRENCY_FROM}}', $currencyFrom, $this->_url);
        $url = str_replace('{{CURRENCY_TO}}', $currencyTo, $url);

        try {
            $response = $this->_httpClient->setUri(
                $url
            )->setConfig(
                array('timeout' => $this->_coreStoreConfig->getConfig('currency/webservicex/timeout'))
            )->request(
                'GET'
            )->getBody();

            $xml = simplexml_load_string($response, null, LIBXML_NOERROR);
            if (!$xml) {
                $this->_messages[] = __('We can\'t retrieve a rate from %1.', $url);
                return null;
            }
            return (double)$xml;
        } catch (\Exception $e) {
            if ($retry == 0) {
                $this->_convert($currencyFrom, $currencyTo, 1);
            } else {
                $this->_messages[] = __('We can\'t retrieve a rate from %1.', $url);
            }
        }
    }
}
