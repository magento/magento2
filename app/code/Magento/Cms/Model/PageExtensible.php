<?php

declare(strict_types=1);

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Model;

use Magento\Cms\Api\Data\PageExtensibleExtensionInterface;
use Magento\Cms\Api\Data\PageExtensibleInterface;
use Magento\Cms\Helper\Page as PageHelper;
use Magento\Cms\Model\ResourceModel\Page as PageResource;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;

/**
 * TODO: Add missing unit tests
 *
 * TODO: Fix getters so they won't return any data set through $model->setData($key, $value)
 *
 * Class PageExtensible
 *
 * @package Magento\Cms\Model
 */
class PageExtensible extends AbstractExtensibleModel implements PageExtensibleInterface, IdentityInterface
{
    /**
     * @var string
     */
    public const NOROUTE_PAGE_ID = 'no-route';

    /**
     * @var int
     */
    public const STATUS_ENABLED = 1;

    /**
     * @var int
     */
    public const STATUS_DISABLED = 0;

    /**
     * @var string
     */
    public const CACHE_TAG = 'cms_p';

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * @var string
     */
    protected $_eventPrefix = 'cms_page';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Init
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(PageResource::class);
    }

    /**
     * PageExtensible constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param ResourceModel\Page $resource
     * @param ResourceModel\Page\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        ScopeConfigInterface $scopeConfig,
        ResourceModel\Page $resource,
        ResourceModel\Page\Collection $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );

        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Load
     *
     * @param int|null $id
     * @param string|null $field
     *
     * @return PageExtensible
     *
     * @deprecated
     *
     * @see \Magento\Cms\Model\ResourceModel\Page::load
     *
     * @codeCoverageIgnore
     */
    public function load($id, $field = null): self
    {
        if ($id === null) {
            return $this->noRoutePage();
        }

        $this->_resource->load($this, $id, $field);
        return $this;
    }

    /**
     * TODO: Should this be part of interface?
     *
     * No route page
     *
     * @return self
     *
     * @deprecated
     */
    public function noRoutePage(): self
    {
        return $this->load(self::NOROUTE_PAGE_ID, $this->getIdFieldName());
    }

    /**
     * Before save
     *
     * @return PageExtensible
     *
     * @throws LocalizedException
     *
     * @codeCoverageIgnore
     */
    public function beforeSave(): self
    {
        $originalIdentifier = $this->getOrigData(self::IDENTIFIER);
        $currentIdentifier = $this->getIdentifier();

        if ($this->hasDataChanges()) {
            $this->setUpdateTime(null);
        }

        if (!$this->getId() || $originalIdentifier === $currentIdentifier) {
            return parent::beforeSave();
        }

        switch ($originalIdentifier) {
            case $this->scopeConfig->getValue(PageHelper::XML_PATH_NO_ROUTE_PAGE):
                throw new LocalizedException(
                    __('This identifier is reserved for "CMS No Route Page" in configuration.')
                );
            case $this->scopeConfig->getValue(PageHelper::XML_PATH_HOME_PAGE):
                throw new LocalizedException(__('This identifier is reserved for "CMS Home Page" in configuration.'));
            case $this->scopeConfig->getValue(PageHelper::XML_PATH_NO_COOKIES_PAGE):
                throw new LocalizedException(
                    __('This identifier is reserved for "CMS No Cookies Page" in configuration.')
                );
        }

        return parent::beforeSave();
    }

    /**
     * TODO: Should this be part of interface? Using public function not in available in interface makes us depend on
     *
     * TODO: concrete implementation - maybe this should be marked as depracted and moved to separte service contract?
     *
     * @param string $identifier
     * @param int $storeId
     *
     * @return int
     *
     * @throws LocalizedException
     *
     * @deprecated
     *
     * @see \Magento\Cms\Api\GetPageByIdentifierInterface::execute
     */
    public function checkIdentifier(string $identifier, int $storeId): int
    {
        return (int) $this->_resource->checkIdentifier($identifier, $storeId);
    }

    /**
     * TODO: Move to separate OptionSourceInterface instance
     *
     * Prepare page's statuses.
     * Available event cms_page_get_available_statuses to customize statuses.
     *
     * @return array
     *
     * @deprecated
     *
     * @codeCoverageIgnore
     */
    public function getAvailableStatuses(): array
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get id
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        $id = $this->getData(self::PAGE_ID);
        return empty($id) ? null : (int) $id;
    }

    /**
     * Get identifier
     *
     * @return string|null
     */
    public function getIdentifier(): ?string
    {
        return $this->getData(self::IDENTIFIER);
    }

    /**
     * Get title
     *
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->getData(self::TITLE);
    }

    /**
     * Get Page layout
     *
     * @return string|null
     */
    public function getPageLayout(): ?string
    {
        return $this->getData(self::PAGE_LAYOUT);
    }

    /**
     * Get meta title
     *
     * @return string|null
     */
    public function getMetaTitle(): ?string
    {
        return $this->getData(self::META_TITLE);
    }

    /**
     * Get meta keywords
     *
     * @return string|null
     */
    public function getMetaKeywords(): ?string
    {
        return $this->getData(self::META_KEYWORDS);
    }

    /**
     * Get meta description
     *
     * @return string|null
     */
    public function getMetaDescription(): ?string
    {
        return $this->getData(self::META_DESCRIPTION);
    }

    /**
     * Get content heading
     *
     * @return string|null
     */
    public function getContentHeading(): ?string
    {
        return $this->getData(self::CONTENT_HEADING);
    }

    /**
     * Get content
     *
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->getData(self::CONTENT);
    }

    /**
     * Get creation time
     *
     * @return string|null
     */
    public function getCreationTime(): ?string
    {
        return $this->getData(self::CREATION_TIME);
    }

    /**
     * Get update time
     *
     * @return string|null
     */
    public function getUpdateTime(): ?string
    {
        return $this->getData(self::UPDATE_TIME);
    }

    /**
     * Get sort order
     *
     * @return string|null
     */
    public function getSortOrder(): ?string
    {
        return $this->getData(self::SORT_ORDER);
    }

    /**
     * Get Layout update xml
     *
     * @return string|null
     */
    public function getLayoutUpdateXml(): ?string
    {
        return $this->getData(self::LAYOUT_UPDATE_XML);
    }

    /**
     * Get custom theme
     *
     * @return string|null
     */
    public function getCustomTheme(): ?string
    {
        return $this->getData(self::CUSTOM_THEME);
    }

    /**
     * Get custom root template
     *
     * @return string|null
     */
    public function getCustomRootTemplate(): ?string
    {
        return $this->getData(self::CUSTOM_ROOT_TEMPLATE);
    }

    /**
     * Get custom layout update xml
     *
     * @return string|null
     */
    public function getCustomLayoutUpdateXml(): ?string
    {
        return $this->getData(self::CUSTOM_LAYOUT_UPDATE_XML);
    }

    /**
     * Get custom theme from
     *
     * @return string|null
     */
    public function getCustomThemeFrom(): ?string
    {
        return $this->getData(self::CUSTOM_THEME_FROM);
    }

    /**
     * Get custom theme
     *
     * @return string|null
     */
    public function getCustomThemeTo(): ?string
    {
        return $this->getData(self::CUSTOM_THEME_TO);
    }

    /**
     * Is active
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return (bool) $this->getData(self::IS_ACTIVE);
    }

    /**
     * Set id
     *
     * @param int|mixed $id
     *
     * @return PageExtensibleInterface
     */
    public function setId($id): PageExtensibleInterface
    {
        $this->setData(self::PAGE_ID, $id);
        return $this;
    }

    /**
     * Set identifier
     *
     * @param string $identifier
     *
     * @return PageExtensibleInterface
     */
    public function setIdentifier($identifier): PageExtensibleInterface
    {
        $this->setData(self::IDENTIFIER, $identifier);
        return $this;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return PageExtensibleInterface
     */
    public function setTitle($title): PageExtensibleInterface
    {
        $this->setData(self::TITLE, $title);
        return $this;
    }

    /**
     * Set page layout
     *
     * @param string $pageLayout
     *
     * @return PageExtensibleInterface
     */
    public function setPageLayout($pageLayout): PageExtensibleInterface
    {
        $this->setData(self::PAGE_LAYOUT, $pageLayout);
        return $this;
    }

    /**
     * Set meta title
     *
     * @param string $metaTitle
     *
     * @return PageExtensibleInterface
     */
    public function setMetaTitle($metaTitle): PageExtensibleInterface
    {
        $this->setData(self::META_TITLE, $metaTitle);
        return $this;
    }

    /**
     * Set meta keywords
     *
     * @param string $metaKeywords
     *
     * @return PageExtensibleInterface
     */
    public function setMetaKeywords($metaKeywords): PageExtensibleInterface
    {
        $this->setData(self::META_KEYWORDS, $metaKeywords);
        return $this;
    }

    /**
     * Set meta description
     *
     * @param string $metaDescription
     *
     * @return PageExtensibleInterface
     */
    public function setMetaDescription($metaDescription): PageExtensibleInterface
    {
        $this->setData(self::META_DESCRIPTION, $metaDescription);
        return $this;
    }

    /**
     * Set content heading
     *
     * @param string $contentHeading
     *
     * @return PageExtensibleInterface
     */
    public function setContentHeading($contentHeading): PageExtensibleInterface
    {
        $this->setData(self::CONTENT_HEADING, $contentHeading);
        return $this;
    }

    /**
     * Set content
     *
     * @param string $content
     *
     * @return PageExtensibleInterface
     */
    public function setContent($content): PageExtensibleInterface
    {
        $this->setData(self::CONTENT, $content);
        return $this;
    }

    /**
     * Set creation time
     *
     * @param string $creationTime
     *
     * @return PageExtensibleInterface
     */
    public function setCreationTime($creationTime): PageExtensibleInterface
    {
        $this->setData(self::CREATION_TIME, $creationTime);
        return $this;
    }

    /**
     * Set update time
     *
     * @param string|null $updateTime
     *
     * @return PageExtensibleInterface
     */
    public function setUpdateTime($updateTime): PageExtensibleInterface
    {
        $this->setData(self::UPDATE_TIME, $updateTime);
        return $this;
    }

    /**
     * Set sort order
     *
     * @param string $sortOrder
     *
     * @return PageExtensibleInterface
     */
    public function setSortOrder($sortOrder): PageExtensibleInterface
    {
        $this->setData(self::SORT_ORDER, $sortOrder);
        return $this;
    }

    /**
     * Set layout update xml
     *
     * @param string $layoutUpdateXml
     *
     * @return PageExtensibleInterface
     */
    public function setLayoutUpdateXml($layoutUpdateXml): PageExtensibleInterface
    {
        $this->setData(self::LAYOUT_UPDATE_XML, $layoutUpdateXml);
        return $this;
    }

    /**
     * Set custom theme
     *
     * @param string $customTheme
     *
     * @return PageExtensibleInterface
     */
    public function setCustomTheme($customTheme): PageExtensibleInterface
    {
        $this->setData(self::CUSTOM_THEME, $customTheme);
        return $this;
    }

    /**
     * Set custom root template
     *
     * @param string $customRootTemplate
     *
     * @return PageExtensibleInterface
     */
    public function setCustomRootTemplate($customRootTemplate): PageExtensibleInterface
    {
        $this->setData(self::CUSTOM_ROOT_TEMPLATE, $customRootTemplate);
        return $this;
    }

    /**
     * Set custom layout update xml
     *
     * @param string $customLayoutUpdateXml
     *
     * @return PageExtensibleInterface
     */
    public function setCustomLayoutUpdateXml($customLayoutUpdateXml): PageExtensibleInterface
    {
        $this->setData(self::CUSTOM_LAYOUT_UPDATE_XML, $customLayoutUpdateXml);
        return $this;
    }

    /**
     * Set custom theme from
     *
     * @param string $customThemeFrom
     *
     * @return PageExtensibleInterface
     */
    public function setCustomThemeFrom($customThemeFrom): PageExtensibleInterface
    {
        $this->setData(self::CUSTOM_THEME_FROM, $customThemeFrom);
        return $this;
    }

    /**
     * Set custom theme to
     *
     * @param string $customThemeTo
     *
     * @return PageExtensibleInterface
     */
    public function setCustomThemeTo($customThemeTo): PageExtensibleInterface
    {
        $this->setData(self::CUSTOM_THEME_TO, $customThemeTo);
        return $this;
    }

    /**
     * Set is active
     *
     * @param bool $isActive
     *
     * @return PageExtensibleInterface
     */
    public function setIsActive($isActive): PageExtensibleInterface
    {
        $this->setData(self::IS_ACTIVE, $isActive);
        return $this;
    }

    /**
     * Set stores
     *
     * @return int[]
     */
    public function getStores(): array
    {
        $storeDataByStoresKey = $this->getData('stores');
        $storeDataByStoreIdKey = $this->getData('store_id');

        if (!empty($storeDataByStoresKey)) {
            return is_array($storeDataByStoresKey) ? $storeDataByStoresKey : [$storeDataByStoresKey];
        } elseif (!empty($storeDataByStoreIdKey)) {
            return is_array($storeDataByStoreIdKey) ? $storeDataByStoreIdKey : [$storeDataByStoreIdKey];
        }

        return [];
    }

    /**
     * Get extension attributes
     *
     * @return \Magento\Cms\Api\Data\PageExtensibleExtensionInterface|null
     *
     * @codeCoverageIgnore
     */
    public function getExtensionAttributes(): ?PageExtensibleExtensionInterface
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set extension attributes
     *
     * @param \Magento\Cms\Api\Data\PageExtensibleExtensionInterface $extensionAttributes
     *
     * @return void
     *
     * @codeCoverageIgnore
     */
    public function setExtensionAttributes(PageExtensibleExtensionInterface $extensionAttributes): void
    {
        $this->_setExtensionAttributes($extensionAttributes);
    }
}
