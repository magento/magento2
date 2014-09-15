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
namespace Magento\Directory\Model\Currency;

class DefaultLocator
{
    /**
     * Config object
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_configuration;

    /**
     * Store manager
     *
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $configuration
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $configuration,
        \Magento\Framework\StoreManagerInterface $storeManager
    ) {
        $this->_configuration = $configuration;
        $this->_storeManager = $storeManager;
    }

    /**
     * Retrieve default currency for selected store, website or website group
     * @todo: Refactor to ScopeDefiner
     * @param \Magento\Framework\App\RequestInterface $request
     * @return string
     */
    public function getDefaultCurrency(\Magento\Framework\App\RequestInterface $request)
    {
        if ($request->getParam('store')) {
            $store = $request->getParam('store');
            $currencyCode = $this->_storeManager->getStore($store)->getBaseCurrencyCode();
        } else {
            if ($request->getParam('website')) {
                $website = $request->getParam('website');
                $currencyCode = $this->_storeManager->getWebsite($website)->getBaseCurrencyCode();
            } else {
                if ($request->getParam('group')) {
                    $group = $request->getParam('group');
                    $currencyCode = $this->_storeManager->getGroup($group)->getWebsite()->getBaseCurrencyCode();
                } else {
                    $currencyCode = $this->_configuration->getValue(
                        \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                        'default'
                    );
                }
            }
        }

        return $currencyCode;
    }
}
