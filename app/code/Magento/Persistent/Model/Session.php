<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Model;

/**
 * Persistent Session Model
 *
 * @api
 * @method int getCustomerId()
 * @method Session setCustomerId()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
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
     * @since 2.0.0
     */
    protected $_unserializableFields = [
        'persistent_id',
        'key',
        'customer_id',
        'website_id',
        'info',
        'updated_at',
    ];

    /**
     * If model loads expired sessions
     *
     * @var bool
     * @since 2.0.0
     */
    protected $_loadExpired = false;

    /**
     * Persistent data
     *
     * @var \Magento\Persistent\Helper\Data
     * @since 2.0.0
     */
    protected $_persistentData;

    /**
     * Json Helper
     *
     * @var \Magento\Framework\Json\Helper\Data
     * @since 2.0.0
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 2.0.0
     */
    protected $_coreConfig;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * Cookie manager
     *
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     * @since 2.0.0
     */
    protected $_cookieManager;

    /**
     * Cookie metadata factory
     *
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     * @since 2.0.0
     */
    protected $_cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Math\Random
     * @since 2.0.0
     */
    protected $mathRandom;

    /**
     * @var \Magento\Framework\Session\Config\ConfigInterface
     * @since 2.0.0
     */
    protected $sessionConfig;

    /**
     * Request
     *
     * @var \Magento\Framework\App\Request\Http
     * @since 2.1.0
     */
    private $request;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $coreConfig
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Persistent\Helper\Data $persistentData
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Framework\Session\Config\ConfigInterface $sessionConfig
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $coreConfig,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Persistent\Helper\Data $persistentData,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Framework\Session\Config\ConfigInterface $sessionConfig,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->jsonHelper = $jsonHelper;
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
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\Persistent\Model\ResourceModel\Session::class);
    }

    /**
     * Set if load expired persistent session
     *
     * @param bool $loadExpired
     * @return $this
     * @codeCoverageIgnore
     * @since 2.0.0
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
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 2.0.0
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
     * @codeCoverageIgnore
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function beforeSave()
    {
        parent::beforeSave();

        // Setting info
        $info = [];
        foreach ($this->getData() as $index => $value) {
            if (!in_array($index, $this->_unserializableFields)) {
                $info[$index] = $value;
            }
        }
        $this->setInfo($this->jsonHelper->jsonEncode($info));

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
     * @since 2.0.0
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        $info = null;
        if ($this->getInfo()) {
            $info = $this->jsonHelper->jsonDecode($this->getInfo());
        }
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
     * @since 2.0.0
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
     * @codeCoverageIgnore
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @api
     * @since 2.0.0
     */
    public function removePersistentCookie()
    {
        $cookieMetadata = $this->_cookieMetadataFactory->createSensitiveCookieMetadata()
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
     * @api
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function deleteExpired($websiteId = null)
    {
        if ($websiteId === null) {
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
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function afterDeleteCommit()
    {
        $this->removePersistentCookie();
        return parent::afterDeleteCommit();
    }

    /**
     * Set persistent shopping cart cookie.
     *
     * @param string $value
     * @param int $duration
     * @param string $path
     * @return void
     * @since 2.0.0
     */
    private function setCookie($value, $duration, $path)
    {
        $publicCookieMetadata = $this->_cookieMetadataFactory->createPublicCookieMetadata()
            ->setDuration($duration)
            ->setPath($path)
            ->setSecure($this->getRequest()->isSecure())
            ->setHttpOnly(true);
        $this->_cookieManager->setPublicCookie(
            self::COOKIE_NAME,
            $value,
            $publicCookieMetadata
        );
    }

    /**
     * Get request object
     *
     * @return \Magento\Framework\App\Request\Http
     * @deprecated 2.1.0
     * @since 2.1.0
     */
    private function getRequest()
    {
        if ($this->request == null) {
            $this->request = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\App\Request\Http::class);
        }
        return $this->request;
    }

    /**
     * Set `updated_at` to be always changed
     *
     * @return $this
     * @since 2.1.0
     */
    public function save()
    {
        $this->setUpdatedAt(gmdate('Y-m-d H:i:s'));
        return parent::save();
    }
}
