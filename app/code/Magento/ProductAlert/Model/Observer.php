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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\ProductAlert\Model;

/**
 * ProductAlert observer
 *
 * @category   Magento
 * @package    Magento_ProductAlert
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Observer
{
    /**
     * Error email template configuration
     */
    const XML_PATH_ERROR_TEMPLATE = 'catalog/productalert_cron/error_email_template';

    /**
     * Error email identity configuration
     */
    const XML_PATH_ERROR_IDENTITY = 'catalog/productalert_cron/error_email_identity';

    /**
     * 'Send error emails to' configuration
     */
    const XML_PATH_ERROR_RECIPIENT = 'catalog/productalert_cron/error_email';

    /**
     * Allow price alert
     *
     */
    const XML_PATH_PRICE_ALLOW = 'catalog/productalert/allow_price';

    /**
     * Allow stock alert
     *
     */
    const XML_PATH_STOCK_ALLOW = 'catalog/productalert/allow_stock';

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
     * @var \Magento\Stdlib\DateTime\DateTimeFactory
     */
    protected $_dateFactory;

    /**
     * @var \Magento\ProductAlert\Model\Resource\Stock\CollectionFactory
     */
    protected $_stockColFactory;

    /**
     * @var \Magento\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\ProductAlert\Model\EmailFactory
     */
    protected $_emailFactory;

    /**
     * @var \Magento\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\ProductAlert\Model\Resource\Price\CollectionFactory $priceColFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Stdlib\DateTime\DateTimeFactory $dateFactory
     * @param \Magento\ProductAlert\Model\Resource\Stock\CollectionFactory $stockColFactory
     * @param \Magento\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\ProductAlert\Model\EmailFactory $emailFactory
     * @param \Magento\Translate\Inline\StateInterface $inlineTranslation
     */
    public function __construct(
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\ProductAlert\Model\Resource\Price\CollectionFactory $priceColFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Stdlib\DateTime\DateTimeFactory $dateFactory,
        \Magento\ProductAlert\Model\Resource\Stock\CollectionFactory $stockColFactory,
        \Magento\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\ProductAlert\Model\EmailFactory $emailFactory,
        \Magento\Translate\Inline\StateInterface $inlineTranslation
    ) {
        $this->_taxData = $taxData;
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_storeManager = $storeManager;
        $this->_priceColFactory = $priceColFactory;
        $this->_customerFactory = $customerFactory;
        $this->_productFactory = $productFactory;
        $this->_dateFactory = $dateFactory;
        $this->_stockColFactory = $stockColFactory;
        $this->_transportBuilder = $transportBuilder;
        $this->_emailFactory = $emailFactory;
        $this->inlineTranslation = $inlineTranslation;
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
            } catch (\Exception $e) {
                $this->_errors[] = $e->getMessage();
            }
        }
        return $this->_websites;
    }

    /**
     * Process price emails
     *
     * @param \Magento\ProductAlert\Model\Email $email
     * @return $this
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
            )
            ) {
                continue;
            }
            try {
                $collection = $this->_priceColFactory->create()->addWebsiteFilter(
                    $website->getId()
                )->setCustomerOrder();
            } catch (\Exception $e) {
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

                    $product = $this->_productFactory->create()->setStoreId(
                        $website->getDefaultStore()->getId()
                    )->load(
                        $alert->getProductId()
                    );
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
                } catch (\Exception $e) {
                    $this->_errors[] = $e->getMessage();
                }
            }
            if ($previousCustomer) {
                try {
                    $email->send();
                } catch (\Exception $e) {
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
     * @return $this
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
            )
            ) {
                continue;
            }
            try {
                $collection = $this->_stockColFactory->create()->addWebsiteFilter(
                    $website->getId()
                )->addStatusFilter(
                    0
                )->setCustomerOrder();
            } catch (\Exception $e) {
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

                    $product = $this->_productFactory->create()->setStoreId(
                        $website->getDefaultStore()->getId()
                    )->load(
                        $alert->getProductId()
                    );
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
                } catch (\Exception $e) {
                    $this->_errors[] = $e->getMessage();
                }
            }

            if ($previousCustomer) {
                try {
                    $email->send();
                } catch (\Exception $e) {
                    $this->_errors[] = $e->getMessage();
                }
            }
        }

        return $this;
    }

    /**
     * Send email to administrator if error
     *
     * @return $this
     */
    protected function _sendErrorEmail()
    {
        if (count($this->_errors)) {
            if (!$this->_coreStoreConfig->getConfig(self::XML_PATH_ERROR_TEMPLATE)) {
                return $this;
            }

            $this->inlineTranslation->suspend();

            $transport = $this->_transportBuilder->setTemplateIdentifier(
                $this->_coreStoreConfig->getConfig(self::XML_PATH_ERROR_TEMPLATE)
            )->setTemplateOptions(
                array(
                    'area' => \Magento\Core\Model\App\Area::AREA_FRONTEND,
                    'store' => $this->_storeManager->getStore()->getId()
                )
            )->setTemplateVars(
                array('warnings' => join("\n", $this->_errors))
            )->setFrom(
                $this->_coreStoreConfig->getConfig(self::XML_PATH_ERROR_IDENTITY)
            )->addTo(
                $this->_coreStoreConfig->getConfig(self::XML_PATH_ERROR_RECIPIENT)
            )->getTransport();

            $transport->sendMessage();

            $this->inlineTranslation->resume();
            $this->_errors[] = array();
        }
        return $this;
    }

    /**
     * Run process send product alerts
     *
     * @return $this
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
