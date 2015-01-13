<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model;

use Magento\Framework\Object\IdentityInterface;

/**
 * Cms Page Model
 *
 * @method \Magento\Cms\Model\Resource\Page _getResource()
 * @method \Magento\Cms\Model\Resource\Page getResource()
 * @method \Magento\Cms\Model\Page setTitle(string $value)
 * @method string getPageLayout()
 * @method \Magento\Cms\Model\Page setPageLayout(string $value)
 * @method string getMetaKeywords()
 * @method \Magento\Cms\Model\Page setMetaKeywords(string $value)
 * @method string getMetaDescription()
 * @method \Magento\Cms\Model\Page setMetaDescription(string $value)
 * @method \Magento\Cms\Model\Page setIdentifier(string $value)
 * @method string getContentHeading()
 * @method \Magento\Cms\Model\Page setContentHeading(string $value)
 * @method string getContent()
 * @method \Magento\Cms\Model\Page setContent(string $value)
 * @method string getCreationTime()
 * @method \Magento\Cms\Model\Page setCreationTime(string $value)
 * @method string getUpdateTime()
 * @method \Magento\Cms\Model\Page setUpdateTime(string $value)
 * @method int getIsActive()
 * @method \Magento\Cms\Model\Page setIsActive(int $value)
 * @method int getSortOrder()
 * @method \Magento\Cms\Model\Page setSortOrder(int $value)
 * @method string getLayoutUpdateXml()
 * @method \Magento\Cms\Model\Page setLayoutUpdateXml(string $value)
 * @method string getCustomTheme()
 * @method \Magento\Cms\Model\Page setCustomTheme(string $value)
 * @method string getCustomPageLayout()
 * @method \Magento\Cms\Model\Page setCustomPageLayout(string $value)
 * @method string getCustomLayoutUpdateXml()
 * @method \Magento\Cms\Model\Page setCustomLayoutUpdateXml(string $value)
 * @method string getCustomThemeFrom()
 * @method \Magento\Cms\Model\Page setCustomThemeFrom(string $value)
 * @method string getCustomThemeTo()
 * @method \Magento\Cms\Model\Page setCustomThemeTo(string $value)
 * @method int[] getStores()
 */
class Page extends \Magento\Framework\Model\AbstractModel implements IdentityInterface
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

    /**#@+
     * Data object constants
     */
    const PAGE_ID = 'page_id';
    const IDENTIFIER = 'identifier';
    const TITLE = 'title';
    /**#@-*/

    /**
     * CMS page cache tag
     */
    const CACHE_TAG = 'cms_page';

    /**
     * @var string
     */
    protected $_cacheTag = 'cms_page';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'cms_page';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Cms\Model\Resource\Page');
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->_getData(self::PAGE_ID);
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return (string) $this->_getData(self::IDENTIFIER);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->_getData(self::TITLE);
    }

    /**
     * Load object data
     *
     * @param int|null $id
     * @param string $field
     * @return $this
     */
    public function load($id, $field = null)
    {
        if (is_null($id)) {
            return $this->noRoutePage();
        }
        return parent::load($id, $field);
    }

    /**
     * Load No-Route Page
     *
     * @return \Magento\Cms\Model\Page
     */
    public function noRoutePage()
    {
        return $this->load(self::NOROUTE_PAGE_ID, $this->getIdFieldName());
    }

    /**
     * Check if page identifier exist for specific store
     * return page id if page exists
     *
     * @param string $identifier
     * @param int $storeId
     * @return int
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
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
