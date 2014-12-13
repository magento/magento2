<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Ogone\Model;

use Magento\Store\Model\ScopeInterface;

/**
 * Config model
 */
class Config extends \Magento\Payment\Model\Config
{
    const OGONE_PAYMENT_PATH = 'payment/ogone/';

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $_encryptor;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Factory $paymentMethodFactory
     * @param \Magento\Framework\Locale\ListsInterface $localeLists
     * @param \Magento\Framework\Config\DataInterface $dataStorage
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Factory $paymentMethodFactory,
        \Magento\Framework\Locale\ListsInterface $localeLists,
        \Magento\Framework\Config\DataInterface $dataStorage,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ) {
        parent::__construct($scopeConfig, $paymentMethodFactory, $localeLists, $dataStorage, $date);
        $this->_urlBuilder = $urlBuilder;
        $this->_encryptor = $encryptor;
    }

    /**
     * Return Ogone payment config information
     *
     * @param string $path
     * @param int|null $storeId
     * @return bool|null|string
     */
    public function getConfigData($path, $storeId = null)
    {
        if (!empty($path)) {
            return $this->_scopeConfig->getValue(
                self::OGONE_PAYMENT_PATH . $path,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
        return false;
    }

    /**
     * Return SHA1-in crypt key from config. Setup on admin place.
     *
     * @param int|null $storeId
     * @return string
     */
    public function getShaInCode($storeId = null)
    {
        return $this->getConfigData('secret_key_in', $storeId);
    }

    /**
     * Return SHA1-out crypt key from config. Setup on admin place.
     * @param int|null $storeId
     * @return string
     */
    public function getShaOutCode($storeId = null)
    {
        return $this->getConfigData('secret_key_out', $storeId);
    }

    /**
     * Return gateway path, get from config. Setup on admin place.
     *
     * @param int|null $storeId
     * @return string
     */
    public function getGatewayPath($storeId = null)
    {
        return $this->getConfigData('ogone_gateway', $storeId);
    }

    /**
     * Get PSPID, affiliation name in Ogone system
     *
     * @param int|null $storeId
     * @return string
     */
    public function getPSPID($storeId = null)
    {
        return $this->getConfigData('pspid', $storeId);
    }

    /**
     * Get paypage template for magento style templates using
     *
     * @return string
     */
    public function getPayPageTemplate()
    {
        return $this->_urlBuilder->getUrl('ogone/api/paypage', ['_nosid' => true]);
    }

    /**
     * Return url which Ogone system will use as accept
     *
     * @return string
     */
    public function getAcceptUrl()
    {
        return $this->_urlBuilder->getUrl('ogone/api/accept', ['_nosid' => true]);
    }

    /**
     * Return url which Ogone system will use as decline url
     *
     * @return string
     */
    public function getDeclineUrl()
    {
        return $this->_urlBuilder->getUrl('ogone/api/decline', ['_nosid' => true]);
    }

    /**
     * Return url which ogone system will use as exception url
     *
     * @return string
     */
    public function getExceptionUrl()
    {
        return $this->_urlBuilder->getUrl('ogone/api/exception', ['_nosid' => true]);
    }

    /**
     * Return url which Ogone system will use as cancel url
     *
     * @return string
     */
    public function getCancelUrl()
    {
        return $this->_urlBuilder->getUrl('ogone/api/cancel', ['_nosid' => true]);
    }

    /**
     * Return url which Ogone system will use as our magento home url on Ogone success page
     *
     * @return string
     */
    public function getHomeUrl()
    {
        return $this->_urlBuilder->getUrl('checkout/cart', ['_nosid' => true]);
    }
}
