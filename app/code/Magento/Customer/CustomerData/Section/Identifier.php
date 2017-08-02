<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\CustomerData\Section;

/**
 * Customer section identifier
 * @since 2.0.0
 */
class Identifier
{
    const COOKIE_KEY = 'storage_data_id';

    const SECTION_KEY = 'data_id';

    const UPDATE_MARK = 'sections_updated';

    /**
     * @var int
     * @since 2.0.0
     */
    protected $markId;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     * @since 2.0.0
     */
    protected $cookieManager;

    /**
     * @var \Magento\Framework\Session\Config\ConfigInterface
     * @since 2.0.0
     */
    protected $sessionConfig;

    /**
     * @param \Magento\Framework\Stdlib\Cookie\PhpCookieManager $cookieManager
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Stdlib\Cookie\PhpCookieManager $cookieManager
    ) {
        $this->cookieManager = $cookieManager;
    }

    /**
     * Init mark(identifier) for sections
     *
     * @param bool $forceUpdate
     * @return int
     * @since 2.0.0
     */
    public function initMark($forceUpdate)
    {
        if ($forceUpdate) {
            $this->markId = time();
            return $this->markId;
        }

        $cookieMarkId = false;
        if (!$this->markId) {
            $cookieMarkId = $this->cookieManager->getCookie(self::COOKIE_KEY);
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
     * @since 2.0.0
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
        return $sectionsData;
    }
}
