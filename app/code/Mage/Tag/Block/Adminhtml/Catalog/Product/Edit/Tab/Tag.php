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
 * @category   Mage
 * @package    Mage_Tag
 * @copyright  Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Products tags tab
 *
 * @category   Mage
 * @package    Mage_Tag
 * @author     Magento Core Team <core@magentocommerce.com>
 *
 * @method     Mage_Tag_Block_Adminhtml_Catalog_Product_Edit_Tab_Tag setTitle() setTitle(string $title)
 * @method     array getTitle() getTitle()
 */

class Mage_Tag_Block_Adminhtml_Catalog_Product_Edit_Tab_Tag
    extends Mage_Backend_Block_Template
    implements Mage_Backend_Block_Widget_Tab_Interface
{
    /**
     * Id of current tab
     */
    const TAB_ID = 'tags';

    /**
     * @param Mage_Backend_Block_Template_Context $context
     * @param array $data
     */
    public function __construct(Mage_Backend_Block_Template_Context $context, array $data = array())
    {
        parent::__construct($context, $data);

        $this->setId(self::TAB_ID);
        $this->setTitle($this->_helperFactory->get('Mage_Tag_Helper_Data')->__('Product Tags'));
    }

    /**
     * Tab label getter
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->getTitle();
    }

    /**
     * Tab title getter
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTitle();
    }

    /**
     * Check whether tab can be showed
     *
     * @return bool
     */
    public function canShowTab()
    {
        return $this->_authorization->isAllowed('Mage_Tag::tag_all');
    }

    /**
     * Check whether tab should be hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Tab class getter
     *
     * @return string
     */
    public function getTabClass()
    {
        return 'ajax';
    }

    /**
     * Tab URL getter
     *
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl('*/*/tagGrid', array('_current' => true));
    }

    /**
     * Retrieve id of tab after which current tab will be rendered
     *
     * @return string
     */
    public function getAfter()
    {
        return 'product-reviews';
    }

    /**
     * @return string
     */
    public function getGroupCode()
    {
        return Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs::ADVANCED_TAB_GROUP_CODE;
    }
}
