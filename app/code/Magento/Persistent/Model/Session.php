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
namespace Magento\Persistent\Model;

/**
 * Persistent Session Model
 *
 * @method int getCustomerId()
 * @method Session setCustomerId()
 */
class Session extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Persistent cookie key length
     */
    const KEY_LENGTH = 50;

    /**
     * Persistent cookie name
     */
    const COOKIE_NAME = 'persistent_shopping_cart';

    /**
     * Fields which model does not save into `info` db field
     *
     * @var string[]
     */
    protected $_unserializableFields = array(
        'persistent_id',
        'key',
        'customer_id',
        'website_id',
        'info',
        'updated_at'
    );

    /**
     * If model loads expired sessions
     *
     * @var bool
     */
    protected $_loadExpired = false;

    /**
     * Persistent data
     *
     * @var \Magento\Persistent\Helper\Data
     */
    protected $_persistentData;

    /**
     * Core data
     *
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_coreConfig;

    /**
     * Store manager
     *
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Cookie manager
     *
     * @var \Magento\Framework\Stdlib\CookieManager
     */
    protected $_cookieManager;

    /**
     * Cookie metadata factory
     *
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $_cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;

    /**
     * @var \Magento\Framework\Session\Config\ConfigInterface
     */
    protected $sessionConfig;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $coreConfig
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Persistent\Helper\Data $persistentData
     * @param \Magento\Framework\Stdlib\CookieManager $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Framework\Session\Config\ConfigInterface $sessionConfig
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $coreConfig,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Persistent\Helper\Data $persistentData,
        \Magento\Framework\Stdlib\CookieManager $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Framework\Session\Config\ConfigInterface $sessionConfig,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_coreData = $coreData;
        $this->_persistentData = $persistentData;
        $this->_coreConfig = $coreConfig;
        $this->_cookieManager = $cookieManager;
        $this->_cookieMetadataFactory = $cookieMetadataFactory;
        $this->_storeManager = $storeManager;
        $this->sessionConfig = $sessionConfig;
        $this->mathRandom = $mathRandom;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Persistent\Model\Resource\Session');
    }

    /**
     * Set if load expired persistent session
     *
     * @param bool $loadExpired
     * @return $this
     */
    public function setLoadExpired($loadExpired = true)
    {
        $this->_loadExpired = $loadExpired;
        return $this;
    }

    /**
     * Get if model loads expired sessions
     *
     * @return bool
     */
    public function getLoadExpired()
    {
        return $this->_loadExpired;
    }

    /**
     * Get date-time before which persistent session is expired
     *
     * @param int|string|\Magento\Store\Model\Store $store
     * @return string
     */
    public function getExpiredBefore($store = null)
    {
        return gmdate('Y-m-d H:i:s', time() - $this->_persistentData->getLifeTime($store));
    }

    /**
     * Serialize info for Resource Model to save
     * For new model check and set available cookie key
     *
     * @return $this
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();

        // Setting info
        $info = array();
        foreach ($this->getData() as $index => $value) {
            if (!in_array($index, $this->_unserializableFields)) {
                $info[$index] = $value;
            }
        }
        $this->setInfo($this->_coreData->jsonEncode($info));

        if ($this->isObjectNew()) {
            $this->setWebsiteId($this->_storeManager->getStore()->getWebsiteId());
            // Setting cookie key
            do {
                $this->setKey($this->mathRandom->getRandomString(self::KEY_LENGTH));
            } while (!$this->getResource()->isKeyAllowed($this->getKey()));
        }

        return $this;
    }

    /**
     * Set model data from info field
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        $info = $this->_coreData->jsonDecode($this->getInfo());
        if (is_array($info)) {
            foreach ($info as $key => $value) {
                $this->setData($key, $value);
            }
        }
        return $this;
    }

    /**
     * Get persistent session by cookie key
     *
     * @param string $key
     * @return $this
     */
    public function loadByCookieKey($key = null)
    {
        if (null === $key) {
            $key = $this->_cookieManager->getCookie(self::COOKIE_NAME);
        }
        if ($key) {
            $this->load($key, 'key');
        }

        return $this;
    }

    /**
     * Load session model by specified customer id
     *
     * @param int $id
     * @return $this
     */
    public function loadByCustomerId($id)
    {
        return $this->load($id, 'customer_id');
    }

    /**
     * Delete customer persistent session by customer id
     *
     * @param int $customerId
     * @param bool $clearCookie
     * @return $this
     */
    public function deleteByCustomerId($customerId, $clearCookie = true)
    {
        if ($clearCookie) {
            $this->removePersistentCookie();
        }
        $this->getResource()->deleteByCustomerId($customerId);
        return $this;
    }

    /**
     * Remove persistent cookie
     *
     * @return $this
     */
    public function removePersistentCookie()
    {
        $cookieMetadata = $this->_cookieMetadataFactory->createCookieMetadata()
            ->setPath($this->sessionConfig->getCookiePath());
        $this->_cookieManager->deleteCookie(self::COOKIE_NAME, $cookieMetadata);
        return $this;
    }

    /**
     * Set persistent cookie
     *
     * @param int $duration Time in seconds.
     * @param string $path
     * @return $this
     */
    public function setPersistentCookie($duration, $path)
    {
        $value = $this->getKey();
        $this->setCookie($value, $duration, $path);
        return $this;
    }

    /**
     * Postpone cookie expiration time if cookie value defined
     *
     * @param int $duration Time in seconds.
     * @param string $path
     * @return $this
     */
    public function renewPersistentCookie($duration, $path)
    {
        if ($duration === null) {
            return $this;
        }
        $value = $this->_cookieManager->getCookie(self::COOKIE_NAME);
        if (null !== $value) {
            $this->setCookie($value, $duration, $path);
        }
        return $this;
    }

    /**
     * Delete expired persistent sessions for the website
     *
     * @param null|int $websiteId
     * @return $this
     */
    public function deleteExpired($websiteId = null)
    {
        if (is_null($websiteId)) {
            $websiteId = $this->_storeManager->getStore()->getWebsiteId();
        }

        $lifetime = $this->_coreConfig->getValue(
            \Magento\Persistent\Helper\Data::XML_PATH_LIFE_TIME,
            'website',
            intval($websiteId)
        );

        if ($lifetime) {
            $this->getResource()->deleteExpired($websiteId, gmdate('Y-m-d H:i:s', time() - $lifetime));
        }

        return $this;
    }

    /**
     * Delete 'persistent' cookie
     *
     * @return $this
     */
    protected function _afterDeleteCommit()
    {
        $this->removePersistentCookie();
        return parent::_afterDeleteCommit();
    }

    /**
     * Set `updated_at` to be always changed
     *
     * @return $this
     */
    public function save()
    {
        $this->setUpdatedAt(gmdate('Y-m-d H:i:s'));
        return parent::save();
    }

    /**
     * Set persistent shopping cart cookie.
     *
     * @param string $value
     * @param int $duration
     * @param string $path
     * @return void
     */
    private function setCookie($value, $duration, $path)
    {
        $publicCookieMetadata = $this->_cookieMetadataFactory->createPublicCookieMetadata()
            ->setDuration($duration)
            ->setPath($path)
            ->setHttpOnly(true);
        $this->_cookieManager->setPublicCookie(
            self::COOKIE_NAME,
            $value,
            $publicCookieMetadata
        );
    }
}
