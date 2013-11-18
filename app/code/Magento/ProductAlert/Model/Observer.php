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
 * @package     Magento_ProductAlert
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * ProductAlert observer
 *
 * @category   Magento
 * @package    Magento_ProductAlert
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\ProductAlert\Model;

class Observer
{
    /**
     * Error email template configuration
     */
    const XML_PATH_ERROR_TEMPLATE   = 'catalog/productalert_cron/error_email_template';

    /**
     * Error email identity configuration
     */
    const XML_PATH_ERROR_IDENTITY   = 'catalog/productalert_cron/error_email_identity';

    /**
     * 'Send error emails to' configuration
     */
    const XML_PATH_ERROR_RECIPIENT  = 'catalog/productalert_cron/error_email';

    /**
     * Allow price alert
     *
     */
    const XML_PATH_PRICE_ALLOW      = 'catalog/productalert/allow_price';

    /**
     * Allow stock alert
     *
     */
    const XML_PATH_STOCK_ALLOW      = 'catalog/productalert/allow_stock';

    /**
     * Website collection array
     *
     * @var array
     */
    protected $_websites;

    /**
     * Warning (exception) errors array
     *
     * @var array
     */
    protected $_errors = array();

    /**
     * Tax data
     *
     * @var \Magento\Tax\Helper\Data
     */
    protected $_taxData = null;

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\ProductAlert\Model\Resource\Price\CollectionFactory
     */
    protected $_priceColFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Core\Model\DateFactory
     */
    protected $_dateFactory;

    /**
     * @var \Magento\ProductAlert\Model\Resource\Stock\CollectionFactory
     */
    protected $_stockColFactory;

    /**
     * @var \Magento\Core\Model\Translate
     */
    protected $_translate;

    /**
     * @var \Magento\Core\Model\Email\TemplateFactory
     */
    protected $_templateFactory;

    /**
     * @var \Magento\ProductAlert\Model\EmailFactory
     */
    protected $_emailFactory;

    /**
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\ProductAlert\Model\Resource\Price\CollectionFactory $priceColFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Core\Model\DateFactory $dateFactory
     * @param \Magento\ProductAlert\Model\Resource\Stock\CollectionFactory $stockColFactory
     * @param \Magento\Core\Model\Translate $translate
     * @param \Magento\Core\Model\Email\TemplateFactory $templateFactory
     * @param \Magento\ProductAlert\Model\EmailFactory $emailFactory
     */
    public function __construct(
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\ProductAlert\Model\Resource\Price\CollectionFactory $priceColFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Core\Model\DateFactory $dateFactory,
        \Magento\ProductAlert\Model\Resource\Stock\CollectionFactory $stockColFactory,
        \Magento\Core\Model\Translate $translate,
        \Magento\Core\Model\Email\TemplateFactory $templateFactory,
        \Magento\ProductAlert\Model\EmailFactory $emailFactory
    ) {
        $this->_taxData = $taxData;
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_storeManager = $storeManager;
        $this->_priceColFactory = $priceColFactory;
        $this->_customerFactory = $customerFactory;
        $this->_productFactory = $productFactory;
        $this->_dateFactory = $dateFactory;
        $this->_stockColFactory = $stockColFactory;
        $this->_translate = $translate;
        $this->_templateFactory = $templateFactory;
        $this->_emailFactory = $emailFactory;
    }

    /**
     * Retrieve website collection array
     *
     * @return array
     */
    protected function _getWebsites()
    {
        if (is_null($this->_websites)) {
            try {
                $this->_websites = $this->_storeManager->getWebsites();
            }
            catch (\Exception $e) {
                $this->_errors[] = $e->getMessage();
            }
        }
        return $this->_websites;
    }

    /**
     * Process price emails
     *
     * @param \Magento\ProductAlert\Model\Email $email
     * @return \Magento\ProductAlert\Model\Observer
     */
    protected function _processPrice(\Magento\ProductAlert\Model\Email $email)
    {
        $email->setType('price');
        foreach ($this->_getWebsites() as $website) {
            /* @var $website \Magento\Core\Model\Website */

            if (!$website->getDefaultGroup() || !$website->getDefaultGroup()->getDefaultStore()) {
                continue;
            }
            if (!$this->_coreStoreConfig->getConfig(
                self::XML_PATH_PRICE_ALLOW,
                $website->getDefaultGroup()->getDefaultStore()->getId()
            )) {
                continue;
            }
            try {
                $collection = $this->_priceColFactory->create()
                    ->addWebsiteFilter($website->getId())
                    ->setCustomerOrder();
            }
            catch (\Exception $e) {
                $this->_errors[] = $e->getMessage();
                return $this;
            }

            $previousCustomer = null;
            $email->setWebsite($website);
            foreach ($collection as $alert) {
                try {
                    if (!$previousCustomer || $previousCustomer->getId() != $alert->getCustomerId()) {
                        $customer = $this->_customerFactory->create()->load($alert->getCustomerId());
                        if ($previousCustomer) {
                            $email->send();
                        }
                        if (!$customer) {
                            continue;
                        }
                        $previousCustomer = $customer;
                        $email->clean();
                        $email->setCustomer($customer);
                    } else {
                        $customer = $previousCustomer;
                    }

                    $product = $this->_productFactory->create()
                        ->setStoreId($website->getDefaultStore()->getId())
                        ->load($alert->getProductId());
                    if (!$product) {
                        continue;
                    }
                    $product->setCustomerGroupId($customer->getGroupId());
                    if ($alert->getPrice() > $product->getFinalPrice()) {
                        $productPrice = $product->getFinalPrice();
                        $product->setFinalPrice($this->_taxData->getPrice($product, $productPrice));
                        $product->setPrice($this->_taxData->getPrice($product, $product->getPrice()));
                        $email->addPriceProduct($product);

                        $alert->setPrice($productPrice);
                        $alert->setLastSendDate($this->_dateFactory->create()->gmtDate());
                        $alert->setSendCount($alert->getSendCount() + 1);
                        $alert->setStatus(1);
                        $alert->save();
                    }
                }
                catch (\Exception $e) {
                    $this->_errors[] = $e->getMessage();
                }
            }
            if ($previousCustomer) {
                try {
                    $email->send();
                }
                catch (\Exception $e) {
                    $this->_errors[] = $e->getMessage();
                }
            }
        }
        return $this;
    }

    /**
     * Process stock emails
     *
     * @param \Magento\ProductAlert\Model\Email $email
     * @return \Magento\ProductAlert\Model\Observer
     */
    protected function _processStock(\Magento\ProductAlert\Model\Email $email)
    {
        $email->setType('stock');

        foreach ($this->_getWebsites() as $website) {
            /* @var $website \Magento\Core\Model\Website */

            if (!$website->getDefaultGroup() || !$website->getDefaultGroup()->getDefaultStore()) {
                continue;
            }
            if (!$this->_coreStoreConfig->getConfig(
                self::XML_PATH_STOCK_ALLOW,
                $website->getDefaultGroup()->getDefaultStore()->getId()
            )) {
                continue;
            }
            try {
                $collection = $this->_stockColFactory->create()
                    ->addWebsiteFilter($website->getId())
                    ->addStatusFilter(0)
                    ->setCustomerOrder();
            }
            catch (\Exception $e) {
                $this->_errors[] = $e->getMessage();
                return $this;
            }

            $previousCustomer = null;
            $email->setWebsite($website);
            foreach ($collection as $alert) {
                try {
                    if (!$previousCustomer || $previousCustomer->getId() != $alert->getCustomerId()) {
                        $customer = $this->_customerFactory->create()->load($alert->getCustomerId());
                        if ($previousCustomer) {
                            $email->send();
                        }
                        if (!$customer) {
                            continue;
                        }
                        $previousCustomer = $customer;
                        $email->clean();
                        $email->setCustomer($customer);
                    } else {
                        $customer = $previousCustomer;
                    }

                    $product = $this->_productFactory->create()
                        ->setStoreId($website->getDefaultStore()->getId())
                        ->load($alert->getProductId());
                    /* @var $product \Magento\Catalog\Model\Product */
                    if (!$product) {
                        continue;
                    }

                    $product->setCustomerGroupId($customer->getGroupId());

                    if ($product->isSalable()) {
                        $email->addStockProduct($product);

                        $alert->setSendDate($this->_dateFactory->create()->gmtDate());
                        $alert->setSendCount($alert->getSendCount() + 1);
                        $alert->setStatus(1);
                        $alert->save();
                    }
                }
                catch (\Exception $e) {
                    $this->_errors[] = $e->getMessage();
                }
            }

            if ($previousCustomer) {
                try {
                    $email->send();
                }
                catch (\Exception $e) {
                    $this->_errors[] = $e->getMessage();
                }
            }
        }

        return $this;
    }

    /**
     * Send email to administrator if error
     *
     * @return \Magento\ProductAlert\Model\Observer
     */
    protected function _sendErrorEmail()
    {
        if (count($this->_errors)) {
            if (!$this->_coreStoreConfig->getConfig(self::XML_PATH_ERROR_TEMPLATE)) {
                return $this;
            }

            $$this->_translate->setTranslateInline(false);

            /* @var $emailTemplate \Magento\Core\Model\Email\Template */
            $this->_templateFactory->create()->setDesignConfig(array('area'  => 'backend'))
                ->sendTransactional(
                    $this->_coreStoreConfig->getConfig(self::XML_PATH_ERROR_TEMPLATE),
                    $this->_coreStoreConfig->getConfig(self::XML_PATH_ERROR_IDENTITY),
                    $this->_coreStoreConfig->getConfig(self::XML_PATH_ERROR_RECIPIENT),
                    null,
                    array('warnings' => join("\n", $this->_errors))
                );

            $$this->_translate->setTranslateInline(true);
            $this->_errors[] = array();
        }
        return $this;
    }

    /**
     * Run process send product alerts
     *
     * @return \Magento\ProductAlert\Model\Observer
     */
    public function process()
    {
        /* @var $email \Magento\ProductAlert\Model\Email */
        $email = $this->_emailFactory->create();
        $this->_processPrice($email);
        $this->_processStock($email);
        $this->_sendErrorEmail();

        return $this;
    }
}
