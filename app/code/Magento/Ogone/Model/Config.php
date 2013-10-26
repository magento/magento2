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
 * @package     Magento_Ogone
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Config model
 */
namespace Magento\Ogone\Model;

class Config extends \Magento\Payment\Model\Config
{
    const OGONE_PAYMENT_PATH = 'payment/ogone/';

    /**
     * Core data
     *
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData = null;

    /**
     * @var \Magento\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @param \Magento\UrlInterface $urlBuilder
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Core\Model\Config $coreConfig
     * @param \Magento\Payment\Model\Method\Factory $paymentMethodFactory
     * @param \Magento\Core\Model\LocaleInterface $locale
     * @param \Magento\Config\DataInterface $dataStorage
     */
    public function __construct(
        \Magento\UrlInterface $urlBuilder,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Core\Model\Config $coreConfig,
        \Magento\Payment\Model\Method\Factory $paymentMethodFactory,
        \Magento\Core\Model\LocaleInterface $locale,
        \Magento\Config\DataInterface $dataStorage
    ) {
        parent::__construct($coreStoreConfig, $coreConfig, $paymentMethodFactory, $locale, $dataStorage);
        $this->_urlBuilder = $urlBuilder;
        $this->_coreData = $coreData;
    }

    /**
     * Return ogone payment config information
     *
     * @param string $path
     * @param int $storeId
     * @return Simple_Xml
     */
    public function getConfigData($path, $storeId=null)
    {
        if (!empty($path)) {
            return $this->_coreStoreConfig->getConfig(self::OGONE_PAYMENT_PATH . $path, $storeId);
        }
        return false;
    }

    /**
     * Return SHA1-in crypt key from config. Setup on admin place.
     *
     * @param int $storeId
     * @return string
     */
    public function getShaInCode($storeId=null)
    {
        return $this->_coreData->decrypt($this->getConfigData('secret_key_in', $storeId));
    }

    /**
     * Return SHA1-out crypt key from config. Setup on admin place.
     * @param int $storeId
     * @return string
     */
    public function getShaOutCode($storeId=null)
    {
        return $this->_coreData->decrypt($this->getConfigData('secret_key_out', $storeId));
    }

    /**
     * Return gateway path, get from confing. Setup on admin place.
     *
     * @param int $storeId
     * @return string
     */
    public function getGatewayPath($storeId=null)
    {
        return $this->getConfigData('ogone_gateway', $storeId);
    }

    /**
     * Get PSPID, affiliation name in ogone system
     *
     * @param int $storeId
     * @return string
     */
    public function getPSPID($storeId=null)
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
        return $this->_urlBuilder->getUrl('ogone/api/paypage', array('_nosid' => true));
    }

    /**
     * Return url which ogone system will use as accept
     *
     * @return string
     */
    public function getAcceptUrl()
    {
        return $this->_urlBuilder->getUrl('ogone/api/accept', array('_nosid' => true));
    }

    /**
     * Return url which ogone system will use as decline url
     *
     * @return string
     */
    public function getDeclineUrl()
    {
        return $this->_urlBuilder->getUrl('ogone/api/decline', array('_nosid' => true));
    }

    /**
     * Return url which ogone system will use as exception url
     *
     * @return string
     */
    public function getExceptionUrl()
    {
        return $this->_urlBuilder->getUrl('ogone/api/exception', array('_nosid' => true));
    }

    /**
     * Return url which ogone system will use as cancel url
     *
     * @return string
     */
    public function getCancelUrl()
    {
        return $this->_urlBuilder->getUrl('ogone/api/cancel', array('_nosid' => true));
    }

    /**
     * Return url which ogone system will use as our magento home url on ogone success page
     *
     * @return string
     */
    public function getHomeUrl()
    {
        return $this->_urlBuilder->getUrl('checkout/cart', array('_nosid' => true));
    }
}
