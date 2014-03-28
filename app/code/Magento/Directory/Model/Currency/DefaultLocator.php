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
namespace Magento\Directory\Model\Currency;

class DefaultLocator
{
    /**
     * Config object
     *
     * @var \Magento\App\ConfigInterface
     */
    protected $_configuration;

    /**
     * Store manager
     *
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\App\ConfigInterface $configuration
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\App\ConfigInterface $configuration,
        \Magento\Core\Model\StoreManagerInterface $storeManager
    ) {
        $this->_configuration = $configuration;
        $this->_storeManager = $storeManager;
    }

    /**
     * Retrieve default currency for selected store, website or website group
     *
     * @param \Magento\App\RequestInterface $request
     * @return string
     */
    public function getDefaultCurrency(\Magento\App\RequestInterface $request)
    {
        if ($request->getParam('store')) {
            $store = $request->getParam('store');
            $currencyCode = $this->_storeManager->getStore($store)->getBaseCurrencyCode();
        } else if ($request->getParam('website')) {
            $website = $request->getParam('website');
            $currencyCode = $this->_storeManager->getWebsite($website)->getBaseCurrencyCode();
        } else if ($request->getParam('group')) {
            $group = $request->getParam('group');
            $currencyCode = $this->_storeManager->getGroup($group)->getWebsite()->getBaseCurrencyCode();
        } else {
            $currencyCode = $this->_configuration->getValue(
                \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                'default'
            );
        }

        return $currencyCode;
    }
}
