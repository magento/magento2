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
namespace Magento\Cms\Model;

/**
 * Cms Page Model
 *
 * @method \Magento\Cms\Model\Resource\Page _getResource()
 * @method \Magento\Cms\Model\Resource\Page getResource()
 * @method string getTitle()
 * @method \Magento\Cms\Model\Page setTitle(string $value)
 * @method string getPageLayout()
 * @method \Magento\Cms\Model\Page setPageLayout(string $value)
 * @method string getMetaKeywords()
 * @method \Magento\Cms\Model\Page setMetaKeywords(string $value)
 * @method string getMetaDescription()
 * @method \Magento\Cms\Model\Page setMetaDescription(string $value)
 * @method string getIdentifier()
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
class Page extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\Object\IdentityInterface
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
        return array(self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled'));
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return array(self::CACHE_TAG . '_' . $this->getId());
    }
}
