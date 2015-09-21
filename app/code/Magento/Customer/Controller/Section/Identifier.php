<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Section;

/**
 * Customer section identifier
 */
class Identifier
{
    CONST COOKIE_KEY = 'storage_data_id';

    CONST SECTION_KEY = 'data_id';

    CONST UPDATE_MARK = 'sections_updated';

    /**
     * @var int
     */
    protected $markId;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    protected $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Session\Config\ConfigInterface
     */
    protected $sessionConfig;

    /**
     * @param \Magento\Framework\Stdlib\Cookie\PhpCookieManager $cookieManager
     * @param \Magento\Framework\Session\Config\ConfigInterface $sessionConfig
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     */
    public function __construct(
        \Magento\Framework\Stdlib\Cookie\PhpCookieManager $cookieManager,
        \Magento\Framework\Session\Config\ConfigInterface $sessionConfig,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
    ) {
        $this->cookieManager = $cookieManager;
        $this->sessionConfig = $sessionConfig;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
    }

    /**
     * Update sections data mark in cookie
     */
//    public function updateCookieMark()
//    {
//        $cookieMetadata = $this->cookieMetadataFactory
//            ->createPublicCookieMetadata();
//        $cookieMetadata->setDomain($this->sessionConfig->getCookieDomain());
//        $cookieMetadata->setPath($this->sessionConfig->getCookiePath());
//        $cookieMetadata->setDuration($this->sessionConfig->getCookieLifetime());
//        $this->cookieManager->setPublicCookie(
//            self::UPDATE_MARK,
//            time(),
//            $cookieMetadata
//        );
//    }

    /**
     * Save sections info to cookie
     * @param array $value
     */
//    protected function setCookie($value)
//    {
//        $cookieMetadata = $this->cookieMetadataFactory
//            ->createPublicCookieMetadata();
//        $cookieMetadata->setDomain($this->sessionConfig->getCookieDomain());
//        $cookieMetadata->setPath($this->sessionConfig->getCookiePath());
//        $cookieMetadata->setDuration($this->sessionConfig->getCookieLifetime());
//        $this->cookieManager->setPublicCookie(
//            self::COOKIE_KEY,
//            json_encode($value),
//            $cookieMetadata
//        );
//    }

    /**
     * Init mark(identifier) for sections
     *
     * @param bool $forceUpdate
     * @return int
     */
    public function initMark($forceUpdate)
    {
        if ($forceUpdate) {
            $this->markId = time();
            return $this->markId;
        }

        $cookieMarkId = false;
        if (!$this->markId) {
            $cookieMarkId = $this->cookieManager->getCookie(SELF::COOKIE_KEY);
        }

        $this->markId = $cookieMarkId ? $cookieMarkId : time();

        return $this->markId;
    }

    /**
     * Mark sections with data id
     *
     * @param array $sectionsData
     * @param null $sectionNames
     * @param bool $updateIds
     * @return array
     */
    public function markSections(array $sectionsData, $sectionNames = null, $updateIds = false)
    {
        if (!$sectionNames) {
            $sectionNames = array_keys($sectionsData);
        }
        $markId = $this->initMark($updateIds);

        foreach ($sectionNames as $name) {
            if ($updateIds || !array_key_exists(self::SECTION_KEY, $sectionsData[$name])) {
                $sectionsData[$name][self::SECTION_KEY] = $markId;
            }
        }
//        $this->setCookie($sectionsData);
        return $sectionsData;
    }
}
