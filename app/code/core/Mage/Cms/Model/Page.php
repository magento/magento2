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
 * @category    Mage
 * @package     Mage_Cms
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Cms Page Model
 *
 * @method Mage_Cms_Model_Resource_Page _getResource()
 * @method Mage_Cms_Model_Resource_Page getResource()
 * @method string getTitle()
 * @method Mage_Cms_Model_Page setTitle(string $value)
 * @method string getRootTemplate()
 * @method Mage_Cms_Model_Page setRootTemplate(string $value)
 * @method string getMetaKeywords()
 * @method Mage_Cms_Model_Page setMetaKeywords(string $value)
 * @method string getMetaDescription()
 * @method Mage_Cms_Model_Page setMetaDescription(string $value)
 * @method string getIdentifier()
 * @method Mage_Cms_Model_Page setIdentifier(string $value)
 * @method string getContentHeading()
 * @method Mage_Cms_Model_Page setContentHeading(string $value)
 * @method string getContent()
 * @method Mage_Cms_Model_Page setContent(string $value)
 * @method string getCreationTime()
 * @method Mage_Cms_Model_Page setCreationTime(string $value)
 * @method string getUpdateTime()
 * @method Mage_Cms_Model_Page setUpdateTime(string $value)
 * @method int getIsActive()
 * @method Mage_Cms_Model_Page setIsActive(int $value)
 * @method int getSortOrder()
 * @method Mage_Cms_Model_Page setSortOrder(int $value)
 * @method string getLayoutUpdateXml()
 * @method Mage_Cms_Model_Page setLayoutUpdateXml(string $value)
 * @method string getCustomTheme()
 * @method Mage_Cms_Model_Page setCustomTheme(string $value)
 * @method string getCustomRootTemplate()
 * @method Mage_Cms_Model_Page setCustomRootTemplate(string $value)
 * @method string getCustomLayoutUpdateXml()
 * @method Mage_Cms_Model_Page setCustomLayoutUpdateXml(string $value)
 * @method string getCustomThemeFrom()
 * @method Mage_Cms_Model_Page setCustomThemeFrom(string $value)
 * @method string getCustomThemeTo()
 * @method Mage_Cms_Model_Page setCustomThemeTo(string $value)
 *
 * @category    Mage
 * @package     Mage_Cms
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Cms_Model_Page extends Mage_Core_Model_Abstract
{
    const NOROUTE_PAGE_ID = 'no-route';

    /**
     * Page's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    const CACHE_TAG              = 'cms_page';
    protected $_cacheTag         = 'cms_page';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'cms_page';

    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('Mage_Cms_Model_Resource_Page');
    }

    /**
     * Load object data
     *
     * @param mixed $id
     * @param string $field
     * @return Mage_Cms_Model_Page
     */
    public function load($id, $field=null)
    {
        if (is_null($id)) {
            return $this->noRoutePage();
        }
        return parent::load($id, $field);
    }

    /**
     * Load No-Route Page
     *
     * @return Mage_Cms_Model_Page
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
        $statuses = new Varien_Object(array(
            self::STATUS_ENABLED => Mage::helper('Mage_Cms_Helper_Data')->__('Enabled'),
            self::STATUS_DISABLED => Mage::helper('Mage_Cms_Helper_Data')->__('Disabled'),
        ));

        Mage::dispatchEvent('cms_page_get_available_statuses', array('statuses' => $statuses));

        return $statuses->getData();
    }
}
