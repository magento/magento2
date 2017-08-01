<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Model\ResourceModel\Page as ResourceCmsPage;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Cms\Helper\Page as PageHelper;

/**
 * Cms Page Model
 *
 * @api
 * @method ResourceCmsPage _getResource()
 * @method ResourceCmsPage getResource()
 * @method Page setStoreId(array $storeId)
 * @method array getStoreId()
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @since 2.0.0
 */
class Page extends AbstractModel implements PageInterface, IdentityInterface
{
    /**
     * No route page id
     */
    const NOROUTE_PAGE_ID = 'no-route';

    /**#@+
     * Page's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    /**#@-*/

    /**
     * CMS page cache tag
     */
    const CACHE_TAG = 'cms_p';

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Prefix of model events names
     *
     * @var string
     * @since 2.0.0
     */
    protected $_eventPrefix = 'cms_page';

    /**
     * @var ScopeConfigInterface
     * @since 2.1.0
     */
    private $scopeConfig;

    /**
     * Initialize resource model
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\Cms\Model\ResourceModel\Page::class);
    }

    /**
     * Load object data
     *
     * @param int|null $id
     * @param string $field
     * @return $this
     * @since 2.0.0
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRoutePage();
        }
        return parent::load($id, $field);
    }

    /**
     * Load No-Route Page
     *
     * @return \Magento\Cms\Model\Page
     * @since 2.0.0
     */
    public function noRoutePage()
    {
        return $this->load(self::NOROUTE_PAGE_ID, $this->getIdFieldName());
    }

    /**
     * Receive page store ids
     *
     * @return int[]
     * @since 2.0.0
     */
    public function getStores()
    {
        return $this->hasData('stores') ? $this->getData('stores') : (array)$this->getData('store_id');
    }

    /**
     * Check if page identifier exist for specific store
     * return page id if page exists
     *
     * @param string $identifier
     * @param int $storeId
     * @return int
     * @since 2.0.0
     */
    public function checkIdentifier($identifier, $storeId)
    {
        return $this->_getResource()->checkIdentifier($identifier, $storeId);
    }

    /**
     * Prepare page's statuses.
     * Available event cms_page_get_available_statuses to customize statuses.
     *
     * @return array
     * @since 2.0.0
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    /**
     * Get identities
     *
     * @return array
     * @since 2.0.0
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get ID
     *
     * @return int
     * @since 2.0.0
     */
    public function getId()
    {
        return parent::getData(self::PAGE_ID);
    }

    /**
     * Get identifier
     *
     * @return string
     * @since 2.0.0
     */
    public function getIdentifier()
    {
        return $this->getData(self::IDENTIFIER);
    }

    /**
     * Get title
     *
     * @return string
     * @since 2.0.0
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * Get page layout
     *
     * @return string
     * @since 2.0.0
     */
    public function getPageLayout()
    {
        return $this->getData(self::PAGE_LAYOUT);
    }

    /**
     * Get meta title
     *
     * @return string|null
     * @since 2.1.0
     */
    public function getMetaTitle()
    {
        return $this->getData(self::META_TITLE);
    }

    /**
     * Get meta keywords
     *
     * @return string
     * @since 2.0.0
     */
    public function getMetaKeywords()
    {
        return $this->getData(self::META_KEYWORDS);
    }

    /**
     * Get meta description
     *
     * @return string
     * @since 2.0.0
     */
    public function getMetaDescription()
    {
        return $this->getData(self::META_DESCRIPTION);
    }

    /**
     * Get content heading
     *
     * @return string
     * @since 2.0.0
     */
    public function getContentHeading()
    {
        return $this->getData(self::CONTENT_HEADING);
    }

    /**
     * Get content
     *
     * @return string
     * @since 2.0.0
     */
    public function getContent()
    {
        return $this->getData(self::CONTENT);
    }

    /**
     * Get creation time
     *
     * @return string
     * @since 2.0.0
     */
    public function getCreationTime()
    {
        return $this->getData(self::CREATION_TIME);
    }

    /**
     * Get update time
     *
     * @return string
     * @since 2.0.0
     */
    public function getUpdateTime()
    {
        return $this->getData(self::UPDATE_TIME);
    }

    /**
     * Get sort order
     *
     * @return string
     * @since 2.0.0
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }

    /**
     * Get layout update xml
     *
     * @return string
     * @since 2.0.0
     */
    public function getLayoutUpdateXml()
    {
        return $this->getData(self::LAYOUT_UPDATE_XML);
    }

    /**
     * Get custom theme
     *
     * @return string
     * @since 2.0.0
     */
    public function getCustomTheme()
    {
        return $this->getData(self::CUSTOM_THEME);
    }

    /**
     * Get custom root template
     *
     * @return string
     * @since 2.0.0
     */
    public function getCustomRootTemplate()
    {
        return $this->getData(self::CUSTOM_ROOT_TEMPLATE);
    }

    /**
     * Get custom layout update xml
     *
     * @return string
     * @since 2.0.0
     */
    public function getCustomLayoutUpdateXml()
    {
        return $this->getData(self::CUSTOM_LAYOUT_UPDATE_XML);
    }

    /**
     * Get custom theme from
     *
     * @return string
     * @since 2.0.0
     */
    public function getCustomThemeFrom()
    {
        return $this->getData(self::CUSTOM_THEME_FROM);
    }

    /**
     * Get custom theme to
     *
     * @return string
     * @since 2.0.0
     */
    public function getCustomThemeTo()
    {
        return $this->getData(self::CUSTOM_THEME_TO);
    }

    /**
     * Is active
     *
     * @return bool
     * @since 2.0.0
     */
    public function isActive()
    {
        return (bool)$this->getData(self::IS_ACTIVE);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return \Magento\Cms\Api\Data\PageInterface
     * @since 2.0.0
     */
    public function setId($id)
    {
        return $this->setData(self::PAGE_ID, $id);
    }

    /**
     * Set identifier
     *
     * @param string $identifier
     * @return \Magento\Cms\Api\Data\PageInterface
     * @since 2.0.0
     */
    public function setIdentifier($identifier)
    {
        return $this->setData(self::IDENTIFIER, $identifier);
    }

    /**
     * Set title
     *
     * @param string $title
     * @return \Magento\Cms\Api\Data\PageInterface
     * @since 2.0.0
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * Set page layout
     *
     * @param string $pageLayout
     * @return \Magento\Cms\Api\Data\PageInterface
     * @since 2.0.0
     */
    public function setPageLayout($pageLayout)
    {
        return $this->setData(self::PAGE_LAYOUT, $pageLayout);
    }

    /**
     * Set meta title
     *
     * @param string $metaTitle
     * @return \Magento\Cms\Api\Data\PageInterface
     * @since 2.1.0
     */
    public function setMetaTitle($metaTitle)
    {
        return $this->setData(self::META_TITLE, $metaTitle);
    }

    /**
     * Set meta keywords
     *
     * @param string $metaKeywords
     * @return \Magento\Cms\Api\Data\PageInterface
     * @since 2.0.0
     */
    public function setMetaKeywords($metaKeywords)
    {
        return $this->setData(self::META_KEYWORDS, $metaKeywords);
    }

    /**
     * Set meta description
     *
     * @param string $metaDescription
     * @return \Magento\Cms\Api\Data\PageInterface
     * @since 2.0.0
     */
    public function setMetaDescription($metaDescription)
    {
        return $this->setData(self::META_DESCRIPTION, $metaDescription);
    }

    /**
     * Set content heading
     *
     * @param string $contentHeading
     * @return \Magento\Cms\Api\Data\PageInterface
     * @since 2.0.0
     */
    public function setContentHeading($contentHeading)
    {
        return $this->setData(self::CONTENT_HEADING, $contentHeading);
    }

    /**
     * Set content
     *
     * @param string $content
     * @return \Magento\Cms\Api\Data\PageInterface
     * @since 2.0.0
     */
    public function setContent($content)
    {
        return $this->setData(self::CONTENT, $content);
    }

    /**
     * Set creation time
     *
     * @param string $creationTime
     * @return \Magento\Cms\Api\Data\PageInterface
     * @since 2.0.0
     */
    public function setCreationTime($creationTime)
    {
        return $this->setData(self::CREATION_TIME, $creationTime);
    }

    /**
     * Set update time
     *
     * @param string $updateTime
     * @return \Magento\Cms\Api\Data\PageInterface
     * @since 2.0.0
     */
    public function setUpdateTime($updateTime)
    {
        return $this->setData(self::UPDATE_TIME, $updateTime);
    }

    /**
     * Set sort order
     *
     * @param string $sortOrder
     * @return \Magento\Cms\Api\Data\PageInterface
     * @since 2.0.0
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::SORT_ORDER, $sortOrder);
    }

    /**
     * Set layout update xml
     *
     * @param string $layoutUpdateXml
     * @return \Magento\Cms\Api\Data\PageInterface
     * @since 2.0.0
     */
    public function setLayoutUpdateXml($layoutUpdateXml)
    {
        return $this->setData(self::LAYOUT_UPDATE_XML, $layoutUpdateXml);
    }

    /**
     * Set custom theme
     *
     * @param string $customTheme
     * @return \Magento\Cms\Api\Data\PageInterface
     * @since 2.0.0
     */
    public function setCustomTheme($customTheme)
    {
        return $this->setData(self::CUSTOM_THEME, $customTheme);
    }

    /**
     * Set custom root template
     *
     * @param string $customRootTemplate
     * @return \Magento\Cms\Api\Data\PageInterface
     * @since 2.0.0
     */
    public function setCustomRootTemplate($customRootTemplate)
    {
        return $this->setData(self::CUSTOM_ROOT_TEMPLATE, $customRootTemplate);
    }

    /**
     * Set custom layout update xml
     *
     * @param string $customLayoutUpdateXml
     * @return \Magento\Cms\Api\Data\PageInterface
     * @since 2.0.0
     */
    public function setCustomLayoutUpdateXml($customLayoutUpdateXml)
    {
        return $this->setData(self::CUSTOM_LAYOUT_UPDATE_XML, $customLayoutUpdateXml);
    }

    /**
     * Set custom theme from
     *
     * @param string $customThemeFrom
     * @return \Magento\Cms\Api\Data\PageInterface
     * @since 2.0.0
     */
    public function setCustomThemeFrom($customThemeFrom)
    {
        return $this->setData(self::CUSTOM_THEME_FROM, $customThemeFrom);
    }

    /**
     * Set custom theme to
     *
     * @param string $customThemeTo
     * @return \Magento\Cms\Api\Data\PageInterface
     * @since 2.0.0
     */
    public function setCustomThemeTo($customThemeTo)
    {
        return $this->setData(self::CUSTOM_THEME_TO, $customThemeTo);
    }

    /**
     * Set is active
     *
     * @param int|bool $isActive
     * @return \Magento\Cms\Api\Data\PageInterface
     * @since 2.0.0
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function beforeSave()
    {
        $originalIdentifier = $this->getOrigData('identifier');
        $currentIdentifier = $this->getIdentifier();

        if (!$this->getId() || $originalIdentifier === $currentIdentifier) {
            return parent::beforeSave();
        }

        switch ($originalIdentifier) {
            case $this->getScopeConfig()->getValue(PageHelper::XML_PATH_NO_ROUTE_PAGE):
                throw new LocalizedException(
                    __('This identifier is reserved for "CMS No Route Page" in configuration.')
                );
            case $this->getScopeConfig()->getValue(PageHelper::XML_PATH_HOME_PAGE):
                throw new LocalizedException(__('This identifier is reserved for "CMS Home Page" in configuration.'));
            case $this->getScopeConfig()->getValue(PageHelper::XML_PATH_NO_COOKIES_PAGE):
                throw new LocalizedException(
                    __('This identifier is reserved for "CMS No Cookies Page" in configuration.')
                );
        }

        return parent::beforeSave();
    }

    /**
     * @return ScopeConfigInterface
     * @since 2.1.0
     */
    private function getScopeConfig()
    {
        if (null === $this->scopeConfig) {
            $this->scopeConfig = \Magento\Framework\App\ObjectManager::getInstance()->get(ScopeConfigInterface::class);
        }

        return $this->scopeConfig;
    }
}
