<?php
/**
 * Obsolete classes
 *
 * Format: array(<class_name>[, <replacement>])
 *
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
return array(
    array('Mage_Admin_Helper_Data', 'Magento\Backend\Helper\Data'),
    array('Mage_Admin_Model_Acl', 'Magento_Acl'),
    array('Mage_Admin_Model_Acl_Role'),
    array('Mage_Admin_Model_Acl_Resource', 'Magento\Framework\Acl\Resource'),
    array('Mage_Admin_Model_Acl_Role_Registry', 'Magento\Framework\Acl\Role\Registry'),
    array('Mage_Admin_Model_Acl_Role_Generic', 'Magento\Authorization\Model\Acl\Role\Generic'),
    array('Mage_Admin_Model_Acl_Role_Group', 'Magento\Authorization\Model\Acl\Role\Group'),
    array('Mage_Admin_Model_Acl_Role_User', 'Magento\Authorization\Model\Acl\Role\User'),
    array('Mage_Admin_Model_Resource_Acl', 'Magento\User\Model\Resource\Acl'),
    array('Mage_Admin_Model_Observer'),
    array('Mage_Admin_Model_Session', 'Magento\Backend\Model\Auth\Session'),
    array('Mage_Admin_Model_Resource_Acl_Role'),
    array('Mage_Admin_Model_Resource_Acl_Role_Collection'),
    array('Mage_Admin_Model_User', 'Magento\User\Model\User'),
    array('Mage_Admin_Model_Config'),
    array('Mage_Admin_Model_Resource_User', 'Magento\User\Model\Resource\User'),
    array('Mage_Admin_Model_Resource_User_Collection', 'Magento\User\Model\Resource\User\Collection'),
    array('Mage_Admin_Model_Role', 'Magento\Authorization\Model\Role'),
    array('Mage_Admin_Model_Roles', 'Magento\Authorization\Model\Roles'),
    array('Mage_Admin_Model_Rules', 'Magento\Authorization\Model\Rules'),
    array('Mage_Admin_Model_Resource_Role', 'Magento\Authorization\Model\Resource\Role'),
    array('Mage_Admin_Model_Resource_Roles', 'Magento\User\Model\Resource\Roles'),
    array('Mage_Admin_Model_Resource_Rules', 'Magento\Authorization\Model\Resource\Rules'),
    array('Mage_Admin_Model_Resource_Role_Collection', 'Magento\Authorization\Model\Resource\Role\Collection'),
    array('Mage_Admin_Model_Resource_Roles_Collection', 'Magento\User\Model\Resource\Roles\Collection'),
    array('Mage_Admin_Model_Resource_Roles_User_Collection', 'Magento\User\Model\Resource\Roles\User\Collection'),
    array('Mage_Admin_Model_Resource_Rules_Collection', 'Magento\Authorization\Model\Resource\Rules\Collection'),
    array(
        'Mage_Admin_Model_Resource_Permissions_Collection',
        'Magento\Authorization\Model\Resource\Permissions\Collection'
    ),
    array('Mage_Adminhtml_Block_Abstract', 'Magento\Backend\Block\AbstractBlock'),
    array('Mage_Adminhtml_Block_Backup_Grid'),
    array('Mage_Adminhtml_Block_Cache_Grid'),
    array('Mage_Adminhtml_Block_Catalog'),
    array('Mage_Adminhtml_Block_Catalog_Product_Attribute_Set_Grid'),
    array('Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Config_Grid'),
    array('Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Group_Grid'),
    array('Mage_Adminhtml_Block_Catalog_Search_Grid'),
    array('Mage_Adminhtml_Block_Cms_Block_Grid'),
    array('Mage_Adminhtml_Block_Customer_Group_Grid'),
    array('Mage_Adminhtml_Block_Customer_Online_Grid'),
    array('Mage_Adminhtml_Block_Newsletter_Problem_Grid'),
    array('Mage_Adminhtml_Block_Newsletter_Queue'),
    array('Mage_Adminhtml_Block_Newsletter_Queue_Grid'),
    array('Mage_Adminhtml_Block_Page_Menu', 'Magento\Backend\Block\Menu'),
    array('Mage_Adminhtml_Block_Permissions_User'),
    array('Mage_Adminhtml_Block_Permissions_User_Grid'),
    array('Mage_Adminhtml_Block_Permissions_User_Edit'),
    array('Mage_Adminhtml_Block_Permissions_User_Edit_Tabs'),
    array('Mage_Adminhtml_Block_Permissions_User_Edit_Tab_Main'),
    array('Mage_Adminhtml_Block_Permissions_User_Edit_Tab_Roles'),
    array('Mage_Adminhtml_Block_Permissions_User_Edit_Form'),
    array('Mage_Adminhtml_Block_Permissions_Role'),
    array('Mage_Adminhtml_Block_Permissions_Buttons'),
    array('Mage_Adminhtml_Block_Permissions_Role_Grid_User'),
    array('Mage_Adminhtml_Block_Permissions_Grid_Role'),
    array('Mage_Adminhtml_Block_Permissions_Grid_User'),
    array('Mage_Adminhtml_Block_Permissions_Tab_Roleinfo'),
    array('Mage_Adminhtml_Block_Permissions_Tab_Rolesedit'),
    array('Mage_Adminhtml_Block_Permissions_Tab_Rolesusers'),
    array('Mage_Adminhtml_Block_Permissions_Tab_Useredit'),
    array('Mage_Adminhtml_Block_Permissions_Editroles'),
    array('Mage_Adminhtml_Block_Permissions_Roles'),
    array('Mage_Adminhtml_Block_Permissions_Users'),
    array('Mage_Adminhtml_Block_Permissions_Edituser'),
    array('Mage_Adminhtml_Block_Permissions_Tab_Userroles'),
    array('Mage_Adminhtml_Block_Permissions_Usernroles'),
    array('Mage_Adminhtml_Block_Promo_Catalog_Grid'),
    array('Mage_Adminhtml_Block_Promo_Quote_Grid'),
    array('Mage_Adminhtml_Block_Rating_Grid'),
    array('Mage_Adminhtml_Block_System_Store_Grid'),
    array('Mage_Adminhtml_Permissions_UserController'),
    array('Mage_Adminhtml_Permissions_RoleController'),
    array('Mage_Adminhtml_Block_Report_Grid', 'Magento\Reports\Block\Adminhtml\Grid'),
    array('Mage_Adminhtml_Block_Report_Customer_Accounts', 'Magento\Reports\Block\Adminhtml\Customer\Accounts'),
    array('Mage_Adminhtml_Block_Report_Customer_Accounts_Grid'),
    array('Mage_Adminhtml_Block_Report_Customer_Totals', 'Magento\Reports\Block\Adminhtml\Customer\Totals'),
    array('Mage_Adminhtml_Block_Report_Customer_Totals_Grid'),
    array('Mage_Adminhtml_Block_Report_Product_Sold', 'Magento\Reports\Block\Adminhtml\Product\Sold'),
    array('Mage_Adminhtml_Block_Report_Product_Sold_Grid'),
    array('Mage_Adminhtml_Block_Report_Review_Customer_Grid'),
    array('Mage_Adminhtml_Block_Report_Customer_Orders', 'Magento\Reports\Block\Adminhtml\Customer\Orders'),
    array('Mage_Adminhtml_Block_Report_Customer_Orders_Grid'),
    array('Mage_Adminhtml_Block_Report_Product_Ordered'),
    array('Mage_Adminhtml_Block_Report_Product_Ordered_Grid'),
    array('Mage_Adminhtml_Block_Report_Review_Product_Grid'),
    array('Mage_Adminhtml_Block_Report_Refresh_Statistics', 'Magento\Reports\Block\Adminhtml\Refresh\Statistics'),
    array('Mage_Adminhtml_Block_Report_Refresh_Statistics_Grid'),
    array('Mage_Adminhtml_Block_Report_Search_Grid'),
    array('Mage_Adminhtml_Block_Sales'),
    array('Magento\GoogleCheckout'), // removed module
    array('Magento\Sales\Block\Adminhtml\Order\Shipment\Create\Form', 'Magento\Shipping\Block\Adminhtml\Create\Form'),
    array(
        'Magento\Sales\Block\Adminhtml\Order\Shipment\Create\Items',
        'Magento\Shipping\Block\Adminhtml\Create\Items'
    ),
    array(
        'Magento\Sales\Block\Adminhtml\Order\Shipment\View\Comments',
        'Magento\Shipping\Block\Adminhtml\View\Comments'
    ),
    array('Magento\Sales\Block\Adminhtml\Order\Shipment\View\Form', 'Magento\Shipping\Block\Adminhtml\View\Form'),
    array('Magento\Sales\Block\Adminhtml\Order\Shipment\View\Items', 'Magento\Shipping\Block\Adminhtml\View\Items'),
    array('Magento\Sales\Block\Adminhtml\Order\Shipment\Create', 'Magento\Shipping\Block\Adminhtml\Create'),
    array('Magento\Sales\Block\Adminhtml\Order\Shipment\View', 'Magento\Shipping\Block\Adminhtml\View'),
    array('Magento\Sales\Block\Order\Shipment\Items', 'Magento\Shipping\Block\Items'),
    array('Magento\Sales\Controller\Adminhtml\Order\Shipment', 'Magento\Shipping\Controller\Adminhtml\Order\Shipment'),
    array('Magento\Sales\Block\Order\Shipment', 'Magento\Shipping\Block\Order\Shipment'),
    array('Mage_Adminhtml_Block_Sales_Order_Create_Customer_Grid'),
    array('Mage_Adminhtml_Block_Sales_Order_Create_Search_Grid_Renderer_Giftmessage'),
    array('Mage_Adminhtml_Block_Sales_Order_Status_Grid'),
    array('Mage_Adminhtml_Block_Sitemap_Grid'),
    array('Mage_Adminhtml_Block_System_Config_Edit', 'Magento\Backend\Block\System\Config\Edit'),
    array('Mage_Adminhtml_Block_System_Config_Form', 'Magento\Backend\Block\System\Config\Form'),
    array('Mage_Adminhtml_Block_System_Config_Tabs', 'Magento\Backend\Block\System\Config\Tabs'),
    array(
        'Mage_Adminhtml_Block_System_Config_System_Storage_Media_Synchronize',
        'Magento\Backend\Block\System\Config\System\Storage\Media\Synchronize'
    ),
    array(
        'Mage_Adminhtml_Block_System_Config_Form_Fieldset_Modules_DisableOutput',
        'Magento\Backend\Block\System\Config\Form\Fieldset\Modules\DisableOutput'
    ),
    array(
        'Mage_Adminhtml_Block_System_Config_Form_Field_Regexceptions',
        'Magento\Backend\Block\System\Config\Form\Field\Regexceptions'
    ),
    array(
        'Mage_Adminhtml_Block_System_Config_Form_Field_Notification',
        'Magento\Backend\Block\System\Config\Form\Field\Notification'
    ),
    array(
        'Mage_Adminhtml_Block_System_Config_Form_Field_Heading',
        'Magento\Backend\Block\System\Config\Form\Field\Heading'
    ),
    array(
        'Mage_Adminhtml_Block_System_Config_Form_Field_Datetime',
        'Magento\Backend\Block\System\Config\Form\Field\Datetime'
    ),
    array(
        'Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract',
        'Magento\Backend\Block\System\Config\Form\Field\Array\AbstractArray'
    ),
    array('Mage_Adminhtml_Block_System_Config_Form_Fieldset', 'Magento\Backend\Block\System\Config\Form\Fieldset'),
    array('Mage_Adminhtml_Block_System_Config_Form_Field', 'Magento\Backend\Block\System\Config\Form\Field'),
    array(
        'Mage_Adminhtml_Block_System_Config_Form_Field_Import',
        'Magento\Backend\Block\System\Config\Form\Field\Import'
    ),
    array(
        'Mage_Adminhtml_Block_System_Config_Form_Field_Image',
        'Magento\Backend\Block\System\Config\Form\Field\Image'
    ),
    array(
        'Mage_Adminhtml_Block_System_Config_Form_Field_Export',
        'Magento\Backend\Block\System\Config\Form\Field\Export'
    ),
    array(
        'Mage_Adminhtml_Block_System_Config_Form_Field_Select_Allowspecific',
        'Magento\Backend\Block\System\Config\Form\Field\Select\Allowspecific'
    ),
    array('Mage_Adminhtml_Block_System_Config_Form_Field_File', 'Magento\Backend\Block\System\Config\Form\Field\File'),
    array(
        'Mage_Adminhtml_Block_System_Config_Form_Field_Select_Flatproduct',
        'Magento\Catalog\Block\Adminhtml\System\Config\Form\Field\Select\Flatproduct'
    ),
    array(
        'Mage_Adminhtml_Block_System_Config_Form_Field_Select_Flatcatalog',
        'Magento\Catalog\Block\Adminhtml\System\Config\Form\Field\Select\Flatcatalog'
    ),
    array(
        'Mage_Adminhtml_Block_System_Config_Form_Fieldset_Order_Statuses',
        'Magento\Sales\Block\Adminhtml\System\Config\Form\Fieldset\Order\Statuses'
    ),
    array('Mage_Adminhtml_Block_System_Config_Dwstree', 'Magento\Backend\Block\System\Config\Dwstree'),
    array('Mage_Adminhtml_Block_System_Config_Switcher', 'Magento\Backend\Block\System\Config\Switcher'),
    array('Mage_Adminhtml_Block_System_Design_Grid'),
    array('Magento\Adminhtml\Block\System\Email\Template', 'Magento\Email\Block\Adminhtml\Template'),
    array('Magento\Adminhtml\Block\System\Email\Template\Edit', 'Magento\Email\Block\Adminhtml\Template\Edit'),
    array(
        'Magento\Adminhtml\Block\System\Email\Template\Edit\Form',
        'Magento\Email\Block\Adminhtml\Template\Edit\Form'
    ),
    array('Magento\Adminhtml\Block\System\Email\Template\Preview', 'Magento\Email\Block\Adminhtml\Template\Preview'),
    array('Mage_Adminhtml_Block_System_Email_Template_Grid'),
    array(
        'Magento\Adminhtml\Block\System\Email\Template\Grid\Renderer\Action',
        'Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Action'
    ),
    array(
        'Magento\Adminhtml\Block\System\Email\Template\Grid\Renderer\Sender',
        'Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Sender'
    ),
    array(
        'Magento\Adminhtml\Block\System\Email\Template\Grid\Renderer\Type',
        'Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Type'
    ),
    array(
        'Magento\Adminhtml\Block\System\Email\Template\Grid\Filter\Type',
        'Magento\Email\Block\Adminhtml\Template\Grid\Filter\Type'
    ),
    array('Mage_Adminhtml_Block_System_Variable_Grid'),
    array('Mage_Adminhtml_Block_Store_Switcher', 'Magento\Backend\Block\Store\Switcher'),
    array(
        'Mage_Adminhtml_Block_Store_Switcher_Form_Renderer_Fieldset',
        'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset'
    ),
    array(
        'Mage_Adminhtml_Block_Store_Switcher_Form_Renderer_Fieldset_Element',
        'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
    ),
    array('Mage_Adminhtml_Block_Tag_Tag_Edit'),
    array('Mage_Adminhtml_Block_Tag_Tag_Edit_Form'),
    array('Mage_Adminhtml_Block_Tax_Rate_Grid'),
    array('Mage_Adminhtml_Block_Tax_Rule_Grid'),
    array('Mage_Adminhtml_Block_Tree'),
    array('Mage_Adminhtml_Block_Urlrewrite_Grid'),
    array('Magento\Adminhtml\Controller\System\Email\Template', 'Magento\Email\Controller\Adminhtml\Template'),
    array('Mage_Adminhtml_Helper_Rss'),
    array('Mage_Adminhtml_Model_Config', 'Magento\Backend\Model\Config\Structure'),
    array('Mage_Adminhtml_Model_Config_Data', 'Magento\Backend\Model\Config'),
    array('Magento\Adminhtml\Model\Email\Template', 'Magento\Email\Model\Adminhtml\Template'),
    array('Mage_Adminhtml_Model_Extension'),
    array('Mage_Adminhtml_Model_System_Config_Source_Shipping_Allowedmethods'),
    array(
        'Mage_Adminhtml_Model_System_Config_Backend_Admin_Password_Link_Expirationperiod',
        'Magento\Backend\Model\Config\Backend\Admin\Password\Link\Expirationperiod'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Backend_Admin_Custom',
        'Magento\Backend\Model\Config\Backend\Admin\Custom'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Backend_Admin_Custompath',
        'Magento\Backend\Model\Config\Backend\Admin\Custompath'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Backend_Admin_Observer',
        'Magento\Backend\Model\Config\Backend\Admin\Observer'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Backend_Admin_Robots',
        'Magento\Backend\Model\Config\Backend\Admin\Robots'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Backend_Admin_Usecustom',
        'Magento\Backend\Model\Config\Backend\Admin\Usecustom'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Backend_Admin_Usecustompath',
        'Magento\Backend\Model\Config\Backend\Admin\Custompath'
    ),
    array(
        'Magento\Backend\Model\Config\Backend\Admin\Usecustompath',
        'Magento\Backend\Model\Config\Backend\Admin\Custompath'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Backend_Admin_Usesecretkey',
        'Magento\Backend\Model\Config\Backend\Admin\Usesecretkey'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Backend_Catalog_Inventory_Managestock',
        'Magento\CatalogInventory\Model\Config\Backend\Managestock'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Backend_Catalog_Search_Type',
        'Magento\CatalogSearch\Model\Config\Backend\Search\Type'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Backend_Currency_Abstract',
        'Magento\Backend\Model\Config\Backend\Currency\AbstractCurrency'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Backend_Currency_Allow',
        'Magento\Backend\Model\Config\Backend\Currency\Allow'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Backend_Currency_Base',
        'Magento\Backend\Model\Config\Backend\Currency\Base'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Backend_Currency_Cron',
        'Magento\Backend\Model\Config\Backend\Currency\Cron'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Backend_Currency_Default',
        'Magento\Backend\Model\Config\Backend\Currency\DefaultCurrency'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Backend_Customer_Address_Street',
        'Magento\Customer\Model\Config\Backend\Address\Street'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Backend_Customer_Password_Link_Expirationperiod',
        'Magento\Customer\Model\Config\Backend\Password\Link\Expirationperiod'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Backend_Customer_Show_Address',
        'Magento\Customer\Model\Config\Backend\Show\Address'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Backend_Customer_Show_Customer',
        'Magento\Customer\Model\Config\Backend\Show\Customer'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Backend_Design_Exception',
        'Magento\Backend\Model\Config\Backend\Design\Exception'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Backend_Email_Address',
        'Magento\Backend\Model\Config\Backend\Email\Address'
    ),
    array('Mage_Adminhtml_Model_System_Config_Backend_Email_Logo', 'Magento\Backend\Model\Config\Backend\Email\Logo'),
    array(
        'Mage_Adminhtml_Model_System_Config_Backend_Email_Sender',
        'Magento\Backend\Model\Config\Backend\Email\Sender'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Backend_Image_Adapter',
        'Magento\Backend\Model\Config\Backend\Image\Adapter'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Backend_Image_Favicon',
        'Magento\Backend\Model\Config\Backend\Image\Favicon'
    ),
    array('Mage_Adminhtml_Model_System_Config_Backend_Image_Pdf', 'Magento\Backend\Model\Config\Backend\Image\Pdf'),
    array(
        'Mage_Adminhtml_Model_System_Config_Backend_Locale_Timezone',
        'Magento\Backend\Model\Config\Backend\Locale\Timezone'
    ),
    array('Mage_Adminhtml_Model_System_Config_Backend_Log_Cron', 'Magento\Backend\Model\Config\Backend\Log\Cron'),
    array('Mage_Adminhtml_Model_System_Config_Backend_Price_Scope'),
    array(
        'Mage_Adminhtml_Model_System_Config_Backend_Product_Alert_Cron',
        'Magento\Cron\Model\Config\Backend\Product\Alert'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Backend_Seo_Product',
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Backend_Serialized_Array',
        'Magento\Backend\Model\Config\Backend\Serialized\Array'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Backend_Shipping_Tablerate',
        'Magento\OfflineShipping\Model\Config\Backend\Tablerate'
    ),
    array('Mage_Adminhtml_Model_System_Config_Backend_Sitemap_Cron', 'Magento\Cron\Model\Config\Backend\Sitemap'),
    array(
        'Mage_Adminhtml_Model_System_Config_Backend_Storage_Media_Database',
        'Magento\Backend\Model\Config\Backend\Storage\Media\Database'
    ),
    array('Mage_Adminhtml_Model_System_Config_Backend_Baseurl', 'Magento\Backend\Model\Config\Backend\Baseurl'),
    array('Mage_Adminhtml_Model_System_Config_Backend_Cache', 'Magento\Backend\Model\Config\Backend\Cache'),
    array('Mage_Adminhtml_Model_System_Config_Backend_Category', 'Magento\Catalog\Model\Config\Backend\Category'),
    array('Mage_Adminhtml_Model_System_Config_Backend_Cookie', 'Magento\Backend\Model\Config\Backend\Cookie'),
    array('Mage_Adminhtml_Model_System_Config_Backend_Datashare', 'Magento\Backend\Model\Config\Backend\Datashare'),
    array('Mage_Adminhtml_Model_System_Config_Backend_Encrypted', 'Magento\Backend\Model\Config\Backend\Encrypted'),
    array('Mage_Adminhtml_Model_System_Config_Backend_File', 'Magento\Backend\Model\Config\Backend\File'),
    array('Mage_Adminhtml_Model_System_Config_Backend_Filename', 'Magento\Backend\Model\Config\Backend\Filename'),
    array('Mage_Adminhtml_Model_System_Config_Backend_Image', 'Magento\Backend\Model\Config\Backend\Image'),
    array('Mage_Adminhtml_Model_System_Config_Backend_Locale', 'Magento\Backend\Model\Config\Backend\Locale'),
    array('Mage_Adminhtml_Model_System_Config_Backend_Secure', 'Magento\Backend\Model\Config\Backend\Secure'),
    array('Mage_Adminhtml_Model_System_Config_Backend_Serialized', 'Magento\Backend\Model\Config\Backend\Serialized'),
    array('Mage_Adminhtml_Model_System_Config_Backend_Sitemap', 'Magento\Sitemap\Model\Config\Backend\Priority'),
    array('Mage_Adminhtml_Model_System_Config_Backend_Store', 'Magento\Backend\Model\Config\Backend\Store'),
    array('Mage_Adminhtml_Model_System_Config_Backend_Translate', 'Magento\Backend\Model\Config\Backend\Translate'),
    array(
        'Mage_Adminhtml_Model_System_Config_Clone_Media_Image',
        'Magento\Catalog\Model\Config\CatalogClone\Media\Image'
    ),
    array('Mage_Adminhtml_Model_System_Config_Source_Admin_Page', 'Magento\Backend\Model\Config\Source\Admin\Page'),
    array(
        'Mage_Adminhtml_Model_System_Config_Source_Catalog_Search_Type',
        'Magento\CatalogSearch\Model\Config\Source\Search\Type'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Source_Catalog_GridPerPage',
        'Magento\Catalog\Model\Config\Source\GridPerPage'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Source_Catalog_ListMode',
        'Magento\Catalog\Model\Config\Source\ListMode'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Source_Catalog_ListPerPage',
        'Magento\Catalog\Model\Config\Source\ListPerPage'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Source_Catalog_ListSort',
        'Magento\Catalog\Model\Config\Source\ListSort'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Source_Catalog_TimeFormat',
        'Magento\Catalog\Model\Config\Source\TimeFormat'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Source_Cms_Wysiwyg_Enabled',
        'Magento\Cms\Model\Config\Source\Wysiwyg\Enabled'
    ),
    array('Mage_Adminhtml_Model_System_Config_Source_Cms_Page', 'Magento\Cms\Model\Config\Source\Page'),
    array(
        'Mage_Adminhtml_Model_System_Config_Source_Country_Full',
        'Magento\Directory\Model\Config\Source\Country\Full'
    ),
    array('Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency', 'Magento\Cron\Model\Config\Source\Frequency'),
    array(
        'Mage_Adminhtml_Model_System_Config_Source_Currency_Service',
        'Magento\Backend\Model\Config\Source\Currency'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Source_Customer_Address_Type',
        'Magento\Customer\Model\Config\Source\Address\Type'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Source_Customer_Group_Multiselect',
        'Magento\Customer\Model\Config\Source\Group\Multiselect'
    ),
    array('Mage_Adminhtml_Model_System_Config_Source_Customer_Group', 'Magento\Customer\Model\Config\Source\Group'),
    array('Mage_Adminhtml_Model_System_Config_Source_Date_Short', 'Magento\Backend\Model\Config\Source\Date\Short'),
    array('Mage_Adminhtml_Model_System_Config_Source_Design_Package'),
    array(
        'Mage_Adminhtml_Model_System_Config_Source_Design_Robots',
        'Magento\Backend\Model\Config\Source\Design\Robots'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Source_Dev_Dbautoup',
        'Magento\Backend\Model\Config\Source\Dev\Dbautoup'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Source_Email_Identity',
        'Magento\Backend\Model\Config\Source\Email\Identity'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Source_Email_Method',
        'Magento\Backend\Model\Config\Source\Email\Method'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Source_Email_Smtpauth',
        'Magento\Backend\Model\Config\Source\Email\Smtpauth'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Source_Email_Template',
        'Magento\Backend\Model\Config\Source\Email\Template'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Source_Image_Adapter',
        'Magento\Backend\Model\Config\Source\Image\Adapter'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Source_Locale_Country',
        'Magento\Backend\Model\Config\Source\Locale\Country'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Source_Locale_Currency_All',
        'Magento\Backend\Model\Config\Source\Locale\Currency\All'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Source_Locale_Currency',
        'Magento\Backend\Model\Config\Source\Locale\Currency'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Source_Locale_Timezone',
        'Magento\Backend\Model\Config\Source\Locale\Timezone'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Source_Locale_Weekdays',
        'Magento\Backend\Model\Config\Source\Locale\Weekdays'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Source_Notification_Frequency',
        'Magento\AdminNotification\Model\Config\Source\Frequency'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Source_Order_Status_New',
        'Magento\Sales\Model\Config\Source\Order\Status\NewStatus'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Source_Order_Status_Newprocessing',
        'Magento\Sales\Model\Config\Source\Order\Status\Newprocessing'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Source_Order_Status_Processing',
        'Magento\Sales\Model\Config\Source\Order\Status\Processing'
    ),
    array('Mage_Adminhtml_Model_System_Config_Source_Order_Status', 'Magento\Sales\Model\Config\Source\Order\Status'),
    array(
        'Mage_Adminhtml_Model_System_Config_Source_Payment_Allmethods',
        'Magento\Payment\Model\Config\Source\Allmethods'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Source_Payment_Allowedmethods',
        'Magento\Payment\Model\Config\Source\Allowedmethods'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Source_Payment_Allspecificcountries',
        'Magento\Payment\Model\Config\Source\Allspecificcountries'
    ),
    array('Mage_Adminhtml_Model_System_Config_Source_Payment_Cctype', 'Magento\Payment\Model\Config\Source\Cctype'),
    array('Mage_Adminhtml_Model_System_Config_Source_Price_Scope', 'Magento\Catalog\Model\Config\Source\Price\Scope'),
    array('Mage_Adminhtml_Model_System_Config_Source_Price_Step', 'Magento\Catalog\Model\Config\Source\Price\Step'),
    array(
        'Mage_Adminhtml_Model_System_Config_Source_Product_Options_Price',
        'Magento\Catalog\Model\Config\Source\Product\Options\Price'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Source_Product_Options_Type',
        'Magento\Catalog\Model\Config\Source\Product\Options\Type'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Source_Product_Thumbnail',
        'Magento\Catalog\Model\Config\Source\Product\Thumbnail'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Source_Reports_Scope',
        'Magento\Backend\Model\Config\Source\Reports\Scope'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Source_Shipping_Allmethods',
        'Magento\Shipping\Model\Config\Source\Allmethods'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Source_Shipping_Allspecificcountries',
        'Magento\Shipping\Model\Config\Source\Allspecificcountries'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Source_Shipping_Flatrate',
        'Magento\OfflineShipping\Model\Config\Source\Flatrate'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Source_Shipping_Tablerate',
        'Magento\OfflineShipping\Model\Config\Source\Tablerate'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Source_Shipping_Taxclass',
        'Magento\Tax\Model\TaxClass\Source\Product'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Source_Storage_Media_Database',
        'Magento\Backend\Model\Config\Source\Storage\Media\Database'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Source_Storage_Media_Storage',
        'Magento\Backend\Model\Config\Source\Storage\Media\Storage'
    ),
    array('Mage_Adminhtml_Model_System_Config_Source_Tax_Apply_On', 'Magento\Tax\Model\Config\Source\Apply\On'),
    array('Mage_Adminhtml_Model_System_Config_Source_Tax_Basedon', 'Magento\Tax\Model\Config\Source\Basedon'),
    array('Mage_Adminhtml_Model_System_Config_Source_Tax_Catalog', 'Magento\Tax\Model\Config\Source\Catalog'),
    array(
        'Mage_Adminhtml_Model_System_Config_Source_Watermark_Position',
        'Magento\Catalog\Model\Config\Source\Watermark\Position'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Source_Web_Protocol',
        'Magento\Backend\Model\Config\Source\Web\Protocol'
    ),
    array(
        'Mage_Adminhtml_Model_System_Config_Source_Web_Redirect',
        'Magento\Backend\Model\Config\Source\Web\Redirect'
    ),
    array('Mage_Adminhtml_Model_System_Config_Source_Allregion', 'Magento\Directory\Model\Config\Source\Allregion'),
    array('Mage_Adminhtml_Model_System_Config_Source_Category', 'Magento\Catalog\Model\Config\Source\Category'),
    array('Mage_Adminhtml_Model_System_Config_Source_Checktype', 'Magento\Backend\Model\Config\Source\Checktype'),
    array('Mage_Adminhtml_Model_System_Config_Source_Country', 'Magento\Directory\Model\Config\Source\Country'),
    array('Mage_Adminhtml_Model_System_Config_Source_Currency', 'Magento\Backend\Model\Config\Source\Currency'),
    array(
        'Mage_Adminhtml_Model_System_Config_Source_Enabledisable',
        'Magento\Backend\Model\Config\Source\Enabledisable'
    ),
    array('Mage_Adminhtml_Model_System_Config_Source_Frequency', 'Magento\Sitemap\Model\Config\Source\Frequency'),
    array('Mage_Adminhtml_Model_System_Config_Source_Locale', 'Magento\Backend\Model\Config\Source\Locale'),
    array('Mage_Adminhtml_Model_System_Config_Source_Nooptreq', 'Magento\Backend\Model\Config\Source\Nooptreq'),
    array('Mage_Adminhtml_Model_System_Config_Source_Store', 'Magento\Backend\Model\Config\Source\Store'),
    array('Mage_Adminhtml_Model_System_Config_Source_Website', 'Magento\Backend\Model\Config\Source\Website'),
    array('Mage_Adminhtml_Model_System_Config_Source_Yesno', 'Magento\Backend\Model\Config\Source\Yesno'),
    array(
        'Mage_Adminhtml_Model_System_Config_Source_Yesnocustom',
        'Magento\Backend\Model\Config\Source\Yesnocustom'
    ),
    array('Mage_Adminhtml_Model_System_Store', 'Magento\Store\Model\System\Store'),
    array('Mage_Adminhtml_Model_Url', 'Magento\Backend\Model\UrlInterface'),
    array('Mage_Adminhtml_Rss_CatalogController'),
    array('Mage_Adminhtml_Rss_OrderController'),
    array('Mage_Adminhtml_SystemController', 'Magento\Backend\Controller\Adminhtml\System'),
    array('Mage_Adminhtml_System_ConfigController', 'Magento\Backend\Controller\Adminhtml\System\Config'),
    array(
        'Magento\Backend\Model\Config\Source\Currency\Service',
        'Magento\Directory\Model\Currency\Import\Source\Service'
    ),
    array('Mage_Backend_Model_Menu_Config_Menu'),
    array('Mage_Backend_Model_Menu_Director_Dom'),
    array('Mage_Backend_Model_Menu_Factory', 'Mage_Backend_Model_MenuFactory'),
    array('Mage_Bundle_Product_EditController', 'Mage_Bundle_Controller_Adminhtml_Bundle_Selection'),
    array('Mage_Bundle_SelectionController', 'Mage_Bundle_Controller_Adminhtml_Bundle_Selection'),
    array('Magento\Catalog\Block\Adminhtml\System\Config\Form\Field\Select\Flatproduct'),
    array('Mage_Catalog_Model_Category_Limitation'),
    array('Mage_Catalog_Model_Convert'),
    array('Mage_Catalog_Model_Convert_Adapter_Catalog'),
    array('Mage_Catalog_Model_Convert_Adapter_Product'),
    array('Mage_Catalog_Model_Convert_Parser_Product'),
    array('Mage_Catalog_Model_Entity_Product_Attribute_Frontend_Image'),
    array('Magento\Catalog\Model\Product\Flat\Flag'),
    array('Magento\Catalog\Model\Product\Flat\Indexer'),
    array('Magento\Catalog\Model\Product\Flat\Observer'),
    array('Magento\Catalog\Model\Product\Indexer\Flat'),
    array('Mage_Catalog_Model_Product_Limitation'),
    array('Mage_Catalog_Model_Resource_Product_Attribute_Frontend_Image'),
    array('Mage_Catalog_Model_Resource_Product_Attribute_Frontend_Tierprice'),
    array(
        'Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab\Variations\Main',
        'Magento\ConfigurableProduct\Block\Adminhtml\Product\Attribute\Edit\Tab\Variations\Main'
    ),
    array(
        'Magento\Catalog\Block\Adminhtml\Product\Attribute\NewAttribute\Product\Created',
        'Magento\ConfigurableProduct\Block\Adminhtml\Product\Attribute\NewAttribute\Product\Created'
    ),
    array(
        'Magento\Catalog\Block\Adminhtml\Product\Composite\Fieldset\Configurable',
        'Magento\ConfigurableProduct\Block\Adminhtml\Product\Composite\Fieldset\Configurable'
    ),
    array('Magento\Catalog\Block\Adminhtml\Product\Created'),
    array(
        'Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Super\Config\Grid\Filter\Inventory',
        'Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Super\Config\Grid\Filter\Inventory'
    ),
    array(
        'Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Super\Config\Grid\Renderer\Checkbox',
        'Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Super\Config\Grid\Renderer\Checkbox'
    ),
    array(
        'Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Super\Config\Grid\Renderer\Inventory',
        'Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Super\Config\Grid\Renderer\Inventory'
    ),
    array(
        'Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Super\Config\Attribute',
        'Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Super\Config\Attribute'
    ),
    array(
        '\Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Super\Config\Matrix',
        '\Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Super\Config\Matrix'
    ),
    array(
        'Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Super\Config\Simple'
    ),
    array(
        'Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Super\Config',
        'Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Super\Config'
    ),
    array(
        '\Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Super\Settings',
        '\Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Super\Settings'
    ),
    array(
        'Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs\Configurable',
        'Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tabs\Configurable'
    ),
    array(
        'Magento\Catalog\Block\Product\Configurable\AssociatedSelector\Backend\Grid\ColumnSet',
        'Magento\ConfigurableProduct\Block\Product\Configurable\AssociatedSelector\Backend\Grid\ColumnSet'
    ),
    array(
        'Magento\Catalog\Block\Product\Configurable\AssociatedSelector\Renderer\Id',
        'Magento\ConfigurableProduct\Block\Product\Configurable\AssociatedSelector\Renderer\Id'
    ),
    array(
        'Magento\Catalog\Block\Product\Configurable\AttributeSelector',
        'Magento\ConfigurableProduct\Block\Product\Configurable\AttributeSelector'
    ),
    array(
        'Magento\Catalog\Block\Product\View\Type\Configurable',
        'Magento\ConfigurableProduct\Block\Product\View\Type\Configurable'
    ),
    array(
        'Magento\Catalog\Block\Layer\Filter\AbstractFilter',
        'Magento\LayeredNavigation\Block\Navigation\FilterRenderer'
    ),
    array('Magento\Catalog\Block\Layer\Filter\Attribute', 'Magento\LayeredNavigation\Block\Navigation\FilterRenderer'),
    array('Magento\Catalog\Block\Layer\Filter\Category', 'Magento\LayeredNavigation\Block\Navigation\FilterRenderer'),
    array('Magento\Catalog\Block\Layer\Filter\Decimal', 'Magento\LayeredNavigation\Block\Navigation\FilterRenderer'),
    array('Magento\Catalog\Block\Layer\Filter\Price', 'Magento\LayeredNavigation\Block\Navigation\FilterRenderer'),
    array('Magento\Catalog\Block\Layer\State', 'Magento\LayeredNavigation\Block\Navigation\State'),
    array('Magento\Catalog\Block\Layer\View', 'Magento\LayeredNavigation\Block\Navigation'),
    array('Magento\CatalogSearch\Block\Layer', 'Magento\LayeredNavigation\Block\Navigation'),
    array(
        'Magento\CatalogSearch\Block\Layer\Filter\Attribute',
        'Magento\LayeredNavigation\Block\Navigation\FilterRenderer'
    ),
    array('Magento\CatalogSearch\Model\Layer', 'Magento\Catalog\Model\Layer'),
    array('Magento\CatalogSearch\Model\Layer\Filter\Attribute'),
    array('Magento\Search\Block\Catalog\Layer\View', 'Magento\LayeredNavigation\Block\Navigation'),
    array(
        'Magento\Search\Block\Catalog\Layer\Filter\Attribute',
        'Magento\LayeredNavigation\Block\Navigation\FilterRenderer'
    ),
    array(
        'Magento\Search\Block\Catalog\Layer\Filter\Category',
        'Magento\LayeredNavigation\Block\Navigation\FilterRenderer'
    ),
    array(
        'Magento\Search\Block\Catalog\Layer\Filter\Decimal',
        'Magento\LayeredNavigation\Block\Navigation\FilterRenderer'
    ),
    array(
        'Magento\Search\Block\Catalog\Layer\Filter\Price',
        'Magento\LayeredNavigation\Block\Navigation\FilterRenderer'
    ),
    array(
        'Magento\Search\Block\Catalogsearch\Layer\Filter\Attribute',
        'Magento\LayeredNavigation\Block\Navigation\FilterRenderer'
    ),
    array('Magento\Search\Block\Catalogsearch\Layer', 'Magento\LayeredNavigation\Block\Navigation\FilterRenderer'),
    array('Magento\Search\Model\Catalog\Layer', 'Magento\Catalog\Model\Layer\Category'),
    array('Magento\Search\Model\Catalog\Layer\Filter\Attribute', 'Magento\Catalog\Model\Layer\Filter\Attribute'),
    array('Magento\Search\Model\Catalog\Layer\Filter\Category', 'Magento\Catalog\Model\Layer\Filter\Category'),
    array('Magento\Search\Model\Catalog\Layer\Filter\Decimal', 'Magento\Catalog\Model\Layer\Filter\Decimal'),
    array('Magento\Search\Model\Catalog\Layer\Filter\Price', 'Magento\Catalog\Model\Layer\Filter\Price'),
    array('Magento\Search\Model\Search\Layer\Filter\Attribute', 'Magento\Catalog\Model\Layer\Filter\Attribute'),
    array('Magento\Search\Model\Search\Layer', 'Magento\Catalog\Model\Layer'),
    array(
        'Magento\Catalog\Model\Product\Type\Configurable',
        'Magento\ConfigurableProduct\Model\Product\Type\Configurable'
    ),
    array(
        'Magento\Catalog\Model\Resource\Product\Type\Configurable\Attribute',
        'Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Attribute'
    ),
    array(
        'Magento\Catalog\Model\Resource\Product\Type\Configurable\Product\Collection',
        'Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Product\Collection'
    ),
    array(
        'Magento\Catalog\Model\Resource\Product\Type\Configurable\Attribute\Collection',
        'Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Attribute\Collection'
    ),
    array(
        'Magento\Catalog\Model\Resource\Product\Type\Configurable',
        'Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable'
    ),
    array(
        'Magento\Catalog\Model\Resource\Product\Indexer\Price\Configurable',
        'Magento\ConfigurableProduct\Model\Resource\Product\Indexer\Price\Configurable'
    ),
    array(
        'Magento\Catalog\Model\Product\Type\Configurable\Price',
        'Magento\ConfigurableProduct\Model\Product\Type\Configurable\Price'
    ),
    array(
        'Magento\Checkout\Block\Cart\Item\Renderer\Configurable',
        'Magento\ConfigurableProduct\Block\Cart\Item\Renderer\Configurable'
    ),
    array('Magento\Catalog\Model\Resource\Product\Flat\Indexer'),
    array('Magento\Catalog\Model\System\Config\Backend\Catalog\Product\Flat'),
    array('Mage_Checkout_Block_Links'),
    array('Mage_Core_Block_Flush'),
    array('Mage_Core_Block_Template_Facade'),
    array('Mage_Core_Block_Template_Smarty'),
    array('Mage_Core_Block_Template_Zend'),
    array('Mage_Core_Controller_Magento_Router_Admin', 'Magento\Backend\App\Router'),
    array('Mage_Core_Model_Convert'),
    array('Mage_Core_Model_Config_Fieldset', 'Magento\Core\Model\Fieldset\Config'),
    array('Mage_Core_Model_Config_Options', 'Magento\Framework\App\Filesystem'),
    array('Magento\Framework\App\Dir', 'Magento\Framework\Filesystem'),
    array('Magento\Framework\Filesystem\Adapter\Local', 'Magento\Framework\Filesystem\Driver\File'),
    array('Magento\Framework\Filesystem\Adapter\Zlib', 'Magento\Framework\Filesystem\Driver\Zlib'),
    array('Magento\Framework\Filesystem\AdapterInterface'),
    array('Magento\Framework\Filesystem\Stream\FactoryInterface'),
    array('Magento\Framework\Filesystem\Stream\Local'),
    array('Magento\Framework\Filesystem\Stream\Mode'),
    array('Magento\Framework\Filesystem\Stream\Zlib'),
    array('Magento\Framework\Filesystem\Stream\Mode\Zlib'),
    array('Mage_Core_Model_Config_Module'),
    array('Mage_Core_Model_Config_System'),
    array('Mage_Core_Model_Design_Source_Apply'),
    array('Mage_Core_Model_Language'),
    array('Magento\Core\Model\Flag', 'Magento\Framework\Flag'),
    array('Magento\Core\Exception', 'Magento\Framework\Model\Exception'),
    array('Magento\Core\Model\AbstractModel', 'Magento\Framework\Model\AbstractModel'),
    array('Magento\Core\Model\Email\Info', 'Magento\Framework\Mail\MessageInterface'),
    array('Magento\Core\Model\Email\Sender', 'Magento\Framework\Mail\Template\TransportBuilder'),
    array('Magento\Core\Model\Email\Template\Mailer', 'Magento\Framework\Mail\Template\TransportBuilder'),
    array('Magento\Core\Model\Resource\AbstractResource', 'Magento\Framework\Model\Resource\AbstractResource'),
    array('Magento\Core\Model\Resource\Db\AbstractDb', 'Magento\Framework\Model\Resource\Db\AbstractDb'),
    array('Magento\Core\Model\Resource\Db\Profiler', 'Magento\Framework\Model\Resource\Db\Profiler'),
    array(
        'Magento\Core\Model\Resource\Entity\AbstractEntity',
        'Magento\Framework\Model\Resource\Entity\AbstractEntity'
    ),
    array('Magento\Core\Model\Resource\Entity\Table', 'Magento\Framework\Model\Resource\Entity\Table'),
    array('Magento\Core\Model\Resource\Flag', 'Magento\Framework\Flag\Resource'),
    array('Magento\Core\Model\Resource\Iterator', 'Magento\Framework\Model\Resource\Iterator'),
    array('Magento\Core\Model\Resource\Resource', 'Magento\Install\Model\Resource\Resource'),
    array('Magento\Core\Model\Resource\Type\AbstractType', 'Magento\Framework\Model\Resource\Type\AbstractType'),
    array('Magento\Core\Model\Resource\Type\Db', 'Magento\Framework\Model\Resource\Type\Db'),
    array('Magento\Core\Model\Resource\Type\Db\Pdo\Mysql', 'Magento\Framework\Model\Resource\Type\Db\Pdo\Mysql'),
    array(
        'Magento\Core\Model\Resource\Db\Collection\AbstractCollection',
        'Magento\Framework\Model\Resource\Db\Collection\AbstractCollection'
    ),
    array('Magento\Email\Model\Info', 'Magento\Framework\Mail\MessageInterface'),
    array('Magento\Email\Model\Sender', 'Magento\Framework\Mail\Template\TransportBuilder'),
    array('Magento\Email\Model\Template\Mailer', 'Magento\Framework\Mail\Template\TransportBuilder'),
    array('Magento\Core\Model\Email\Template', 'Magento\Email\Model\Template'),
    array('Magento\Core\Model\Email\Transport', 'Magento\Email\Model\Transport'),
    array('Magento\Core\Model\Email\Template\Config', 'Magento\Email\Model\Template\Config'),
    array('Magento\Core\Model\Email\Template\Filter', 'Magento\Email\Model\Template\Filter'),
    array('Magento\Core\Model\Email\Template\Config\Converter', 'Magento\Email\Model\Template\Config\Converter'),
    array('Magento\Core\Model\Template\Config\Data', 'Magento\Email\Model\Template\Config\Data'),
    array('Magento\Core\Model\Template\Config\SchemaLocator', 'Magento\Email\Model\Template\Config\SchemaLocator'),
    array('Magento\Core\Model\Resource\Email\Template', 'Magento\Email\Model\Resource\Template'),
    array('Magento\Core\Model\Resource\Email\Template\Collection', 'Magento\Email\Model\Resource\Template\Collection'),
    array('Mage_Core_Model_Resource_Language'),
    array('Mage_Core_Model_Resource_Language_Collection'),
    array('Mage_Core_Model_Resource_Setup_Query_Modifier'),
    array('Mage_Core_Model_Session_Abstract_Varien'),
    array('Mage_Core_Model_Session_Abstract_Zend'),
    array('Magento\Core\Model\Source\Email\Variables', 'Magento\Email\Model\Source\Variables'),
    array('Magento\Core\Model\Store\ListInterface', 'Magento\Framework\StoreManagerInterface'),
    array('Magento\Core\Model\Store\StorageInterface', 'Magento\Framework\StoreManagerInterface'),
    array('Mage_Core_Model_Store_Group_Limitation'),
    array('Mage_Core_Model_Store_Limitation'),
    array('Magento\Core\Model\Variable\Observer'),
    array('Mage_Core_Model_Website_Limitation'),
    array('Mage_Core_Model_Layout_Data', 'Magento\Core\Model\Layout\Update'),
    array('Mage_Core_Model_Theme_Customization_Link'),
    array('Mage_Customer_Block_Account'),
    array('Mage_Customer_Block_Account_Navigation'),
    array('Mage_Customer_Model_Convert_Adapter_Customer'),
    array('Mage_Customer_Model_Convert_Parser_Customer'),
    array(
        'Mage_Customer_Model_Resource_Address_Attribute_Backend_Street',
        'Mage_Eav_Model_Entity_Attribute_Backend_Default'
    ),
    array('Mage_DesignEditor_Block_Page_Html_Head_Vde'),
    array('Mage_DesignEditor_Block_Page_Html_Head'),
    array('Mage_Directory_Model_Resource_Currency_Collection'),
    array('Mage_Downloadable_FileController', 'Magento\Downloadable\Controller\Adminhtml\Downloadable\File'),
    array('Mage_Downloadable_Product_EditController', 'Magento\Backend\Controller\Catalog\Product'),
    array('Mage_Eav_Model_Convert_Adapter_Entity'),
    array('Mage_Eav_Model_Convert_Adapter_Grid'),
    array('Mage_Eav_Model_Convert_Parser_Abstract'),
    array('Mage_Eav_Model_Entity_Collection'),
    array('Mage_GiftMessage_Block_Message_Form'),
    array('Mage_GiftMessage_Block_Message_Helper'),
    array('Mage_GiftMessage_IndexController'),
    array('Mage_GiftMessage_Model_Entity_Attribute_Backend_Boolean_Config'),
    array('Mage_GiftMessage_Model_Entity_Attribute_Source_Boolean_Config'),
    array('Mage_GoogleOptimizer_IndexController', 'Magento\GoogleOptimizer\Adminhtml\Googleoptimizer\IndexController'),
    array('Mage_GoogleShopping_Block_Adminhtml_Types_Grid'),
    array('Mage_GoogleShopping_Helper_SiteVerification', 'Mage_GoogleShopping_Block_SiteVerification'),
    array('Mage_ImportExport_Model_Import_Adapter_Abstract', 'Mage_ImportExport_Model_Import_SourceAbstract'),
    array('Mage_ImportExport_Model_Import_Adapter_Csv', 'Mage_ImportExport_Model_Import_Source_Csv'),
    array('Mage_Install_Model_Installer_Env'),
    array('Mage_Ogone_Model_Api_Debug'),
    array('Mage_Ogone_Model_Resource_Api_Debug'),
    array('Mage_Page_Block_Html_Toplinks'),
    array('Mage_Page_Block_Html_Wrapper'),
    array('Mage_Page_Block_Template_Links'),
    array('Mage_Paypal_Block_Adminhtml_Settlement_Report_Grid'),
    array('Mage_ProductAlert_Block_Price'),
    array('Mage_ProductAlert_Block_Stock'),
    array('Mage_Reports_Model_Resource_Coupons_Collection'),
    array('Mage_Reports_Model_Resource_Invoiced_Collection'),
    array('Mage_Reports_Model_Resource_Product_Ordered_Collection'),
    array(
        'Mage_Reports_Model_Resource_Product_Viewed_Collection',
        'Magento\Reports\Model\Resource\Report\Product\Viewed\Collection'
    ),
    array('Mage_Reports_Model_Resource_Refunded_Collection'),
    array('Mage_Reports_Model_Resource_Shipping_Collection'),
    array('Mage_Reports_Model_Report'),
    array('Mage_Reports_Model_Test'),
    array('Mage_Rss_Model_Observer'),
    array('Mage_Rss_Model_Session', 'Magento_Backend_Model_Auth and \Magento\Backend\Model\Auth\Session'),
    array(
        'Magento\Sales\Block\Adminhtml\Order\Invoice\Create\Tracking',
        '\Magento\Shipping\Block\Adminhtml\Order\Tracking'
    ),
    array(
        'Magento\Sales\Block\Adminhtml\Order\Shipment\Create\Tracking',
        'Magento\Shipping\Block\Adminhtml\Order\Tracking'
    ),
    array(
        'Magento\Sales\Block\Adminhtml\Order\Shipment\Packaging',
        '\Magento\Shipping\Block\Adminhtml\Order\Packaging'
    ),
    array(
        'Magento\Sales\Block\Adminhtml\Order\Shipment\Packaging\Grid',
        '\Magento\Shipping\Block\Adminhtml\Order\Packaging\Grid'
    ),
    array(
        'Magento\Sales\Block\Adminhtml\Order\Shipment\Tracking\Info',
        '\Magento\Shipping\Block\Adminhtml\Order\Tracking'
    ),
    array(
        'Magento\Sales\Block\Adminhtml\Order\Shipment\View\Tracking',
        'Magento\Shipping\Block\Adminhtml\Order\Tracking\View'
    ),
    array('Mage_Sales_Block_Order_Details'),
    array('Mage_Sales_Block_Order_Tax'),
    array('Mage_Sales_Block_Guest_Links'),
    array('Mage_Sales_Model_Entity_Order'),
    array('Mage_Sales_Model_Entity_Order_Address'),
    array('Mage_Sales_Model_Entity_Order_Address_Collection'),
    array('Mage_Sales_Model_Entity_Order_Attribute_Backend_Billing'),
    array('Mage_Sales_Model_Entity_Order_Attribute_Backend_Child'),
    array('Mage_Sales_Model_Entity_Order_Attribute_Backend_Parent'),
    array('Mage_Sales_Model_Entity_Order_Attribute_Backend_Shipping'),
    array('Mage_Sales_Model_Entity_Order_Collection'),
    array('Mage_Sales_Model_Entity_Order_Creditmemo'),
    array('Mage_Sales_Model_Entity_Order_Creditmemo_Attribute_Backend_Child'),
    array('Mage_Sales_Model_Entity_Order_Creditmemo_Attribute_Backend_Parent'),
    array('Mage_Sales_Model_Entity_Order_Creditmemo_Collection'),
    array('Mage_Sales_Model_Entity_Order_Creditmemo_Comment'),
    array('Mage_Sales_Model_Entity_Order_Creditmemo_Comment_Collection'),
    array('Mage_Sales_Model_Entity_Order_Creditmemo_Item'),
    array('Mage_Sales_Model_Entity_Order_Creditmemo_Item_Collection'),
    array('Mage_Sales_Model_Entity_Order_Invoice'),
    array('Mage_Sales_Model_Entity_Order_Invoice_Attribute_Backend_Child'),
    array('Mage_Sales_Model_Entity_Order_Invoice_Attribute_Backend_Item'),
    array('Mage_Sales_Model_Entity_Order_Invoice_Attribute_Backend_Order'),
    array('Mage_Sales_Model_Entity_Order_Invoice_Attribute_Backend_Parent'),
    array('Mage_Sales_Model_Entity_Order_Invoice_Collection'),
    array('Mage_Sales_Model_Entity_Order_Invoice_Comment'),
    array('Mage_Sales_Model_Entity_Order_Invoice_Comment_Collection'),
    array('Mage_Sales_Model_Entity_Order_Invoice_Item'),
    array('Mage_Sales_Model_Entity_Order_Invoice_Item_Collection'),
    array('Mage_Sales_Model_Entity_Order_Item'),
    array('Mage_Sales_Model_Entity_Order_Item_Collection'),
    array('Mage_Sales_Model_Entity_Order_Payment'),
    array('Mage_Sales_Model_Entity_Order_Payment_Collection'),
    array('Mage_Sales_Model_Entity_Order_Shipment'),
    array('Mage_Sales_Model_Entity_Order_Shipment_Attribute_Backend_Child'),
    array('Mage_Sales_Model_Entity_Order_Shipment_Attribute_Backend_Parent'),
    array('Mage_Sales_Model_Entity_Order_Shipment_Collection'),
    array('Mage_Sales_Model_Entity_Order_Shipment_Comment'),
    array('Mage_Sales_Model_Entity_Order_Shipment_Comment_Collection'),
    array('Mage_Sales_Model_Entity_Order_Shipment_Item'),
    array('Mage_Sales_Model_Entity_Order_Shipment_Item_Collection'),
    array('Mage_Sales_Model_Entity_Order_Shipment_Track'),
    array('Mage_Sales_Model_Entity_Order_Shipment_Track_Collection'),
    array('Mage_Sales_Model_Entity_Order_Status_History'),
    array('Mage_Sales_Model_Entity_Order_Status_History_Collection'),
    array('Mage_Sales_Model_Entity_Quote'),
    array('Mage_Sales_Model_Entity_Quote_Address'),
    array('Mage_Sales_Model_Entity_Quote_Address_Attribute_Backend'),
    array('Mage_Sales_Model_Entity_Quote_Address_Attribute_Backend_Child'),
    array('Mage_Sales_Model_Entity_Quote_Address_Attribute_Backend_Parent'),
    array('Mage_Sales_Model_Entity_Quote_Address_Attribute_Backend_Region'),
    array('Mage_Sales_Model_Entity_Quote_Address_Attribute_Frontend'),
    array('Mage_Sales_Model_Entity_Quote_Address_Attribute_Frontend_Custbalance'),
    array('Mage_Sales_Model_Entity_Quote_Address_Attribute_Frontend_Discount'),
    array('Mage_Sales_Model_Entity_Quote_Address_Attribute_Frontend_Grand'),
    array('Mage_Sales_Model_Entity_Quote_Address_Attribute_Frontend_Shipping'),
    array('Mage_Sales_Model_Entity_Quote_Address_Attribute_Frontend_Subtotal'),
    array('Mage_Sales_Model_Entity_Quote_Address_Attribute_Frontend_Tax'),
    array('Mage_Sales_Model_Entity_Quote_Address_Collection'),
    array('Mage_Sales_Model_Entity_Quote_Address_Item'),
    array('Mage_Sales_Model_Entity_Quote_Address_Item_Collection'),
    array('Mage_Sales_Model_Entity_Quote_Address_Rate'),
    array('Mage_Sales_Model_Entity_Quote_Address_Rate_Collection'),
    array('Mage_Sales_Model_Entity_Quote_Collection'),
    array('Mage_Sales_Model_Entity_Quote_Item'),
    array('Mage_Sales_Model_Entity_Quote_Item_Collection'),
    array('Mage_Sales_Model_Entity_Quote_Payment'),
    array('Mage_Sales_Model_Entity_Quote_Payment_Collection'),
    array('Mage_Sales_Model_Entity_Sale_Collection'),
    array('Mage_Sales_Model_Entity_Setup'),
    array('Mage_Shipping_ShippingController'),
    array('Mage_Tag_Block_Adminhtml_Report_Customer_Detail_Grid'),
    array('Mage_Tag_Block_Adminhtml_Report_Customer_Grid'),
    array('Mage_Tag_Block_Adminhtml_Report_Popular_Detail_Grid'),
    array('Mage_Tag_Block_Adminhtml_Report_Product_Detail_Grid'),
    array('Mage_Tag_Block_Adminhtml_Report_Product_Grid'),
    array('Mage_Tag_Block_Customer_Edit'),
    array('Mage_Theme_Block_Adminhtml_System_Design_Theme_Grid'),
    array('Mage_User_Block_Role_Grid'),
    array('Mage_User_Block_User_Grid'),
    array('Mage_User_Model_Roles'),
    array('Mage_User_Model_Resource_Roles'),
    array('Mage_User_Model_Resource_Roles_Collection'),
    array('Mage_User_Model_Resource_Roles_User_Collection'),
    array('Mage_Widget_Block_Adminhtml_Widget_Instance_Grid'),
    array('Magento\Widget\Model\Observer'),
    array('Mage_Wishlist_Model_Resource_Product_Collection'),
    array('Varien_Convert_Action'),
    array('Varien_Convert_Action_Abstract'),
    array('Varien_Convert_Action_Interface'),
    array('Varien_Convert_Adapter_Abstract'),
    array('Varien_Convert_Adapter_Db_Table'),
    array('Varien_Convert_Adapter_Http'),
    array('Varien_Convert_Adapter_Http_Curl'),
    array('Varien_Convert_Adapter_Interface'),
    array('Varien_Convert_Adapter_Io'),
    array('Varien_Convert_Adapter_Soap'),
    array('Varien_Convert_Adapter_Std'),
    array('Varien_Convert_Adapter_Zend_Cache'),
    array('Varien_Convert_Adapter_Zend_Db'),
    array('Varien_Convert_Container_Collection'),
    array('Varien_Convert_Container_Generic'),
    array('Varien_Convert_Container_Interface'),
    array('Varien_Convert_Mapper_Abstract'),
    array('Varien_Convert_Parser_Abstract'),
    array('Varien_Convert_Parser_Csv'),
    array('Varien_Convert_Parser_Interface'),
    array('Varien_Convert_Parser_Serialize'),
    array('Varien_Convert_Parser_Xml_Excel'),
    array('Varien_Convert_Profile'),
    array('Varien_Convert_Profile_Abstract'),
    array('Varien_Convert_Profile_Collection'),
    array('Varien_Convert_Validator_Abstract'),
    array('Varien_Convert_Validator_Column'),
    array('Varien_Convert_Validator_Dryrun'),
    array('Varien_Convert_Validator_Interface'),
    array('Mage_File_Uploader_Image'),
    array('Varien_Profiler', 'Magento_Framework_Profiler'),
    array('Mage_Adminhtml_Block_Notification_Window', 'Magento\AdminNotification\Block\Window'),
    array('Mage_Adminhtml_Block_Notification_Toolbar'),
    array('Mage_Adminhtml_Block_Notification_Survey'),
    array('Mage_Adminhtml_Block_Notification_Security'),
    array('Mage_Adminhtml_Block_Notification_Inbox'),
    array('Mage_Adminhtml_Block_Notification_Grid', 'Magento\AdminNotification\Block\Notification\Grid'),
    array('Mage_Adminhtml_Block_Notification_Baseurl'),
    array(
        'Mage_Adminhtml_Block_Notification_Grid_Renderer_Severity',
        'Magento\AdminNotification\Block\Grid\Renderer\Severity'
    ),
    array(
        'Mage_Adminhtml_Block_Notification_Grid_Renderer_Notice',
        'Magento\AdminNotification\Block\Grid\Renderer\Notice'
    ),
    array(
        'Mage_Adminhtml_Block_Notification_Grid_Renderer_Actions',
        'Magento\AdminNotification\Block\Grid\Renderer\Actions'
    ),
    array('Mage_Adminhtml_Block_Cache_Notifications'),
    array('Mage_AdminNotification_Block_Grid'),
    array('Mage_Core_Model_Design_Package'),
    array('Mage_Core_Model_Design_PackageInterface'),
    array('Mage_Core_Model_Resource_Type_Db_Mysqli_Setup'),
    array('Mage_Core_Model_Resource_Type_Db_Mysqli'),
    array('Varien_Db_Adapter_Mysqli'),
    array('Mage_DB_Mysqli'),
    array('Mage_DB_Exception'),
    array(
        'Magento\Catalog\Block\Product\View\Media',
        'Decomposed into \Magento\Catalog\Block\Product\View\Gallery' .
        ' and \Magento\Catalog\Block\Product\View\BaseImage classes'
    ),
    array('Magento\Wishlist\Block\Links', 'Magento\Wishlist\Block\Link'),
    array('Magento\Wishlist\Block\Render\Item\Price'),
    array('Mage_Adminhtml_Block_Api_Tab_Userroles'),
    array('Mage_Adminhtml_Block_Api_Tab_Roleinfo'),
    array('Mage_Adminhtml_Block_Api_Tab_Rolesusers'),
    array('Mage_Adminhtml_Block_Api_Tab_Rolesedit'),
    array('Mage_Adminhtml_Block_Api_Editroles'),
    array('Mage_Adminhtml_Block_Api_Buttons'),
    array('Mage_Adminhtml_Block_Api_Users'),
    array('Mage_Adminhtml_Block_Api_Role_Grid_User'),
    array('Mage_Adminhtml_Block_Api_Grid_Role'),
    array('Mage_Adminhtml_Block_Api_Roles'),
    array('Mage_Adminhtml_Block_Api_User_Edit_Tab_Main'),
    array('Mage_Adminhtml_Block_Api_User_Edit_Tab_Roles'),
    array('Mage_Adminhtml_Block_Api_User_Edit_Tabs'),
    array('Mage_Adminhtml_Block_Api_User_Edit_Form'),
    array('Mage_Adminhtml_Block_Api_User_Grid'),
    array('Mage_Adminhtml_Block_Api_User_Edit'),
    array('Mage_Adminhtml_Block_Api_Role'),
    array('Mage_Adminhtml_Block_Api_User'),
    array('Mage_Adminhtml_Block_Api_Edituser'),
    array('Mage_Api_Exception'),
    array('Mage_Api_Controller_Action'),
    array('Mage_Api_Model_Acl_Role_Generic'),
    array('Mage_Api_Model_Acl_Role_Group'),
    array('Mage_Api_Model_Acl_Role_Registry'),
    array('Mage_Api_Model_Acl_Role_User'),
    array('Mage_Api_Model_Acl_Assert_Ip'),
    array('Mage_Api_Model_Acl_Assert_Time'),
    array('Mage_Api_Model_Acl_Role'),
    array('Mage_Api_Model_Acl_Resource'),
    array('Mage_Api_Model_Rules'),
    array('Mage_Api_Model_Wsdl_Config'),
    array('Mage_Api_Model_Wsdl_Config_Base'),
    array('Mage_Api_Model_Wsdl_Config_Element'),
    array('Mage_Api_Model_Server'),
    array('Mage_Api_Model_Mysql4_Acl_Role_Collection'),
    array('Mage_Api_Model_Mysql4_Acl_Role'),
    array('Mage_Api_Model_Mysql4_Rules'),
    array('Mage_Api_Model_Mysql4_Role_Collection'),
    array('Mage_Api_Model_Mysql4_Rules_Collection'),
    array('Mage_Api_Model_Mysql4_Roles'),
    array('Mage_Api_Model_Mysql4_Permissions_Collection'),
    array('Mage_Api_Model_Mysql4_User_Collection'),
    array('Mage_Api_Model_Mysql4_Roles_Collection'),
    array('Mage_Api_Model_Mysql4_Roles_User_Collection'),
    array('Mage_Api_Model_Mysql4_Role'),
    array('Mage_Api_Model_Mysql4_Acl'),
    array('Mage_Api_Model_Mysql4_User'),
    array('Mage_Api_Model_Session'),
    array('Mage_Api_Model_Config'),
    array('Mage_Api_Model_Server_V2_Adapter_Soap'),
    array('Mage_Api_Model_Server_V2_Handler'),
    array('Mage_Api_Model_Server_Adapter_Soap'),
    array('Mage_Api_Model_Server_Adapter_Xmlrpc'),
    array('Mage_Api_Model_Server_WSI_Adapter_Soap'),
    array('Mage_Api_Model_Server_WSI_Handler'),
    array('Mage_Api_Model_Server_Handler'),
    array('Mage_Api_Model_Roles'),
    array('Mage_Api_Model_Role'),
    array('Mage_Api_Model_Acl'),
    array('Mage_Api_Model_Resource_Acl_Role_Collection'),
    array('Mage_Api_Model_Resource_Acl_Role'),
    array('Mage_Api_Model_Resource_Rules'),
    array('Mage_Api_Model_Resource_Role_Collection'),
    array('Mage_Api_Model_Resource_Rules_Collection'),
    array('Mage_Api_Model_Resource_Roles'),
    array('Mage_Api_Model_Resource_Permissions_Collection'),
    array('Mage_Api_Model_Resource_User_Collection'),
    array('Mage_Api_Model_Resource_Roles_Collection'),
    array('Mage_Api_Model_Resource_Roles_User_Collection'),
    array('Mage_Api_Model_Resource_Role'),
    array('Mage_Api_Model_Resource_Acl'),
    array('Mage_Api_Model_Resource_Abstract'),
    array('Mage_Api_Model_Resource_User'),
    array('Mage_Api_Model_User'),
    array('Mage_Api_Helper_Data'),
    array('Mage_Api_XmlrpcController'),
    array('Mage_Api_V2_SoapController'),
    array('Mage_Api_SoapController'),
    array('Mage_Api_IndexController'),
    array('Mage_Catalog_Model_Api_Resource'),
    array('Mage_Catalog_Model_Api2_Product_Website'),
    array('Mage_Catalog_Model_Api2_Product_Website_Rest_Admin_V1'),
    array('Mage_Catalog_Model_Api2_Product_Website_Validator_Admin_Website'),
    array('Mage_Catalog_Model_Api2_Product_Rest_Customer_V1'),
    array('Mage_Catalog_Model_Api2_Product_Rest_Guest_V1'),
    array('Mage_Catalog_Model_Api2_Product_Rest_Admin_V1'),
    array('Mage_Catalog_Model_Api2_Product_Category'),
    array('Mage_Catalog_Model_Api2_Product_Image'),
    array('Mage_Catalog_Model_Api2_Product_Category_Rest_Customer_V1'),
    array('Mage_Catalog_Model_Api2_Product_Category_Rest_Guest_V1'),
    array('Mage_Catalog_Model_Api2_Product_Category_Rest_Admin_V1'),
    array('Mage_Catalog_Model_Api2_Product_Image_Rest_Customer_V1'),
    array('Mage_Catalog_Model_Api2_Product_Image_Rest_Guest_V1'),
    array('Mage_Catalog_Model_Api2_Product_Image_Rest_Admin_V1'),
    array('Mage_Catalog_Model_Api2_Product_Image_Validator_Image'),
    array('Mage_Catalog_Model_Api2_Product_Validator_Product'),
    array('Mage_Catalog_Model_Api2_Product'),
    array('Mage_Catalog_Model_Product_Api_V2'),
    array('Mage_Catalog_Model_Product_Api'),
    array('Mage_Catalog_Model_Product_Option_Api_V2'),
    array('Mage_Catalog_Model_Product_Option_Value_Api_V2'),
    array('Mage_Catalog_Model_Product_Option_Value_Api'),
    array('Mage_Catalog_Model_Product_Option_Api'),
    array('Mage_Catalog_Model_Product_Type_Api_V2'),
    array('Mage_Catalog_Model_Product_Type_Api'),
    array('Mage_Catalog_Model_Product_Attribute_Tierprice_Api_V2'),
    array('Mage_Catalog_Model_Product_Attribute_Tierprice_Api'),
    array('Mage_Catalog_Model_Product_Attribute_Media_Api_V2'),
    array('Mage_Catalog_Model_Product_Attribute_Media_Api'),
    array('Mage_Catalog_Model_Product_Attribute_Api_V2'),
    array('Mage_Catalog_Model_Product_Attribute_Set_Api_V2'),
    array('Mage_Catalog_Model_Product_Attribute_Set_Api'),
    array('Mage_Catalog_Model_Product_Attribute_Api'),
    array('Mage_Catalog_Model_Product_Link_Api_V2'),
    array('Mage_Catalog_Model_Product_Link_Api'),
    array('Mage_Catalog_Model_Category_Api_V2'),
    array('Mage_Catalog_Model_Category_Api'),
    array('Mage_Catalog_Model_Category_Attribute_Api_V2'),
    array('Mage_Catalog_Model_Category_Attribute_Api'),
    array('Mage_Checkout_Model_Api_Resource'),
    array('Mage_Checkout_Model_Api_Resource_Customer'),
    array('Mage_Checkout_Model_Api_Resource_Product'),
    array('Mage_Checkout_Model_Cart_Api_V2'),
    array('Mage_Checkout_Model_Cart_Payment_Api'),
    array('Mage_Checkout_Model_Cart_Customer_Api_V2'),
    array('Mage_Checkout_Model_Cart_Customer_Api'),
    array('Mage_Checkout_Model_Cart_Api'),
    array('Mage_Checkout_Model_Cart_Product_Api_V2'),
    array('Mage_Checkout_Model_Cart_Product_Api'),
    array('Mage_Checkout_Model_Cart_Shipping_Api_V2'),
    array('Mage_Checkout_Model_Cart_Shipping_Api'),
    array('Mage_Checkout_Model_Cart_Coupon_Api_V2'),
    array('Mage_Checkout_Model_Cart_Coupon_Api'),
    array('Mage_Core_Model_Store_Api_V2'),
    array('Mage_Core_Model_Store_Api'),
    array('Mage_Core_Model_Magento_Api_V2'),
    array('Mage_Core_Model_Magento_Api'),
    array('Mage_Customer_Model_Group_Api_V2'),
    array('Mage_Customer_Model_Group_Api'),
    array('Mage_Customer_Model_Api_Resource'),
    array('Mage_Customer_Model_Customer_Api_V2'),
    array('Mage_Customer_Model_Customer_Api'),
    array('Mage_Customer_Model_Api2_Customer'),
    array('Mage_Customer_Model_Api2_Customer_Address'),
    array('Mage_Customer_Model_Api2_Customer_Rest_Customer_V1'),
    array('Mage_Customer_Model_Api2_Customer_Rest_Admin_V1'),
    array('Mage_Customer_Model_Api2_Customer_Address_Validator'),
    array('Mage_Customer_Model_Api2_Customer_Address_Rest_Customer_V1'),
    array('Mage_Customer_Model_Api2_Customer_Address_Rest_Admin_V1'),
    array('Mage_Customer_Model_Address_Api_V2'),
    array('Mage_Customer_Model_Address_Api'),
    array('Mage_Directory_Model_Region_Api_V2'),
    array('Mage_Directory_Model_Region_Api'),
    array('Mage_Directory_Model_Country_Api_V2'),
    array('Mage_Directory_Model_Country_Api'),
    array('Mage_Downloadable_Model_Link_Api_V2'),
    array('Mage_Downloadable_Model_Link_Api_Validator'),
    array('Mage_Downloadable_Model_Link_Api_Uploader'),
    array('Mage_Downloadable_Model_Link_Api'),
    array('Mage_GiftMessage_Model_Api_V2'),
    array('Mage_GiftMessage_Model_Api'),
    array('Mage_Sales_Model_Api_Resource'),
    array('Mage_Sales_Model_Api2_Order_Item_Rest_Customer_V1'),
    array('Mage_Sales_Model_Api2_Order_Item_Rest_Admin_V1'),
    array('Mage_Sales_Model_Api2_Order_Comment_Rest_Customer_V1'),
    array('Mage_Sales_Model_Api2_Order_Comment_Rest_Admin_V1'),
    array('Mage_Sales_Model_Api2_Order_Item'),
    array('Mage_Sales_Model_Api2_Order_Comment'),
    array('Mage_Sales_Model_Api2_Order_Address'),
    array('Mage_Sales_Model_Api2_Order_Rest_Customer_V1'),
    array('Mage_Sales_Model_Api2_Order_Rest_Admin_V1'),
    array('Mage_Sales_Model_Api2_Order_Address_Rest_Customer_V1'),
    array('Mage_Sales_Model_Api2_Order_Address_Rest_Admin_V1'),
    array('Mage_Sales_Model_Api2_Order'),
    array('Mage_Sales_Model_Order_Api_V2'),
    array('Mage_Sales_Model_Order_Shipment_Api_V2'),
    array('Mage_Sales_Model_Order_Shipment_Api'),
    array('Mage_Sales_Model_Order_Invoice_Api_V2'),
    array('Mage_Sales_Model_Order_Invoice_Api'),
    array('Mage_Sales_Model_Order_Api'),
    array('Mage_Sales_Model_Order_Creditmemo_Api_V2'),
    array('Mage_Sales_Model_Order_Creditmemo_Api'),
    array('Magento\ImportExport\Model\Config'),
    array('Magento\Install\Model\EntryPoint\Console', 'Magento\Install\App\Console'),
    array('Magento\Install\Model\EntryPoint\Output', 'Magento\Install\App\Output'),
    array('Magento\Framework\Data\Collection\Factory', 'Magento\Framework\Data\CollectionFactory'),
    array('Magento\Customer\Block\Adminhtml\System\Config\ValidatevatFactory'),
    array('Magento\Customer\Model\Attribute\Data'),
    array('Magento\Eav\Model\Attribute\Data'),
    array('Magento\Log\Model\Resource\Helper\Mysql4', 'Magento\Log\Model\Resource\Helper'),
    array('Magento\CatalogSearch\Model\Resource\Helper\Mysql4', 'Magento\CatalogSearch\Model\Resource\Helper'),
    array('Magento\ImportExport\Model\Resource\Helper\Mysql4', 'Magento\ImportExport\Model\Resource\Helper'),
    array('Magento\Reports\Model\Resource\Helper\Mysql4', 'Magento\Reports\Model\Resource\Helper'),
    array('Magento\Backup\Model\Resource\Helper\Mysql4', 'Magento\Backup\Model\Resource\Helper'),
    array('Magento\Sales\Model\CarrierFactory', 'Magento\Shipping\Model\CarrierFactory'),
    array('Magento\Sales\Model\Order\Pdf\Shipment\Packaging', 'Magento\Shipping\Model\Order\Pdf\Packaging'),
    array(
        'Magento\Sales\Model\Observer\Backend\RecurringPayment\FormRenderer',
        'Magento\RecurringPayment\Model\Observer'
    ),
    array(
        'Magento\Sales\Model\Quote\Address\Total\Nominal\AbstractRecurring',
        'Magento\RecurringPayment\Model\Quote\Total\AbstractRecurring'
    ),
    array(
        'Magento\Sales\Model\Quote\Address\Total\Nominal\Recurring\Initial',
        'Magento\RecurringPayment\Model\Quote\Total\Initial'
    ),
    array(
        'Magento\Sales\Model\Quote\Address\Total\Nominal\Recurring\Trial',
        'Magento\RecurringPayment\Model\Quote\Total\Trial'
    ),
    array('Magento\Sales\Model\ResourceFactory'),
    array('Magento\Sales\Model\Resource\Helper\Mysql4', 'Magento\Sales\Model\Resource\Helper'),
    array('Magento\Core\Model\Resource\Helper\Mysql4', 'Magento\Framework\DB\Helper'),
    array('Magento\Core\Model\Resource\Helper', 'Magento\Framework\DB\Helper'),
    array('Magento\Core\Model\Resource\Helper\AbstractHelper', 'Magento\Framework\DB\Helper\AbstractHelper'),
    array('Magento\Core\Model\Resource\HelperFactory'),
    array('Magento\Core\Model\Resource\HelperPool'),
    array('Magento\Core\Model\Resource\Transaction', 'Magento\Framework\DB\Transaction'),
    array('Magento\Catalog\Model\Resource\Helper\Mysql4', 'Magento\Catalog\Model\Resource\Helper'),
    array('Magento\Eav\Model\Resource\Helper\Mysql4', 'Magento\Eav\Model\Resource\Helper'),
    array(
        'Magento\Eav\Model\Entity\Attribute\Backend\Array',
        'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend'
    ),
    array('Magento\Sales\Model\Resource\Helper\HelperInterface', 'Magento\Sales\Model\Resource\HelperInterface'),
    array('Magento\Reports\Model\Resource\Helper\HelperInterface', 'Magento\Reports\Model\Resource\HelperInterface'),
    array(
        'Magento\Payment\Block\Catalog\Product\View\Profile',
        'Magento\RecurringPayment\Block\Catalog\Product\View\Payment'
    ),
    ['Magento\Payment\Block\Form\Banktransfer', 'Magento\OfflinePayments\Block\Form\Banktransfer'],
    ['Magento\Payment\Block\Form\Cashondelivery', 'Magento\OfflinePayments\Block\Form\Cashondelivery'],
    ['Magento\Payment\Block\Form\Ccsave', 'Magento\OfflinePayments\Block\Form\Ccsave'],
    ['Magento\Payment\Block\Form\Checkmo', 'Magento\OfflinePayments\Block\Form\Checkmo'],
    ['Magento\Payment\Block\Form\Purchaseorder', 'Magento\OfflinePayments\Block\Form\Purchaseorder'],
    ['Magento\Payment\Block\Info\Ccsave', 'Magento\OfflinePayments\Block\Info\Ccsave'],
    ['Magento\Payment\Block\Info\Checkmo', 'Magento\OfflinePayments\Block\Info\Checkmo'],
    ['Magento\Payment\Block\Info\Purchaseorder', 'Magento\OfflinePayments\Block\Info\Purchaseorder'],
    ['Magento\Payment\Model\Method\Banktransfer', 'Magento\OfflinePayments\Model\Banktransfer'],
    ['Magento\Payment\Model\Method\Cashondelivery', 'Magento\OfflinePayments\Model\Cashondelivery'],
    ['Magento\Payment\Model\Method\Ccsave', 'Magento\OfflinePayments\Model\Ccsave'],
    ['Magento\Payment\Model\Method\Checkmo', 'Magento\OfflinePayments\Model\Checkmo'],
    ['Magento\Payment\Model\Method\Purchaseorder', 'Magento\OfflinePayments\Model\Purchaseorder'],
    array(
        'Magento\Payment\Model\Recurring\Profile\MethodInterface',
        'Magento\RecurringPayment\Model\ManagerInterface'
    ),
    array('Magento\Poll\Block\ActivePoll'),
    array('Magento\Poll\Controller\Vote'),
    array('Magento\Poll\Helper\Data'),
    array('Magento\Poll\Model\Poll'),
    array('Magento\Poll\Model\Poll\Answer'),
    array('Magento\Poll\Model\Poll\Vote'),
    array('Magento\Poll\Model\Resource\Poll'),
    array('Magento\Poll\Model\Resource\Poll\Answer'),
    array('Magento\Poll\Model\Resource\Poll\Answer\Collection'),
    array('Magento\Poll\Model\Resource\Poll\Collection'),
    array('Magento\Poll\Model\Resource\Poll\Vote'),
    array('Magento\Framework\Backup'),
    array('Magento\Core\Controller\Front\Router'),
    array('Magento\Core\Controller\Request\HttpProxy'),
    array('Magento\Core\Controller\Response\Http', 'Magento\Framework\App\Response\Http'),
    array('Magento\Core\Controller\Varien\Action\Forward', 'Magento\Framework\App\Action\Forward'),
    array('Magento\Core\Controller\Varien\Action\Redirect', 'Magento\Framework\App\Action\Redirect'),
    array('Magento\Core\Controller\Varien\DispatchableInterface'),
    array('Magento\Core\Controller\Varien\Front', 'Magento\Framework\App\FrontController'),
    array('Magento\Core\Controller\FrontInterface', 'Magento\Framework\App\FrontControllerInterface'),
    array('Magento\Core\Model\App\Handler'),
    array('Magento\Core\Model\App\Proxy'),
    array('Magento\Core\Model\Event\Config\SchemaLocator', 'Magento\Framework\Event\Config\SchemaLocator'),
    array('Magento\Core\Controller\Varien\Router\AbstractRouter'),
    array('Magento\Core\Controller\Varien\AbstractAction'),
    array('Magento\Core\Controller\Varien\Exception'),
    array('Magento\Framework\HTTP\HandlerFactory'),
    array('Magento\Core\Controller\Request\Http'),
    array('Magento\Core\Controller\Varien\Router\DefaultRouter'),
    array('Magento\Core\Model\NoRouteHandlerList'),
    array('Magento\Core\Controller\Varien\Action\Factory'),
    array('Magento\Core\Model\Config\Loader\Primary'),
    array('Magento\Core\Model\Config\AbstractStorage'),
    array('Magento\Core\Model\Config\Loader'),
    array('Magento\Core\Model\Config\LoaderInterface'),
    array('Magento\Core\Model\Config\Primary'),
    array('Magento\Core\Model\Config\Storage'),
    array('Magento\Core\Model\Config\StorageInterface'),
    array('Magento\Core\Model\Dir'),
    array('Magento\Core\Model\ModuleList'),
    array('Magento\Core\Model\ModuleListInterface'),
    array('Magento\Core\Model\RouterList'),
    array('Magento\Core\Model\App\State'),
    array('Magento\Core\Model\App'),
    array('Magento\Core\Model\Event\Config\Converter'),
    array('Magento\Core\Model\Event\Config\Data'),
    array('Magento\Core\Model\Event\Config\Reader'),
    array('Magento\Core\Model\Event\Invoker\InvokerDefault'),
    array('Magento\Core\Model\Event\Config'),
    array('Magento\Core\Model\Event\ConfigInterface'),
    array('Magento\Core\Model\Event\InvokerInterface'),
    array('Magento\Core\Model\Event\Manager'),
    array('Magento\Framework\HTTP\Handler\Composite'),
    array('Magento\Framework\HTTP\HandlerInterface'),
    array('Magento\Backend\Model\Request\PathInfoProcessor'),
    array('Magento\Backend\Model\Router\NoRouteHandler'),
    array('Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Super\Config\Simple'),
    array('Magento\Core\Model\Request\PathInfoProcessor', 'Magento\Store\App\Request\PathInfoProcessor'),
    array('Magento\Core\Model\Request\RewriteService'),
    array('Magento\Core\Model\Router\NoRouteHandler'),
    array('Magento\Core\Model\Resource\SetupFactory'),
    array('Magento\Core\Model\Dir\Verification'),
    array('Magento\Core\Model\Module\Declaration\Converter\Dom'),
    array('Magento\Core\Model\Module\Declaration\Reader\Filesystem'),
    array('Magento\Core\Model\Module\Dir'),
    array('Magento\Core\Model\Module\Declaration\FileResolver'),
    array('Magento\Core\Model\Module\Declaration\SchemaLocator'),
    array('Magento\Core\Model\Module\Dir\ReverseResolver'),
    array('Magento\Core\Model\Module\ResourceResolver'),
    array('Magento\Core\Model\Module\ResourceResolverInterface'),
    array('Magento\Core\Model\Resource\SetupInterface'),
    array('Magento\Core\Model\Db\UpdaterInterface'),
    array('Magento\Core\Model\Router\NoRouteHandlerInterface'),
    array('Magento\Core\Model\UrlInterface'),
    array('Magento\Sales\Model\AdminOrder'),
    array('Magento\Sales\Model\AdminOrder\Random'),
    array('Magento\Sales\Model\Resource\Order\Attribute\Backend\Parent'),
    array('Magento\Sales\Model\Resource\Order\Creditmemo\Attribute\Backend\Parent'),
    array('Magento\Sales\Model\Resource\Order\Invoice\Attribute\Backend\Parent'),
    array('Magento\Sales\Model\Resource\Order\Shipment\Attribute\Backend\Parent'),
    array('Magento\Sales\Model\Resource\Quote\Address\Attribute\Backend\Parent'),
    array('Magento\Core\Helper\Http'),
    array('Magento\Core\Model\ThemeInterface', 'Magento\Framework\View\Design\ThemeInterface'),
    array('Magento\Core\Model\View\DesignInterface', 'Magento\Framework\View\DesignInterface'),
    array('Magento\Core\Model\Layout\Element', 'Magento\Framework\View\Layout\Element'),
    array('Magento\Core\Helper\Hint', 'Magento\Backend\Block\Store\Switcher'),
    array(
        'Magento\Core\Model\Design\Fallback\Rule\ModularSwitch',
        'Magento\Framework\View\Design\Fallback\Rule\ModularSwitch'
    ),
    array(
        'Magento\Core\Model\Design\Fallback\Rule\RuleInterface',
        'Magento\Framework\View\Design\Fallback\Rule\RuleInterface'
    ),
    array('Magento\Core\Model\Design\Fallback\Rule\Simple', 'Magento\Framework\View\Design\Fallback\Rule\Simple'),
    array('Magento\Core\Model\Design\Fallback\Factory', 'Magento\Framework\View\Design\Fallback\RulePool'),
    array('Magento\Core\Model\Design\FileResolution\Strategy\Fallback\CachingProxy'),
    array('Magento\Framework\View\Design\FileResolution\Strategy\View\NotifiableInterface'),
    array('Magento\Framework\View\Design\FileResolution\Strategy\View\FileInterface'),
    array('Magento\Framework\View\Design\FileResolution\Strategy\View\LocaleInterface'),
    array('Magento\Framework\View\Design\FileResolution\Strategy\View\ViewInterface'),
    array('Magento\Framework\View\Design\FileResolution\Strategy\Fallback\CachingProxy'),
    array(
        'Magento\Core\Model\Design\FileResolution\Strategy\Fallback',
        'Magento\Framework\View\Design\FileResolution\Fallback\{File,ViewFile,LocaleFile,TemplateFile}'
    ),
    array('Magento\Core\Model\Design\FileResolution\StrategyPool'),
    array('Magento\Framework\View\Design\FileResolution\StrategyPool'),
    array('Magento\Core\Model\Layout\File', 'Magento\Framework\View\File'),
    array('Magento\Core\Model\Layout\File\Factory', 'Magento\Framework\View\File\Factory'),
    array('Magento\Core\Model\Layout\File\FileList\Factory', 'Magento\Framework\View\File\FileList\Factory'),
    array('Magento\Core\Model\Layout\File\ListFile', 'Magento\Framework\View\File\FileList'),
    array(
        'Magento\Core\Model\Layout\File\Source\Aggregated',
        'Magento\Framework\View\Layout\File\Collector\Aggregated'
    ),
    array('Magento\Core\Model\Layout\File\Source\Base', 'Magento\Framework\View\Layout\File\Source\Base'),
    array(
        'Magento\Core\Model\Layout\File\Source\Decorator\ModuleDependency',
        'Magento\Framework\View\File\Collector\Decorator\ModuleDependency'
    ),
    array(
        'Magento\Core\Model\Layout\File\Source\Decorator\ModuleOutput',
        'Magento\Framework\View\File\Collector\Decorator\ModuleOutput'
    ),
    array('Magento\Core\Model\Layout\File\Source\Override\Base', 'Magento\Framework\View\Layout\File\Override\Base'),
    array('Magento\Core\Model\Layout\File\Source\Override\Theme', 'Magento\Framework\View\Layout\File\Override\Theme'),
    array('Magento\Core\Model\Layout\File\Source\Theme', 'Magento\Framework\View\Layout\File\Source\Theme'),
    array('Magento\Core\Model\Layout\File\SourceInterface', 'Magento\Framework\View\File\CollectorInterface'),
    array('Magento\Core\Model\LayoutFactory', 'Magento\Framework\View\LayoutFactory'),
    array('Magento\Core\Model\TemplateEngine\EngineInterface', 'Magento\Framework\View\TemplateEngineInterface'),
    array('Magento\Core\Model\TemplateEngine\Factory', 'Magento\Framework\View\TemplateEngineFactory'),
    array('Magento\Core\Model\TemplateEngine\Php', 'Magento\Framework\View\TemplateEngine\Php'),
    array('Magento\Core\Model\TemplateEngine\Pool', 'Magento\Framework\View\TemplateEnginePool'),
    array('Magento\Media\Model\File\Image'),
    array('Magento\Media\Model\Image'),
    array('Magento\Media\Helper\Data'),
    array(
        'Magento\Adminhtml\Block\Promo\Catalog\Edit\Form',
        'Magento\CatalogRule\Block\Adminhtml\Promo\Catalog\Edit\Form'
    ),
    array(
        'Magento\Adminhtml\Block\Promo\Catalog\Edit\Js',
        'Magento\CatalogRule\Block\Adminhtml\Promo\Catalog\Edit\Js'
    ),
    array(
        'Magento\Adminhtml\Block\Promo\Catalog\Edit\Tab\Actions',
        'Magento\CatalogRule\Block\Adminhtml\Promo\Catalog\Edit\Tab\Actions'
    ),
    array(
        'Magento\Adminhtml\Block\Promo\Catalog\Edit\Tab\Conditions',
        'Magento\CatalogRule\Block\Adminhtml\Promo\Catalog\Edit\Tab\Conditions'
    ),
    array(
        'Magento\Adminhtml\Block\Promo\Catalog\Edit\Tab\Main',
        'Magento\CatalogRule\Block\Adminhtml\Promo\Catalog\Edit\Tab\Main'
    ),
    array(
        'Magento\Adminhtml\Block\Promo\Catalog\Edit\Tabs',
        'Magento\CatalogRule\Block\Adminhtml\Promo\Catalog\Edit\Tabs'
    ),
    array('Magento\Adminhtml\Block\Promo\Catalog\Edit', 'Magento\CatalogRule\Block\Adminhtml\Promo\Catalog\Edit'),
    array('Magento\Adminhtml\Block\Promo\Catalog', 'Magento\CatalogRule\Block\Adminhtml\Promo\Catalog'),
    array(
        'Magento\Adminhtml\Block\Promo\Widget\Chooser\Daterange',
        'Magento\CatalogRule\Block\Adminhtml\Widget\Chooser\Daterange'
    ),
    array(
        'Magento\Adminhtml\Block\Promo\Widget\Chooser\Sku',
        'Magento\CatalogRule\Block\Adminhtml\Widget\Chooser\Sku'
    ),
    array('Magento\Adminhtml\Block\Promo\Widget\Chooser', 'Magento\CatalogRule\Block\Adminhtml\Widget\Chooser'),
    array('Magento\Adminhtml\Block\Promo\Quote\Edit\Form', 'Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Form'),
    array(
        'Magento\Adminhtml\Block\Promo\Quote\Edit\Tab\Actions',
        'Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Actions'
    ),
    array(
        'Magento\Adminhtml\Block\Promo\Quote\Edit\Tab\Conditions',
        'Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Conditions'
    ),
    array(
        'Magento\Adminhtml\Block\Promo\Quote\Edit\Tab\Coupons\Form',
        'Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons\Form'
    ),
    array(
        'Magento\Adminhtml\Block\Promo\Quote\Edit\Tab\Coupons\Grid\Column\Renderer\Used',
        'Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons\Grid\Column\Renderer\Used'
    ),
    array(
        'Magento\Adminhtml\Block\Promo\Quote\Edit\Tab\Coupons\Grid',
        'Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons\Grid'
    ),
    array(
        'Magento\Adminhtml\Block\Promo\Quote\Edit\Tab\Coupons',
        'Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons'
    ),
    array(
        'Magento\Adminhtml\Block\Promo\Quote\Edit\Tab\Labels',
        'Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Labels'
    ),
    array(
        'Magento\Adminhtml\Block\Promo\Quote\Edit\Tab\Main\Renderer\Checkbox',
        'Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Main\Renderer\Checkbox'
    ),
    array(
        'Magento\Adminhtml\Block\Promo\Quote\Edit\Tab\Main',
        'Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Main'
    ),
    array('Magento\Adminhtml\Block\Promo\Quote\Edit\Tabs', 'Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tabs'),
    array('Magento\Adminhtml\Block\Promo\Quote\Edit', 'Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit'),
    array('Magento\Adminhtml\Block\Promo\Quote', 'Magento\SalesRule\Block\Adminhtml\Promo\Quote'),
    array('Magento\Adminhtml\Controller\Promo\Catalog', 'Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog'),
    array('Magento\Adminhtml\Controller\Promo\Quote', 'Magento\SalesRule\Controller\Adminhtml\Promo\Quote'),
    array('Magento\Adminhtml\Controller\Promo\Widget', 'Magento\CatalogRule\Controller\Adminhtml\Promo\Widget'),
    array('Magento\Adminhtml\Controller\Promo', 'Magento\CatalogRule\Controller\Adminhtml\Promo'),
    array('Magento\Adminhtml\Controller\System\Account', 'Magento\Backend\Controller\Adminhtml\System\Account'),
    array('Magento\Adminhtml\Controller\System\Backup', 'Magento\Backend\Controller\Adminhtml\System\Backup'),
    array(
        'Magento\Adminhtml\Controller\System\Config\System\Storage',
        'Magento\Backend\Controller\Adminhtml\System\Config\System\Storage'
    ),
    array('Magento\Adminhtml\Controller\System\Design', 'Magento\Backend\Controller\Adminhtml\System\Design'),
    array('Magento\Adminhtml\Controller\System\Store', 'Magento\Backend\Controller\Adminhtml\System\Store'),
    array('Magento\Adminhtml\Controller\System\Variable', 'Magento\Backend\Controller\Adminhtml\System\Variable'),
    array('Magento\Adminhtml\Block\System\Account\Edit\Form', 'Magento\Backend\Block\System\Account\Edit\Form'),
    array('Magento\Adminhtml\Block\System\Account\Edit', 'Magento\Backend\Block\System\Account\Edit'),
    array('Magento\Adminhtml\Block\System\Cache\Edit', 'Magento\Backend\Block\System\Cache\Edit'),
    array('Magento\Adminhtml\Block\System\Cache\Form', 'Magento\Backend\Block\System\Cache\Form'),
    array(
        'Magento\Adminhtml\Block\System\Design\Edit\Tab\General',
        'Magento\Backend\Block\System\Design\Edit\Tab\General'
    ),
    array('Magento\Adminhtml\Block\System\Design\Edit\Tabs', 'Magento\Backend\Block\System\Design\Edit\Tabs'),
    array('Magento\Adminhtml\Block\System\Design\Edit', 'Magento\Backend\Block\System\Design\Edit'),
    array('Magento\Adminhtml\Block\System\Design', 'Magento\Backend\Block\System\Design'),
    array('Magento\Adminhtml\Block\System\Shipping\Ups', 'Magento\Backend\Block\System\Shipping\Ups'),
    array('Magento\Adminhtml\Block\System\Store\Delete\Form', 'Magento\Backend\Block\System\Store\Delete\Form'),
    array('Magento\Adminhtml\Block\System\Store\Delete\Group', 'Magento\Backend\Block\System\Store\Delete\Group'),
    array('Magento\Adminhtml\Block\System\Store\Delete\Website', 'Magento\Backend\Block\System\Store\Delete\Website'),
    array('Magento\Adminhtml\Block\System\Store\Delete', 'Magento\Backend\Block\System\Store\Delete'),
    array(
        'Magento\Adminhtml\Block\System\Store\Edit\AbstractForm',
        'Magento\Backend\Block\System\Store\Edit\AbstractForm'
    ),
    array(
        'Magento\Adminhtml\Block\System\Store\Edit\Form\Group',
        'Magento\Backend\Block\System\Store\Edit\Form\Group'
    ),
    array(
        'Magento\Adminhtml\Block\System\Store\Edit\Form\Store',
        'Magento\Backend\Block\System\Store\Edit\Form\Store'
    ),
    array(
        'Magento\Adminhtml\Block\System\Store\Edit\Form\Website',
        'Magento\Backend\Block\System\Store\Edit\Form\Website'
    ),
    array('Magento\Adminhtml\Block\System\Store\Edit', 'Magento\Backend\Block\System\Store\Edit'),
    array(
        'Magento\Adminhtml\Block\System\Store\Grid\Render\Group',
        'Magento\Backend\Block\System\Store\Grid\Render\Group'
    ),
    array(
        'Magento\Adminhtml\Block\System\Store\Grid\Render\Store',
        'Magento\Backend\Block\System\Store\Grid\Render\Store'
    ),
    array(
        'Magento\Adminhtml\Block\System\Store\Grid\Render\Website',
        'Magento\Backend\Block\System\Store\Grid\Render\Website'
    ),
    array('Magento\Adminhtml\Block\System\Store\Store', 'Magento\Backend\Block\System\Store\Store'),
    array('Magento\Adminhtml\Block\System\Variable\Edit\Form', 'Magento\Backend\Block\System\Variable\Edit\Form'),
    array('Magento\Adminhtml\Block\System\Variable\Edit', 'Magento\Backend\Block\System\Variable\Edit'),
    array('Magento\Adminhtml\Block\System\Variable', 'Magento\Backend\Block\System\Variable'),
    array(
        'Magento\Adminhtml\Block\Checkout\Agreement\Edit\Form',
        'Magento\CheckoutAgreements\Block\Adminhtml\Agreement\Edit\Form'
    ),
    array(
        'Magento\Adminhtml\Block\Checkout\Agreement\Edit',
        'Magento\CheckoutAgreements\Block\Adminhtml\Agreement\Edit'
    ),
    array(
        'Magento\Adminhtml\Block\Checkout\Agreement\Grid',
        'Magento\CheckoutAgreements\Block\Adminhtml\Agreement\Grid'
    ),
    array('Magento\Adminhtml\Block\Checkout\Agreement', 'Magento\CheckoutAgreements\Block\Adminhtml\Agreement'),
    array('Magento\Adminhtml\Controller\Checkout\Agreement', 'Magento\Checkout\Controller\Adminhtml\Agreement'),
    array('Magento\Core\Model\View\PublicFilesManagerInterface', 'Magento\Framework\View\Asset\SourceFileInterface'),
    array('Magento\Core\Model\View\DeployedFilesManager', 'Magento\Framework\View\AssetInterface'),
    array('Magento\Framework\View\DeployedFilesManager', 'Magento\Framework\View\AssetInterface'),
    array('Magento\Core\Model\View\Publisher', 'Magento\Framework\View\Publisher'),
    array('Magento\Core\Model\View\FileSystem', 'Magento\Framework\View\FileSystem'),
    array('Magento\Core\Model\View\Service', 'Magento\Framework\View\Asset\Repository'),
    array('Magento\Core\Model\View\Url', 'Magento\Framework\View\Asset\Repository'),
    array('Magento\Core\Model\View\Config', 'Magento\Framework\View\Config'),
    array('Magento\Core\Model\Image\Factory', 'Magento\Framework\Image\Factory'),
    array('Magento\Core\Model\Theme\Image', 'Magento\Framework\View\Design\Theme\Image'),
    array('Magento\Core\Model\Theme\FlyweightFactory', 'Magento\Framework\View\Design\Theme\FlyweightFactory'),
    array('Magento\Core\Model\Image\AdapterFactory', 'Magento\Framework\Image\AdapterFactory'),
    array('Magento\Core\Model\EntryPoint\Cron', 'Magento\Framework\App\Cron'),
    array(
        'Magento\Checkout\Block\Cart\Item\Renderer\Grouped',
        'Magento\GroupedProduct\Block\Cart\Item\Renderer\Grouped'
    ),
    array('Magento\Log\Model\EntryPoint\Shell', 'Magento\Log\App\Shell'),
    array('Magento\Core\Model\Config\Modules\Reader', 'Magento\Framework\Module\Dir\Reader'),
    array('Magento\Framework\Data\Form\Factory', 'Magento\Framework\Data\FormFactory'),
    array('Magento\Framework\App\Cache\Config', 'Magento\Framework\Cache\Config'),
    array('Magento\Framework\App\Cache\Config\Converter', 'Magento\Framework\Cache\Config\Converter'),
    array('Magento\Framework\App\Cache\Config\Data', 'Magento\Framework\Cache\Config\Data'),
    array('Magento\Framework\App\Cache\Config\Reader', 'Magento\Framework\Cache\Config\Reader'),
    array('Magento\Framework\App\Cache\Config\SchemaLocator', 'Magento\Framework\Cache\Config\SchemaLocator'),
    array('Magento\Core\Model\Fieldset\Config', 'Magento\Framework\Object\Copy\Config'),
    array('Magento\Core\Model\Fieldset\Config\Converter', 'Magento\Framework\Object\Copy\Config\Converter'),
    array('Magento\Core\Model\Fieldset\Config\Data', 'Magento\Framework\Object\Copy\Config\Data'),
    array('Magento\Core\Model\Fieldset\Config\Reader', 'Magento\Framework\Object\Copy\Config\Reader'),
    array('Magento\Core\Model\Fieldset\Config\SchemaLocator', 'Magento\Framework\Object\Copy\Config\SchemaLocator'),
    array('Magento\Core\Model\ModuleManager', 'Magento\Framework\Module\Manager'),
    array('Magento\Core\Model\EntryPoint\Media', 'Magento\Core\App\Media'),
    array('Magento\Core\Controller\Varien\Action', 'Magento\Framework\App\Action\Action'),
    array('Magento\Core\Controller\Varien\Action\Context', 'Magento\Framework\App\Action\Context'),
    array('Magento\Backend\Controller\AbstractAction', 'Magento\Backend\App\AbstractAction'),
    array('Magento\Backend\Controller\Context', 'Magento\Backend\App\Action\Context'),
    array('Magento\Backend\Controller\Adminhtml\Action', 'Magento\Backend\App\Action'),
    array('Magento\Backend\Block\System\Shipping\Ups', 'Magento\Ups\Block\Backend\System\CarrierConfig'),
    array('Magento\Core\Block\Text', 'Magento\Framework\View\Element\Text'),
    array('Magento\Core\Block\Text\ListText', 'Magento\Framework\View\Element\Text\ListText'),
    array('Magento\Core\Block\Text\TextList\Item', 'Magento\Framework\View\Element\Text\TextList\Item'),
    array('Magento\Core\Block\Text\TextList\Link', 'Magento\Framework\View\Element\Text\TextList\Link'),
    array('Magento\Core\Block\Messages', 'Magento\Framework\View\Element\Messages'),
    array('Magento\Core\Model\Message', 'Magento\Framework\Message\Factory'),
    array('Magento\Core\Model\Message\AbstractMessage', 'Magento\Framework\Message\AbstractMessage'),
    array('Magento\Core\Model\Message\Collection', 'Magento\Framework\Message\Collection'),
    array('Magento\Core\Model\Message\CollectionFactory', 'Magento\Framework\Message\CollectionFactory'),
    array('Magento\Core\Model\Message\Error', 'Magento\Framework\Message\Error'),
    array('Magento\Core\Model\Message\Warning', 'Magento\Framework\Message\Warning'),
    array('Magento\Core\Model\Message\Notice', 'Magento\Framework\Message\Notice'),
    array('Magento\Core\Model\Message\Success', 'Magento\Framework\Message\Success'),
    array('Magento\Core\Block\Html\Date', 'Magento\Framework\View\Element\Html\Date'),
    array('Magento\Core\Block\Html\Select', 'Magento\Framework\View\Element\Html\Select'),
    array('Magento\Core\Block\AbstractBlock', 'Magento\Framework\View\Element\AbstractBlock'),
    array('Magento\Core\Block\Template', 'Magento\Framework\View\Element\Template'),
    array('Magento\Core\Block\Html\Calendar', 'Magento\Framework\View\Element\Html\Calendar'),
    array('Magento\Core\Block\Html\Link', 'Magento\Framework\View\Element\Html\Link'),
    array('Magento\Core\Block\Context', 'Magento\Framework\View\Element\Context'),
    array('Magento\Core\Model\Factory\Helper'),
    array('Magento\Framework\App\Helper\HelperFactory'),
    array('Magento\Core\Helper\AbstractHelper', 'Magento\Framework\App\Helper\AbstractHelper'),
    array('Magento\Core\Helper\Context', 'Magento\Framework\App\Helper\Context'),
    array('Magento\Adminhtml\Controller\Report\AbstractReport', 'Magento\Reports\Controller\Adminhtml\AbstractReport'),
    array('Magento\Adminhtml\Controller\Report\Customer', 'Magento\Reports\Controller\Adminhtml\Customer'),
    array('Magento\Adminhtml\Controller\Report\Product', 'Magento\Reports\Controller\Adminhtml\Product'),
    array('Magento\Adminhtml\Controller\Report\Review', 'Magento\Reports\Controller\Adminhtml\Review'),
    array('Magento\Adminhtml\Controller\Report\Sales', 'Magento\Reports\Controller\Adminhtml\Sales'),
    array('Magento\Adminhtml\Controller\Report\Shopcart', 'Magento\Reports\Controller\Adminhtml\Shopcart'),
    array('Magento\Adminhtml\Controller\Report\Statistics', 'Magento\Reports\Controller\Adminhtml\Statistics'),
    array(
        'Magento\Adminhtml\Block\Report\Config\Form\Field\MtdStart',
        'Magento\Reports\Block\Adminhtml\Config\Form\Field\MtdStart'
    ),
    array(
        'Magento\Adminhtml\Block\Report\Config\Form\Field\YtdStart',
        'Magento\Reports\Block\Adminhtml\Config\Form\Field\YtdStart'
    ),
    array('Magento\Adminhtml\Block\Report\Filter\Form', 'Magento\Reports\Block\Adminhtml\Filter\Form'),
    array('Magento\Adminhtml\Block\Report\Grid\AbstractGrid', 'Magento\Reports\Block\Adminhtml\Grid\AbstractGrid'),
    array(
        'Magento\Adminhtml\Block\Report\Grid\Column\Renderer\Blanknumber',
        'Magento\Reports\Block\Adminhtml\Grid\Column\Renderer\Blanknumber'
    ),
    array(
        'Magento\Adminhtml\Block\Report\Grid\Column\Renderer\Currency',
        'Magento\Reports\Block\Adminhtml\Grid\Column\Renderer\Currency'
    ),
    array(
        'Magento\Adminhtml\Block\Report\Grid\Column\Renderer\Customer',
        'Magento\Reports\Block\Adminhtml\Grid\Column\Renderer\Customer'
    ),
    array(
        'Magento\Adminhtml\Block\Report\Grid\Column\Renderer\Product',
        'Magento\Reports\Block\Adminhtml\Grid\Column\Renderer\Product'
    ),
    array('Magento\Adminhtml\Block\Report\Grid\Shopcart', 'Magento\Reports\Block\Adminhtml\Grid\Shopcart'),
    array(
        'Magento\Adminhtml\Block\Report\Product\Downloads\Grid',
        'Magento\Reports\Block\Adminhtml\Product\Downloads\Grid'
    ),
    array(
        'Magento\Adminhtml\Block\Report\Product\Downloads\Renderer\Purchases',
        'Magento\Reports\Block\Adminhtml\Product\Downloads\Renderer\Purchases'
    ),
    array('Magento\Adminhtml\Block\Report\Product\Downloads', 'Magento\Reports\Block\Adminhtml\Product\Downloads'),
    array('Magento\Adminhtml\Block\Report\Product\Grid', 'Magento\Reports\Block\Adminhtml\Product\Grid'),
    array(
        'Magento\Adminhtml\Block\Report\Product\Lowstock\Grid',
        'Magento\Reports\Block\Adminhtml\Product\Lowstock\Grid'
    ),
    array('Magento\Adminhtml\Block\Report\Product\Lowstock', 'Magento\Reports\Block\Adminhtml\Product\Lowstock'),
    array('Magento\Adminhtml\Block\Report\Product\Viewed\Grid', 'Magento\Reports\Block\Adminhtml\Product\Viewed\Grid'),
    array('Magento\Adminhtml\Block\Report\Product\Viewed', 'Magento\Reports\Block\Adminhtml\Product\Viewed'),
    array('Magento\Adminhtml\Block\Report\Product', 'Magento\Reports\Block\Adminhtml\Product'),
    array('Magento\Adminhtml\Block\Report\Review\Customer', 'Magento\Reports\Block\Adminhtml\Review\Customer'),
    array('Magento\Adminhtml\Block\Report\Review\Detail\Grid', 'Magento\Reports\Block\Adminhtml\Review\Detail\Grid'),
    array('Magento\Adminhtml\Block\Report\Review\Detail', 'Magento\Reports\Block\Adminhtml\Review\Detail'),
    array('Magento\Adminhtml\Block\Report\Review\Product', 'Magento\Reports\Block\Adminhtml\Review\Product'),
    array(
        'Magento\Adminhtml\Block\Report\Sales\Bestsellers\Grid',
        'Magento\Reports\Block\Adminhtml\Sales\Bestsellers\Grid'
    ),
    array('Magento\Adminhtml\Block\Report\Sales\Bestsellers', 'Magento\Reports\Block\Adminhtml\Sales\Bestsellers'),
    array('Magento\Adminhtml\Block\Report\Sales\Coupons\Grid', 'Magento\Reports\Block\Adminhtml\Sales\Coupons\Grid'),
    array('Magento\Adminhtml\Block\Report\Sales\Coupons', 'Magento\Reports\Block\Adminhtml\Sales\Coupons'),
    array(
        'Magento\Adminhtml\Block\Report\Sales\Grid\Column\Renderer\Date',
        'Magento\Reports\Block\Adminhtml\Sales\Grid\Column\Renderer\Date'
    ),
    array('Magento\Adminhtml\Block\Report\Sales\Invoiced\Grid', 'Magento\Reports\Block\Adminhtml\Sales\Invoiced\Grid'),
    array('Magento\Adminhtml\Block\Report\Sales\Invoiced', 'Magento\Reports\Block\Adminhtml\Sales\Invoiced'),
    array('Magento\Adminhtml\Block\Report\Sales\Refunded\Grid', 'Magento\Reports\Block\Adminhtml\Sales\Refunded\Grid'),
    array('Magento\Adminhtml\Block\Report\Sales\Refunded', 'Magento\Reports\Block\Adminhtml\Sales\Refunded'),
    array('Magento\Adminhtml\Block\Report\Sales\Sales\Grid', 'Magento\Reports\Block\Adminhtml\Sales\Sales\Grid'),
    array('Magento\Adminhtml\Block\Report\Sales\Sales', 'Magento\Reports\Block\Adminhtml\Sales\Sales'),
    array('Magento\Adminhtml\Block\Report\Sales\Shipping\Grid', 'Magento\Reports\Block\Adminhtml\Sales\Shipping\Grid'),
    array('Magento\Adminhtml\Block\Report\Sales\Shipping', 'Magento\Reports\Block\Adminhtml\Sales\Shipping'),
    array('Magento\Adminhtml\Block\Report\Sales\Tax\Grid', 'Magento\Reports\Block\Adminhtml\Sales\Tax\Grid'),
    array('Magento\Adminhtml\Block\Report\Sales\Tax', 'Magento\Reports\Block\Adminhtml\Sales\Tax'),
    array('Magento\Adminhtml\Block\Report\Search', 'Magento\Reports\Block\Adminhtml\Search'),
    array(
        'Magento\Adminhtml\Block\Report\Shopcart\Abandoned\Grid',
        'Magento\Reports\Block\Adminhtml\Shopcart\Abandoned\Grid'
    ),
    array('Magento\Adminhtml\Block\Report\Shopcart\Abandoned', 'Magento\Reports\Block\Adminhtml\Shopcart\Abandoned'),
    array(
        'Magento\Adminhtml\Block\Report\Shopcart\Customer\Grid',
        'Magento\Reports\Block\Adminhtml\Shopcart\Customer\Grid'
    ),
    array('Magento\Adminhtml\Block\Report\Shopcart\Customer', 'Magento\Reports\Block\Adminhtml\Shopcart\Customer'),
    array(
        'Magento\Adminhtml\Block\Report\Shopcart\Product\Grid',
        'Magento\Reports\Block\Adminhtml\Shopcart\Product\Grid'
    ),
    array('Magento\Adminhtml\Block\Report\Shopcart\Product', 'Magento\Reports\Block\Adminhtml\Shopcart\Product'),
    array('Magento\Adminhtml\Block\Report\Wishlist\Grid', 'Magento\Reports\Block\Adminhtml\Wishlist\Grid'),
    array('Magento\Adminhtml\Block\Report\Wishlist', 'Magento\Reports\Block\Adminhtml\Wishlist'),
    array('Magento\Framework\App\Dir\Verification', 'Magento\Framework\App\Filesystem\DirectoryList\Verification'),
    array('Magento\Backend\Helper\Addresses'),
    array('Magento\Core\Model\Cookie', 'Magento\Framework\Stdlib\Cookie'),
    array('Magento\Core\Model\Logger', 'Magento\Framework\Logger'),
    array('Magento\Core\Block\Template\Context', 'Magento\Framework\View\Element\Template\Context'),
    array('Magento\Page\Block\Template\Container'),
    array('Magento\Page\Block\Redirect', 'Magento\Framework\View\Element\Redirect'),
    array('Magento\Page\Block\Js\Translate'),
    array('Magento\Page\Block\Js\Components', 'Magento\Framework\View\Element\Js\Components'),
    array('Magento\Page\Block\Js\Cookie', 'Magento\Framework\View\Element\Js\Cookie'),
    array('Magento\Page\Block\Html', 'Magento\Theme\Block\Html'),
    array('Magento\Page\Block\Html\Breadcrumbs', 'Magento\Theme\Block\Html\Breadcrumbs'),
    array('Magento\Page\Block\Html\Footer', 'Magento\Theme\Block\Html\Footer'),
    array('Magento\Page\Block\Html\Head', 'Magento\Theme\Block\Html\Head'),
    array('Magento\Page\Block\Html\Header', 'Magento\Theme\Block\Html\Header'),
    array('Magento\Page\Block\Html\Notices', 'Magento\Theme\Block\Html\Notices'),
    array('Magento\Page\Block\Html\Pager', 'Magento\Theme\Block\Html\Pager'),
    array('Magento\Page\Block\Html\Title', 'Magento\Theme\Block\Html\Title'),
    array('Magento\Page\Block\Html\Topmenu', 'Magento\Theme\Block\Html\Topmenu'),
    array('Magento\Page\Block\Html\Welcome', 'Magento\Theme\Block\Html\Welcome'),
    array('Magento\Page\Helper\Layout', 'Magento\Theme\Helper\Layout'),
    array('Magento\Page\Model\Source\Layout', 'Magento\Theme\Model\Layout\Source\Layout'),
    array('Magento\Page\Model\Config\Converter', 'Magento\Theme\Model\Layout\Config\Converter'),
    array('Magento\Page\Model\Config\Reader', 'Magento\Theme\Model\Layout\Config\Reader'),
    array('Magento\Page\Model\Config\SchemaLocator', 'Magento\Theme\Model\Layout\Config\SchemaLocator'),
    array('Magento\Page\Helper\Data'),
    array('Magento\Page\Helper\Html'),
    array('Magento\Page\Helper\Robots'),
    array('Magento\Core\Model\Page'),
    array('Magento\Core\Model\Page\Asset\AssetInterface', 'Magento\Framework\View\Asset\AssetInterface'),
    array('Magento\Core\Model\Page\Asset\Collection', 'Magento\Framework\View\Asset\Collection'),
    array('Magento\Core\Model\Page\Asset\LocalInterface', 'Magento\Framework\View\Asset\LocalInterface'),
    array('Magento\Core\Model\Page\Asset\MergeService', 'Magento\Framework\View\Asset\MergeService'),
    array(
        'Magento\Core\Model\Page\Asset\MergeStrategy\Checksum',
        'Magento\Framework\View\Asset\MergeStrategy\Checksum'
    ),
    array('Magento\Core\Model\Page\Asset\MergeStrategy\Direct', 'Magento\Framework\View\Asset\MergeStrategy\Direct'),
    array(
        'Magento\Core\Model\Page\Asset\MergeStrategy\FileExists',
        'Magento\Framework\View\Asset\MergeStrategy\FileExists'
    ),
    array(
        'Magento\Core\Model\Page\Asset\MergeStrategyInterface',
        'Magento\Framework\View\Asset\MergeStrategyInterface'
    ),
    array('Magento\Core\Model\Page\Asset\MergeableInterface', 'Magento\Framework\View\Asset\MergeableInterface'),
    array('Magento\Core\Model\Page\Asset\Merged', 'Magento\Framework\View\Asset\Merged'),
    array('Magento\Core\Model\Page\Asset\Minified', 'Magento\Framework\View\Asset\Minified'),
    array('Magento\Core\Model\Page\Asset\MinifyService', 'Magento\Framework\View\Asset\MinifyService'),
    array('Magento\Core\Model\Page\Asset\PublicFile', 'Magento\Framework\View\Asset\PublicFile'),
    array('Magento\Core\Model\Page\Asset\Remote', 'Magento\Framework\View\Asset\Remote'),
    array('Magento\Core\Model\Page\Asset\ViewFile', 'Magento\Framework\View\Asset\File'),
    array('Magento\Page\Block\Html\Head\AssetBlock', 'Magento\Theme\Block\Html\Head\AssetBlockInterface'),
    array('Magento\Page\Block\Html\Head\Css', 'Magento\Theme\Block\Html\Head\Css'),
    array('Magento\Page\Block\Html\Head\Link', 'Magento\Theme\Block\Html\Head\Link'),
    array('Magento\Page\Block\Html\Head\Script', 'Magento\Theme\Block\Html\Head\Script'),
    array('Magento\Page\Model\Asset\GroupedCollection', 'Magento\Framework\View\Asset\GroupedCollection'),
    array('Magento\Page\Model\Asset\PropertyGroup', 'Magento\Framework\View\Asset\PropertyGroup'),
    array('Magento\Page\Block\Template\Links\Block'),
    array('Magento\Page\Block\Link\Current', 'Magento\Framework\View\Element\Html\Link\Current'),
    array('Magento\Page\Block\Links', 'Magento\Framework\View\Element\Html\Links'),
    array('Magento\Page\Block\Link', 'Magento\Framework\View\Element\Html\Link'),
    array(
        'Magento\Core\Model\Layout\Argument\HandlerInterface',
        'Magento\Framework\View\Layout\Argument\HandlerInterface'
    ),
    array('Magento\Core\Model\Layout\Argument\HandlerFactory', 'Magento\Framework\View\Layout\Argument\HandlerFactory'),
    array('Magento\Core\Model\Theme\Label', 'Magento\Framework\View\Design\Theme\Label'),
    array('Magento\Core\Model\Theme\LabelFactory', 'Magento\Framework\View\Design\Theme\LabelFactory'),
    array('Magento\Core\Model\DesignLoader', 'Magento\Framework\View\DesignLoader'),
    array('Magento\Page\Block\Switcher', 'Magento\Store\Block\Switcher'),
    array('Magento\Core\Model\Layout\PageType\Config', 'Magento\Framework\View\Layout\PageType\Config'),
    array(
        'Magento\Core\Model\Layout\PageType\Config\Converter',
        'Magento\Framework\View\Layout\PageType\Config\Converter'
    ),
    array('Magento\Core\Model\Layout\PageType\Config\Reader', 'Magento\Framework\View\Layout\PageType\Config\Reader'),
    array(
        'Magento\Core\Model\Layout\PageType\Config\SchemaLocator',
        'Magento\Framework\View\Layout\PageType\Config\SchemaLocator'
    ),
    array('Magento\Core\Model\Theme\CopyService', 'Magento\Theme\Model\CopyService'),
    array('Magento\Core\Model\Resource\Session', 'Magento\Framework\Session\SaveHandler\DbTable'),
    array('Magento\Core\Model\Session\Exception', 'Magento\Framework\Session\Exception'),
    array('Magento\Core\Model\Session\Context'),
    array('Magento\Core\Model\Session\AbstractSession', 'Magento\Framework\Session\SessionManager'),
    array('Magento\Catalog\Model\Resource\Convert'),
    array('Magento\Reminder\Model\Resource\HelperFactory'),
    array('Magento\Reminder\Model\Resource\Helper'),
    array('Magento\Core\Model\ConfigInterface', 'Magento\Framework\App\Config\ScopeConfigInterface'),
    array('Magento\CatalogRule\Block\Adminhtml\Promo\Widget\Chooser'),
    array(
        'Magento\Catalog\Model\Product\Attribute\Backend\Recurring',
        'Magento\RecurringPayment\Model\Product\Attribute\Backend\Recurring'
    ),
    array(
        'Magento\Catalog\Model\Product\Type\Grouped\Backend',
        'Magento\GroupedProduct\Model\Product\Type\Grouped\Backend'
    ),
    array(
        'Magento\Catalog\Model\Product\Type\Grouped\Price',
        'Magento\GroupedProduct\Model\Product\Type\Grouped\Price'
    ),
    array(
        'Magento\Catalog\Model\Resource\Product\Indexer\Price\Grouped',
        'Magento\GroupedProduct\Model\Resource\Product\Indexer\Price\Grouped'
    ),
    array(
        'Magento\Catalog\Model\Resource\Product\Type\Grouped\AssociatedProductsCollection',
        'Magento\GroupedProduct\Model\Resource\Product\Type\Grouped\AssociatedProductsCollection'
    ),
    array('Magento\Catalog\Model\Product\Type\Grouped', 'Magento\GroupedProduct\Model\Product\Type\Grouped'),
    array(
        'Magento\Catalog\Block\Adminhtml\Product\Composite\Fieldset\Grouped',
        'Magento\GroupedProduct\Block\Adminhtml\Product\Composite\Fieldset\Grouped'
    ),
    array(
        'Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Price\Recurring',
        'Magento\RecurringPayment\Block\Adminhtml\Product\Edit\Tab\Price\Recurring'
    ),
    array('Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs\Grouped'),
    array(
        'Magento\Catalog\Block\Product\Grouped\AssociatedProducts',
        'Magento\GroupedProduct\Block\Product\Grouped\AssociatedProducts'
    ),
    array(
        'Magento\Catalog\Block\Product\Grouped\AssociatedProducts\ListAssociatedProducts',
        'Magento\GroupedProduct\Block\Product\Grouped\AssociatedProducts\ListAssociatedProducts'
    ),
    array('Magento\Catalog\Block\Product\View\Type\Grouped', 'Magento\GroupedProduct\Block\Product\View\Type\Grouped'),
    array(
        'Magento\Sales\Block\Adminhtml\Customer\Edit\Tab\Recurring\Payment',
        'Magento\RecurringPayment\Block\Adminhtml\Customer\Edit\Tab\RecurringPayment'
    ),
    array(
        'Magento\Sales\Block\Adminhtml\Items\Column\Name\Grouped',
        'Magento\GroupedProduct\Block\Adminhtml\Items\Column\Name\Grouped'
    ),
    array('Magento\Sales\Block\Adminhtml\Recurring\Profile', 'Magento\RecurringPayment\Block\Adminhtml\Payment'),
    array(
        'Magento\Sales\Block\Adminhtml\Recurring\Profile\Edit\Form',
        'Magento\RecurringPayment\Block\Adminhtml\Payment\Edit\Form'
    ),
    array(
        'Magento\Sales\Block\Adminhtml\Recurring\Profile\Grid',
        'Magento\RecurringPayment\Block\Adminhtml\Payment\Grid'
    ),
    array(
        'Magento\Sales\Block\Adminhtml\Recurring\Profile\View',
        'Magento\RecurringPayment\Block\Adminhtml\Payment\View'
    ),
    array(
        'Magento\Sales\Block\Adminhtml\Recurring\Profile\View\Getawayinfo',
        'Magento\RecurringPayment\Block\Adminhtml\Payment\View\Getawayinfo'
    ),
    array(
        'Magento\Sales\Block\Adminhtml\Recurring\Profile\View\Info',
        'Magento\RecurringPayment\Block\Adminhtml\Payment\View\Info'
    ),
    array(
        'Magento\Sales\Block\Adminhtml\Recurring\Profile\View\Items',
        'Magento\RecurringPayment\Block\Adminhtml\Payment\View\Info'
    ),
    array(
        'Magento\Sales\Block\Adminhtml\Recurring\Profile\View\Tab\Info',
        'Magento\RecurringPayment\Block\Adminhtml\Payment\View\Tab\Info'
    ),
    array(
        'Magento\Sales\Block\Adminhtml\Recurring\Profile\View\Tab\Orders',
        'Magento\RecurringPayment\Block\Adminhtml\Payment\View\Tab\Orders'
    ),
    array(
        'Magento\Sales\Model\Order\Pdf\Items\Invoice\Grouped',
        'Magento\GroupedProduct\Model\Order\Pdf\Items\Invoice\Grouped'
    ),
    array(
        'Magento\Sales\Block\Order\Item\Renderer\Grouped',
        'Magento\GroupedProduct\Block\Order\Item\Renderer\Grouped'
    ),
    array(
        'Magento\Sales\Block\Recurring\Profile\Related\Orders\Grid',
        'Magento\RecurringPayment\Block\Payment\Related\Orders\Grid'
    ),
    array('Magento\Sales\Block\Recurring\Profiles', 'Magento\RecurringPayment\Block\Profiles'),
    array('Magento\Sales\Block\Recurring\Profile\Grid', 'Magento\RecurringPayment\Block\Payment\Grid'),
    array('Magento\Sales\Block\Recurring\Profile\View', 'Magento\RecurringPayment\Block\Payment\View'),
    array('Magento\Sales\Block\Recurring\Profile\View\Address', 'Magento\RecurringPayment\Block\Payment\View\Address'),
    array('Magento\Sales\Block\Recurring\Profile\View\Data', 'Magento\RecurringPayment\Block\Payment\View\Data'),
    array('Magento\Sales\Block\Recurring\Profile\View\Fees', 'Magento\RecurringPayment\Block\Payment\View\Fees'),
    array('Magento\Sales\Block\Recurring\Profile\View\Item', 'Magento\RecurringPayment\Block\Payment\View\Item'),
    array(
        'Magento\Sales\Block\Recurring\Profile\View\Reference',
        'Magento\RecurringPayment\Block\Payment\View\Reference'
    ),
    array(
        'Magento\Sales\Block\Recurring\Profile\View\Schedule',
        'Magento\RecurringPayment\Block\Payment\View\Schedule'
    ),
    array(
        'Magento\ImportExport\Model\Export\Entity\Product\Type\Grouped',
        'Magento\CatalogImportExport\Model\Export\Entity\Product\Type\Grouped'
    ),
    array(
        'Magento\ImportExport\Model\Import\Entity\Product\Type\Grouped',
        'Magento\CatalogImportExport\Model\Import\Entity\Product\Type\Grouped'
    ),
    array(
        'Magento\GroupedProduct\Model\Export\Entity\Product\Type\Grouped',
        'Magento\CatalogImportExport\Model\Export\Entity\Product\Type\Grouped'
    ),
    array(
        'Magento\GroupedProduct\Model\Import\Entity\Product\Type\Grouped',
        'Magento\CatalogImportExport\Model\Import\Entity\Product\Type\Grouped'
    ),
    array('CollFactory', 'CollectionFactory'), // no need to shorten anymore
    array(
        'Magento\Shipping\Model\Rate\Result\AbstractResult',
        'Magento\Sales\Model\Quote\Address\RateResult\AbstractResult'
    ),
    array('Magento\Shipping\Model\Rate\Result\Error', 'Magento\Sales\Model\Quote\Address\RateResult\Error'),
    array('Magento\Shipping\Model\Rate\Result\Method', 'Magento\Sales\Model\Quote\Address\RateResult\Method'),
    array(
        'Magento\Shipping\Model\Rate\AbstractRate',
        'Magento\Sales\Model\Quote\Address\Rate + Magento\Shipping\Model\CarrierFactory'
    ),
    array('Magento\Shipping\Model\Rate\Request', 'Magento\Sales\Model\Quote\Address\RateRequest'),
    array('Magento\PageCache\Block\Adminhtml\Cache\Additional'),
    array('Magento\PageCache\Model\Control\ControlInterface'),
    array('Magento\PageCache\Model\Control\Zend'),
    array('Magento\PageCache\Model\System\Config\Source\Controls'),
    array('Magento\PageCache\Model\CacheControlFactory'),
    array('Magento\Catalog\Block\Adminhtml\System\Config\Form\Field\Select\Flatcatalog'),
    array('Magento\Catalog\Helper\Category\Flat'),
    array('Magento\Catalog\Model\Category\Indexer\Flat'),
    array('Magento\Framework\Config\Dom\Converter\ArrayConverter'),
    array('Magento\Framework\Acl\Resource\Config\Dom'),
    array('Magento\Sales\Model\Recurring\Profile', 'Magento\RecurringPayment\Model\Payment'),
    array('Magento\Sales\Model\Resource\Recurring\Profile', 'Magento\RecurringPayment\Model\Resource\Payment'),
    array(
        'Magento\Sales\Model\Resource\Recurring\Profile\Collection',
        'Magento\RecurringPayment\Model\Resource\Payment\Collection'
    ),
    array('Magento\Payment\Model\Recurring\Profile', 'Magento\RecurringPayment\Model\RecurringPayment'),
    array('Magento\Validator\Composite\VarienObject', 'Magento\Framework\Validator\Object'),
    array('Magento\GoogleShopping\Helper\Price', 'Magento\Catalog\Model\Product\CatalogPrice'),
    array(
        'Magento\Core\Model\Layout\Argument\Handler\ArrayHandler',
        'Magento\Framework\Data\Argument\Interpreter\ArrayType'
    ),
    array('Magento\Core\Model\Layout\Argument\Handler\String', 'Magento\Framework\Data\Argument\Interpreter\String'),
    array('Magento\Core\Model\Layout\Argument\Handler\Number', 'Magento\Framework\Data\Argument\Interpreter\Number'),
    array('Magento\Core\Model\Layout\Argument\Handler\Boolean', 'Magento\Framework\Data\Argument\Interpreter\Boolean'),
    array(
        'Magento\Core\Model\Layout\Argument\Handler\Object',
        'Magento\Framework\View\Layout\Argument\Interpreter\Object'
    ),
    array(
        'Magento\Core\Model\Layout\Argument\Handler\Options',
        'Magento\Framework\View\Layout\Argument\Interpreter\Options'
    ),
    array('Magento\Core\Model\Layout\Argument\Handler\Url', 'Magento\Framework\View\Layout\Argument\Interpreter\Url'),
    array(
        'Magento\Core\Model\Layout\Argument\Handler\Helper',
        'Magento\Framework\View\Layout\Argument\Interpreter\HelperMethod'
    ),
    array(
        'Magento\Core\Model\Layout\Argument\AbstractHandler',
        'Magento\Framework\View\Layout\Argument\Interpreter\Decorator\Updater'
    ),
    array(
        'Magento\Core\Model\Layout\Argument\Processor',
        'Magento\Framework\View\Layout\Argument\Interpreter\Decorator\Updater'
    ),
    array(
        'Magento\Core\Model\Layout\Argument\Updater',
        'Magento\Framework\View\Layout\Argument\Interpreter\Decorator\Updater'
    ),
    array(
        'Magento\Core\Model\Layout\Argument\UpdaterInterface',
        'Magento\Framework\View\Layout\Argument\UpdaterInterface'
    ),
    array('Magento\Core\Model\Layout\Filter\Acl', 'Magento\Backend\Model\Layout\Filter\Acl'),
    array(
        'Magento\Framework\View\Layout\Argument\HandlerInterface',
        'Magento\Framework\Data\Argument\InterpreterInterface'
    ),
    array(
        'Magento\Framework\View\Layout\Argument\HandlerFactory',
        'Magento\Framework\Data\Argument\Interpreter\Composite'
    ),
    array('Magento\Framework\Phrase\Renderer\Factory'),
    array('Magento\Framework\View\Layout\Factory', 'Magento\DesignEditor\Model\AreaEmulator'),
    array('Magento\Catalog\Model\Category\Indexer\Product'),
    array('Magento\Catalog\Model\Resource\Category\Indexer\Product'),
    array('Magento\Catalog\Model\Index'),
    array('Magento\Catalog\Model\Product\Status', 'Magento\Catalog\Model\Product\Attribute\Source\Status'),
    array('Magento\Catalog\Model\Resource\Product\Status'),
    array(
        'Magento\CatalogInventory\Block\Stockqty\Type\Configurable',
        'Magento\ConfigurableProduct\Block\Stockqty\Type\Configurable'
    ),
    array(
        'Magento\CatalogInventory\Model\Resource\Indexer\Stock\Configurable',
        'Magento\ConfigurableProduct\Model\Resource\Indexer\Stock\Configurable'
    ),
    array(
        'Magento\ImportExport\Model\Export\Entity\Product\Type\Configurable',
        'Magento\CatalogImportExport\Model\Export\Product\Type\Configurable'
    ),
    array(
        'Magento\ConfigurableProduct\Model\Export\Entity\Product\Type\Configurable',
        'Magento\CatalogImportExport\Model\Export\Product\Type\Configurable'
    ),
    array(
        'Magento\ImportExport\Model\Import\Entity\Product\Type\Configurable',
        'Magento\CatalogImportExport\Model\Import\Product\Type\Configurable'
    ),
    array(
        'Magento\ConfigurableProduct\Model\Import\Entity\Product\Type\Configurable',
        'Magento\CatalogImportExport\Model\Import\Product\Type\Configurable'
    ),
    array('Magento\Sales\Block\Adminhtml\Items\Renderer\Configurable'),
    array(
        'Magento\Catalog\Model\Resource\Product\Collection\AssociatedProduct',
        'Magento\ConfigurableProduct\Model\Resource\Product\Collection\AssociatedProduct'
    ),
    array('Magento\Catalog\Model\Resource\Product\Collection\AssociatedProductUpdater'),
    array(
        'Magento\Sales\Controller\Adminhtml\Recurring\Profile',
        'Magento\RecurringPayment\Controller\Adminhtml\Payment'
    ),
    array('Magento\Sales\Controller\Recurring\Profile', 'Magento\RecurringPayment\Controller\Payment'),
    array('Magento\Core\Model\Image\Adapter\Config', 'Magento\Framework\Image\Adapter\Config'),
    array('Magento\Core\Model\AbstractShell', 'Magento\Framework\App\AbstractShell'),
    array('Magento\Core\Model\Calculator', 'Magento\Framework\Math\Calculator'),
    array('Magento\Core\Model\Log\Adapter', 'Magento\Framework\Logger\Adapter'),
    array('Magento\Core\Model\Input\Filter', 'Magento\Framework\Filter\Input'),
    array('Magento\Core\Model\Input\Filter\MaliciousCode', 'Magento\Framework\Filter\Input\MaliciousCode'),
    array('Magento\Core\Model\Option\ArrayInterface', 'Magento\Framework\Option\ArrayInterface'),
    array('Magento\Core\Model\Option\ArrayPool', 'Magento\Framework\Option\ArrayPool'),
    array('Magento\Core\Helper\String', 'Magento\Framework\Code\NameBuilder'),
    array('Magento\Core\Model\Context', 'Magento\Framework\Model\Context'),
    array('Magento\Core\Model\Registry', 'Magento\Framework\Registry'),
    array('Magento\Framework\Code\Plugin\InvocationChain'),
    array('RecurringProfile', 'RecurringPayment'), // recurring profile was renamed to recurring payment
    array('Recurring\Profile', 'Recurring\Payment'), // recurring profile was renamed to recurring payment
    array('Magento\Catalog\Helper\Product\Flat'),
    array('Magento\Catalog\Helper\Flat\AbstractFlat'),
    array('Magento\Core\App\Action\Plugin\Install', 'Magento\Framework\App\Bootstrap'),
    array('Magento\Core\App\Action\Plugin\Session', 'Magento\Core\Block\RequireCookie'),
    array(
        'Magento\Core\Model\LocaleInterface',
        'Magento\Framework\Locale\ResolverInterface, Magento\Framework\Locale\CurrencyInterface,' .
        'Magento\Framework\Locale\FormatInterface, Magento\Framework\Stdlib\DateTime\TimezoneInterface'
    ),
    array(
        'Magento\Core\Model\Locale',
        'Magento\Framework\Locale\Resolver, Magento\Framework\Locale\Currency, Magento\Framework\Locale\Format, ' .
        'Magento\Framework\Stdlib\DateTime\Timezone, Magento\Framework\Locale\Lists'
    ),
    array('Magento\Framework\Locale\Hierarchy\Config\Converter', 'Magento\Framework\App\Language\Dictionary'),
    array('Magento\Framework\Locale\Hierarchy\Config\FileResolver', 'Magento\Framework\App\Language\Dictionary'),
    array('Magento\Framework\Locale\Hierarchy\Config\Reader', 'Magento\Framework\App\Language\Dictionary'),
    array('Magento\Framework\Locale\Hierarchy\Config\SchemaLocator', 'Magento\Framework\App\Language\Dictionary'),
    array('Magento\Framework\Locale\Hierarchy\Config', 'Magento\Framework\App\Language\Dictionary'),
    array('Magento\Core\Model\Locale\Config', 'Magento\Framework\Locale\Config'),
    array('Magento\Core\Model\Locale\Validator', 'Magento\Framework\Locale\Validator'),
    array('Magento\Core\Model\Date', 'Magento\Framework\Stdlib\DateTime\DateTime'),
    array('Magento\Shipping\Model\Config\Source\Flatrate', 'Magento\OfflineShipping\Model\Config\Source\Flatrate'),
    array('Magento\Shipping\Model\Carrier\Flatrate', 'Magento\OfflineShipping\Model\Carrier\Flatrate'),
    array('Magento\Usa\Block\Adminhtml\Dhl\Unitofmeasure', 'Magento\Dhl\Block\Adminhtml\Unitofmeasure'),
    array('Magento\Usa\Model\Shipping\Carrier\Dhl\International', 'Magento\Dhl\Model\Carrier'),
    array(
        'Magento\Usa\Model\Shipping\Carrier\Dhl\International\Source\Method\AbstractMethod',
        'Magento\Dhl\Model\Source\Method\AbstractMethod'
    ),
    array(
        'Magento\Usa\Model\Shipping\Carrier\Dhl\International\Source\Method\Doc',
        'Magento\Dhl\Model\Source\Method\Doc'
    ),
    array(
        'Magento\Usa\Model\Shipping\Carrier\Dhl\International\Source\Method\Freedoc',
        'Magento\Dhl\Model\Source\Method\Freedoc'
    ),
    array(
        'Magento\Usa\Model\Shipping\Carrier\Dhl\International\Source\Method\Freenondoc',
        'Magento\Dhl\Model\Source\Method\Freenondoc'
    ),
    array(
        'Magento\Usa\Model\Shipping\Carrier\Dhl\International\Source\Method\Generic',
        'Magento\Dhl\Model\Source\Method\Generic'
    ),
    array(
        'Magento\Usa\Model\Shipping\Carrier\Dhl\International\Source\Method\Nondoc',
        'Magento\Dhl\Model\Source\Method\Nondoc'
    ),
    array(
        'Magento\Usa\Model\Shipping\Carrier\Dhl\International\Source\Method\Size',
        'Magento\Dhl\Model\Source\Method\Size'
    ),
    array(
        'Magento\Usa\Model\Shipping\Carrier\Dhl\International\Source\Method\Unitofmeasure',
        'Magento\Dhl\Model\Source\Method\Unitofmeasure'
    ),
    array('Magento\Usa\Model\Shipping\Carrier\Dhl\AbstractDhl', 'Magento\Dhl\Model\AbstractDhl'),
    array('Magento\Usa\Model\Shipping\Carrier\Dhl'),
    array('Magento\Usa\Model\Shipping\Carrier\Fedex', 'Magento\Fedex\Model\Carrier'),
    array('Magento\Usa\Model\Shipping\Carrier\Fedex\Source\Droppff', 'Magento\Fedex\Model\Source\Droppff'),
    array('Magento\Usa\Model\Shipping\Carrier\Fedex\Source\Freemethod', 'Magento\Fedex\Model\Source\Freemethod'),
    array('Magento\Usa\Model\Shipping\Carrier\Fedex\Source\Generic', 'Magento\Fedex\Model\Source\Generic'),
    array('Magento\Usa\Model\Shipping\Carrier\Fedex\Source\Method', 'Magento\Fedex\Model\Source\Method'),
    array('Magento\Usa\Model\Shipping\Carrier\Fedex\Source\Packaging', 'Magento\Fedex\Model\Source\Packaging'),
    array('Magento\Rma\Model\CarrierFactory'),
    array('Magento\Usa\Helper\Data'),
    array('Magento\Usa\Model\Shipping\Carrier\Ups\Source\Mode'),
    array('Magento\Usa\Model\Shipping\Carrier\Ups\Source\Container', 'Magento\Ups\Model\Config\Source\Container'),
    array('Magento\Usa\Model\Shipping\Carrier\Ups\Source\DestType', 'Magento\Ups\Model\Config\Source\DestType'),
    array('Magento\Usa\Model\Shipping\Carrier\Ups\Source\Freemethod', 'Magento\Ups\Model\Config\Source\Freemethod'),
    array('Magento\Usa\Model\Shipping\Carrier\Ups\Source\Generic', 'Magento\Ups\Model\Config\Source\Generic'),
    array('Magento\Usa\Model\Shipping\Carrier\Ups\Source\Method', 'Magento\Ups\Model\Config\Source\Method'),
    array(
        'Magento\Usa\Model\Shipping\Carrier\Ups\Source\OriginShipment',
        'Magento\Ups\Model\Config\Source\OriginShipment'
    ),
    array('Magento\Usa\Model\Shipping\Carrier\Ups\Source\Pickup', 'Magento\Ups\Model\Config\Source\Pickup'),
    array('Magento\Usa\Model\Shipping\Carrier\Ups\Source\Type', 'Magento\Ups\Model\Config\Source\Type'),
    array(
        'Magento\Usa\Model\Shipping\Carrier\Ups\Source\Unitofmeasure',
        'Magento\Ups\Model\Config\Source\Unitofmeasure'
    ),
    array('Magento\Usa\Model\Shipping\Carrier\Usps\Source\Container', 'Magento\Usps\Model\Source\Container'),
    array('Magento\Usa\Model\Shipping\Carrier\Usps\Source\Freemethod', 'Magento\Usps\Model\Source\Freemethod'),
    array('Magento\Usa\Model\Shipping\Carrier\Usps\Source\Generic', 'Magento\Usps\Model\Source\Generic'),
    array('Magento\Usa\Model\Shipping\Carrier\Usps\Source\Machinable', 'Magento\Usps\Model\Source\Machinable'),
    array('Magento\Usa\Model\Shipping\Carrier\Usps\Source\Method', 'Magento\Usps\Model\Source\Method'),
    array('Magento\Usa\Model\Shipping\Carrier\Usps\Source\Size', 'Magento\Usps\Model\Source\Size'),
    array('Magento\Usa\Model\Shipping\Carrier\Usps', 'Magento\Usps\Model\Carrier'),
    array('Magento\Usa\Model\Shipping\Carrier\Ups', 'Magento\Ups\Model\Carrier'),
    array('Magento\Usa\Model\Simplexml\Element', 'Magento\Shipping\Model\Simplexml\Element'),
    array(
        'Magento\Usa\Model\Shipping\Carrier\AbstractCarrier',
        'Magento\Shipping\Model\Carrier\AbstractCarrierOnline'
    ),
    array(
        'Magento\Usa\Model\Shipping\Carrier\AbstractCarrier\Source\Mode',
        'Magento\Shipping\Model\Config\Source\Online\Mode'
    ),
    array(
        'Magento\Usa\Model\Shipping\Carrier\AbstractCarrier\Source\Requesttype',
        'Magento\Shipping\Model\Config\Source\Online\Requesttype'
    ),
    array('Magento\Catalog\Helper\Product\Url', 'Magento\Framework\Filter\Translit'),
    array('Magento\Catalog\Model\Product\Indexer\Price'),
    array('Magento\Catalog\Model\Resource\Product\Indexer\Price'),
    ['Magento\PubSub'], // unused library code which was removed
    ['Magento\Outbound'], // unused library code which was removed
    array('Magento\Indexer\Model\Processor\CacheInvalidate', 'Magento\Indexer\Model\Processor\InvalidateCache'),
    array(
        'Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Reviews',
        'Magento\Review\Block\Adminhtml\Product\Edit\Tab\Reviews'
    ),
    array(
        'Magento\Catalog\Controller\Adminhtml\Product\Review',
        'Magento\Review\Controller\Adminhtml\Product'
    ),
    array(
        'Magento\Review\Block\Helper',
        'Magento\Review\Block\Product\ReviewRenderer'
    ),
    array(
        'Magento\LauncherInterface',
        'Magento\Framework\AppInterface',
    ),
    array('Magento\Framework\Convert\ConvertException'),
    array('Magento\Framework\Convert\Container\AbstractContainer'),
    array('Magento\Framework\Convert\Mapper\Column'),
    array('Magento\Framework\Convert\Mapper\MapperInterface'),
    ['Magento\Core\Controller\Ajax', 'Magento\Translation\Controller\Ajax'],
    ['Magento\Core\Helper\Translate', 'Magento\Translation\Helper\Data'],
    ['Magento\Core\Model\Translate\Inline\Config', 'Magento\Translation\Model\Inline\Config'],
    ['Magento\Core\Model\Translate\Inline\Parser', 'Magento\Translation\Model\Inline\Parser'],
    ['Magento\Core\Model\Resource\Translate\String', 'Magento\Translation\Model\Resource\String'],
    ['Magento\Core\Model\Resource\Translate', 'Magento\Translation\Model\Resource\Translate'],
    ['Magento\Core\Model\Translate\String', 'Magento\Translation\Model\String'],
    ['Magento\Translation\Helper\Data'],
    ['Magento\Framework\Translate\Factory'],
    ['Magento\Backend\Model\Translate'],
    ['Magento\DesignEditor\Model\Translate\InlineVde', 'Magento\DesignEditor\Model\Translate\Inline'],
    ['Magento\Backend\Model\Translate\Inline\ConfigFactory'],
    ['Magento\Framework\Translate\Inline\ConfigFactory'],
    ['Magento\Bundle\Model\Price\Index'],
    ['Magento\Bundle\Model\Resource\Price\Index'],
    array('Magento\Core\Model\Template', 'Magento\Email\Model\AbstractTemplate'),
    array('Magento\Core\Helper\Js'),
    array('Magento\Backend\Helper\Media\Js'),
    array(
        'Magento\Core\Model\Resource\Url\Rewrite\Collection',
        'Magento\UrlRewrite\Model\Resource\UrlRewriteCollection'
    ),
    array(
        'Magento\Core\Model\Resource\Url\Rewrite',
        'Magento\UrlRewrite\Model\Resource\UrlRewrite'
    ),
    array(
        'Magento\Core\Model\Url\Rewrite',
        'Magento\UrlRewrite\Model\UrlRewrite'
    ),
    array(
        'Magento\Core\Model\Source\Urlrewrite\Options',
        'Magento\UrlRewrite\Model\UrlRewrite\OptionProvider'
    ),
    array(
        'Magento\Core\Model\Source\Urlrewrite\Types',
        'Magento\UrlRewrite\Model\UrlRewrite\TypeProvider'
    ),
    array(
        'Magento\Core\Helper\Url\Rewrite',
        'Magento\UrlRewrite\Helper\UrlRewrite'
    ),
    array(
        'Magento\Core\App\FrontController\Plugin\UrlRewrite',
        'Magento\UrlRewrite\App\FrontController\Plugin\UrlRewrite'
    ),
    array(
        'Magento\Core\App\Request\RewriteService',
        'Magento\UrlRewrite\App\Request\RewriteService'
    ),
    ['Magento\Framework\App\ConfigInterface', 'Magento\Framework\App\Config\ScopeConfigInterface'],
    ['Magento\Core\Model\Store\ConfigInterface', 'Magento\Framework\App\Config\ScopeConfigInterface'],
    ['Magento\Core\Model\Store\Config', 'Magento\Framework\App\Config\ScopeConfigInterface'],
    ['Magento\Framework\App\Locale\ScopeConfigInterface', 'Magento\Framework\App\Config\ScopeConfigInterface'],
    ['Magento\Core\App\Action\Plugin\StoreCheck', 'Magento\Store\App\Action\Plugin\StoreCheck'],
    [
        'Magento\Store\App\FrontController\Plugin\DispatchExceptionHandler',
        'Magento\Framework\App\Bootstrap'
    ],
    [
        'Magento\Core\App\FrontController\Plugin\RequestPreprocessor',
        'Magento\Store\App\FrontController\Plugin\RequestPreprocessor'
    ],
    ['Magento\Core\App\Response\Redirect', 'Magento\Store\App\Response\Redirect'],
    ['Magento\Core\Block\Store\Switcher', 'Magento\Store\Block\Store\Switcher'],
    ['Magento\Core\Block\Switcher', 'Magento\Store\Block\Switcher'],
    ['Magento\Core\Helper\Cookie', 'Magento\Store\Helper\Cookie'],
    ['Magento\Core\Model\BaseScopeResolver'],
    ['Magento\Core\Model\Config\Scope\Processor\Placeholder', 'Magento\Store\Model\Config\Processor\Placeholder'],
    ['Magento\Core\Model\Config\Scope\Reader\DefaultReader', 'Magento\Store\Model\Config\Reader\DefaultReader'],
    ['Magento\Core\Model\Config\Scope\Reader\Store', 'Magento\Store\Model\Config\Reader\Store'],
    ['Magento\Core\Model\Config\Scope\Reader\Website', 'Magento\Store\Model\Config\Reader\Website'],
    ['Magento\Core\Model\Config\Scope\ReaderPool', 'Magento\Store\Model\Config\Reader\ReaderPool'],
    ['Magento\Core\Model\Resource\Store', 'Magento\Store\Model\Resource\Store'],
    ['Magento\Core\Model\Resource\Store\Collection', 'Magento\Store\Model\Resource\Store\Collection'],
    ['Magento\Core\Model\Resource\Store\Group', 'Magento\Store\Model\Resource\Group'],
    ['Magento\Core\Model\Resource\Store\Group\Collection', 'Magento\Store\Model\Resource\Group\Collection'],
    ['Magento\Core\Model\Resource\Website', 'Magento\Store\Model\Resource\Website'],
    ['Magento\Core\Model\Resource\Website\Collection', 'Magento\Store\Model\Resource\Website\Collection'],
    ['Magento\Core\Model\Resource\Website\Grid\Collection', 'Magento\Store\Model\Resource\Website\Grid\Collection'],
    ['Magento\Core\Model\ScopeInterface', 'Magento\Store\Model\ScopeInterface'],
    ['Magento\Core\Model\Store', 'Magento\Store\Model\Store'],
    ['Magento\Store\Model\Exception', 'Magento\Framework\Model\Exception, Magento\Framework\App\InitException'],
    ['Magento\Core\Model\Store\Group', 'Magento\Store\Model\Group'],
    ['Magento\Core\Model\Store\Group\Factory', 'Magento\Store\Model\GroupFactory'],
    ['Magento\Core\Model\Store\Storage\Db', 'Magento\Store\Model\Storage\Db'],
    ['Magento\Core\Model\Store\Storage\DefaultStorage', 'Magento\Store\Model\Storage\DefaultStorage'],
    ['Magento\Core\Model\Store\StorageFactory', 'Magento\Store\Model\StorageFactory'],
    ['Magento\Core\Model\StoreManager', 'Magento\Store\Model\StoreManager'],
    ['Magento\Store\Model\StoreManagerInterface', 'Magento\Framework\StoreManagerInterface'],
    ['Magento\Core\Model\System\Store', 'Magento\Store\Model\System\Store'],
    ['Magento\Core\Model\Website', 'Magento\Store\Model\Website'],
    ['Magento\Core\Model\Website\Factory', 'Magento\Store\Model\WebsiteFactory'],
    ['Magento\Framework\App\ReinitableConfigInterface', 'Magento\Framework\App\Config\ReinitableConfigInterface'],
    ['Magento\BaseScopeInterface', 'Magento\Framework\App\ScopeInterface'],
    ['Magento\BaseScopeResolverInterface', 'Magento\Framework\App\ScopeResolverInterface'],
    ['Magento\Framework\Locale\ScopeConfigInterface'],
    ['Magento\Core\Model\Module\Output\Config', 'Magento\Framework\Module\Output\Config'],
    ['Magento\Core\Model\Resource\Setup\Context', 'Magento\Framework\Module\Setup\Context'],
    ['Magento\Core\Model\Resource\Setup\Migration', 'Magento\Framework\Module\Setup\Migration'],
    ['Magento\Core\Model\Resource\Setup\Generic'],
    ['Magento\Newsletter\Model\Resource\Setup'],
    ['Magento\SalesRule\Model\Resource\Setup'],
    array('Magento\Core\Model\Session', 'Magento\Framework\Session\Generic'),
    array('Magento\Core\Model\Session\Config', 'Magento\Framework\Session\Config'),
    array('Magento\Core\Model\Session\SidResolver', 'Magento\Framework\Session\SidResolver'),
    array('Magento\Core\Model\Session\Validator', 'Magento\Framework\Session\Validator'),
    ['Magento\Core\Block\Formkey', 'Magento\Framework\View\Element\FormKey'],
    ['Magento\Rating\Helper\Data', 'Magento\Review\Helper\Data'],
    ['Magento\Rating\Controller\Adminhtml\Index', 'Magento\Review\Controller\Adminhtml\Rating'],
    ['Magento\Rating\Block\Entity\Detailed', 'Magento\Review\Block\Rating\Entity\Detailed'],
    ['Magento\Rating\Block\Adminhtml\Rating', 'Magento\Review\Block\Adminhtml\Rating'],
    ['Magento\Rating\Block\Adminhtml\Edit', 'Magento\Review\Block\Adminhtml\Rating\Edit'],
    ['Magento\Rating\Block\Adminhtml\Edit\Tabs', 'Magento\Review\Block\Adminhtml\Rating\Edit\Tabs'],
    ['Magento\Rating\Block\Adminhtml\Edit\Form', 'Magento\Review\Block\Adminhtml\Rating\Edit\Form'],
    ['Magento\Rating\Block\Adminhtml\Edit\Tab\Form', 'Magento\Review\Block\Adminhtml\Rating\Edit\Tab\Form'],
    ['Magento\Rating\Block\Adminhtml\Edit\Tab\Options'],
    ['Magento\Rating\Model\Rating', 'Magento\Review\Model\Rating'],
    [
        'Magento\Rating\Model\Resource\Rating\Option\Vote\Collection',
        'Magento\Review\Model\Resource\Rating\Option\Vote\Collection'
    ],
    [
        'Magento\Rating\Model\Resource\Rating\Option\Collection',
        'Magento\Review\Model\Resource\Rating\Option\Collection'
    ],
    ['Magento\Rating\Model\Resource\Rating\Grid\Collection', 'Magento\Review\Model\Resource\Rating\Grid\Collection'],
    ['Magento\Rating\Model\Resource\Rating\Collection', 'Magento\Review\Model\Resource\Rating\Collection'],
    ['Magento\Rating\Model\Resource\Rating\Option\Vote', 'Magento\Review\Model\Resource\Rating\Option\Vote'],
    ['Magento\Rating\Model\Rating\Option\Vote', 'Magento\Review\Model\Rating\Option\Vote'],
    ['Magento\Rating\Model\Resource\Rating\Option', 'Magento\Review\Model\Resource\Rating\Option'],
    ['Magento\Rating\Model\Resource\Rating\Entity', 'Magento\Review\Model\Resource\Rating\Entity'],
    ['Magento\Rating\Model\Rating\Entity', 'Magento\Review\Model\Rating\Entity'],
    ['Magento\Rating\Model\Resource\Rating', 'Magento\Review\Model\Resource\Rating'],
    ['Magento\Rating\Model\Rating\Option', 'Magento\Review\Model\Rating\Option'],
    ['Magento\Rating\Model\Observer'],
    ['Magento\Core\Model\App\Area\CacheIdentifierPlugin', 'Magento\PageCache\App\CacheIdentifierPlugin'],
    ['Magento\Core\Model\App\Area', 'Magento\Framework\App\Area'],
    ['Magento\Core\Model\App\Area\DesignExceptions', 'Magento\Framework\View\DesignExceptions'],
    ['Magento\Checkout\Block\Adminhtml\Agreement', 'Magento\CheckoutAgreements\Block\Adminhtml\Agreement'],
    ['Magento\Checkout\Block\Adminhtml\Agreement\Edit', 'Magento\CheckoutAgreements\Block\Adminhtml\Agreement\Edit'],
    [
        'Magento\Checkout\Block\Adminhtml\Agreement\Edit\Form',
        'Magento\CheckoutAgreements\Block\Adminhtml\Agreement\Edit\Form'
    ],
    ['Magento\Checkout\Block\Adminhtml\Agreement\Grid', 'Magento\CheckoutAgreements\Block\Adminhtml\Agreement\Grid'],
    ['Magento\Checkout\Block\Agreements', 'Magento\CheckoutAgreements\Block\Agreements'],
    ['Magento\Checkout\Controller\Adminhtml\Agreement', 'Magento\CheckoutAgreements\Controller\Adminhtml\Agreement'],
    ['Magento\Checkout\Model\Resource\Agreement', 'Magento\CheckoutAgreements\Model\Resource\Agreement'],
    [
        'Magento\Checkout\Model\Resource\Agreement\Collection',
        'Magento\CheckoutAgreements\Model\Resource\Agreement\Collection'
    ],
    array('Magento\Sales\Block\Adminhtml\Invoice\Grid'),
    array('Magento\Sales\Block\Adminhtml\Shipment\Grid'),
    array('Magento\Sales\Block\Adminhtml\Creditmemo\Grid'),
    array('Magento\Sales\Block\Adminhtml\Transactions\Grid'),
    array('Magento\Sales\Block\Adminhtml\Transactions\Child\Grid'),
    ['Magento\Catalog\Model\PriceCurrency'],
    [
        'Magento\Framework\App\FrontController\Plugin\Clickjacking',
        'X-Frame-Options HTTP header setting moved to server configuration'
    ],
    ['Magento\Backend\Model\Translate\Inline', 'Magento\Framework\Translate\Inline'],
    ['Magento\Backend\Model\Resource\Translate', 'Magento\Translation\Model\Resource\Translate'],
    ['Magento\Backend\Model\Resource\Translate\String', 'Magento\Translation\Model\Resource\String'],
    ['Magento\Core\Model\Layout', 'Magento\Framework\View\Layout'],
    ['Magento\Catalog\Block\Product\Price\Template'],
    ['Magento\Bundle\Block\Catalog\Product\View'],
    ['Magento\Backup\Archive\Tar', 'Magento\Framework\Backup\Archive\Tar'],
    ['Magento\Backup\Db\BackupDbInterface', 'Magento\Framework\Backup\Db\BackupDbInterface'],
    ['Magento\Backup\Db\BackupFactory', 'Magento\Framework\Backup\Db\BackupFactory'],
    ['Magento\Backup\Db\BackupInterface', 'Magento\Framework\Backup\Db\BackupInterface'],
    ['Magento\Backup\Exception\CantLoadSnapshot', 'Magento\Framework\Backup\Exception\CantLoadSnapshot'],
    ['Magento\Backup\Exception\FtpConnectionFailed', 'Magento\Framework\Backup\Exception\FtpConnectionFailed'],
    ['Magento\Backup\Exception\FtpValidationFailed', 'Magento\Framework\Backup\Exception\FtpValidationFailed'],
    ['Magento\Backup\Exception\NotEnoughFreeSpace', 'Magento\Framework\Backup\Exception\NotEnoughFreeSpace'],
    ['Magento\Backup\Exception\NotEnoughPermissions', 'Magento\Framework\Backup\Exception\NotEnoughPermissions'],
    ['Magento\Backup\Filesystem\Iterator\File', 'Magento\Framework\Backup\Filesystem\Iterator\File'],
    ['Magento\Backup\Filesystem\Iterator\Filter', 'Magento\Framework\Backup\Filesystem\Iterator\Filter'],
    [
        'Magento\Backup\Filesystem\Rollback\AbstractRollback',
        'Magento\Framework\Backup\Filesystem\Rollback\AbstractRollback'
    ],
    ['Magento\Backup\Filesystem\Rollback\Fs', 'Magento\Framework\Backup\Filesystem\Rollback\Fs'],
    ['Magento\Backup\Filesystem\Rollback\Ftp', 'Magento\Framework\Backup\Filesystem\Rollback\Ftp'],
    ['Magento\Backup\Filesystem\Helper', 'Magento\Framework\Backup\Filesystem\Helper'],
    ['Magento\Backup\AbstractBackup', 'Magento\Framework\Backup\AbstractBackup'],
    ['Magento\Backup\BackupException', 'Magento\Framework\Backup\BackupException'],
    ['Magento\Backup\BackupInterface', 'Magento\Framework\Backup\BackupInterface'],
    ['Magento\Backup\Db', 'Magento\Framework\Backup\Db'],
    ['Magento\Backup\Factory', 'Magento\Framework\Backup\Factory'],
    ['Magento\Backup\Filesystem', 'Magento\Framework\Backup\Filesystem'],
    ['Magento\Backup\Media', 'Magento\Framework\Backup\Media'],
    ['Magento\Backup\Nomedia', 'Magento\Framework\Backup\Nomedia'],
    ['Magento\Backup\Snapshot', 'Magento\Framework\Backup\Snapshot'],
    ['Magento\Acl', 'Magento\Framework\Acl'],
    ['Magento\AclFactory', 'Magento\Framework\AclFactory'],
    ['Magento\AppInterface', 'Magento\Framework\AppInterface'],
    ['Magento\Archive', 'Magento\Framework\Archive'],
    ['Magento\Event', 'Magento\Framework\Event'],
    ['Magento\EventFactory', 'Magento\Framework\EventFactory'],
    ['Magento\Exception', 'Magento\Framework\Exception'],
    ['Magento\Filesystem', 'Magento\Framework\Filesystem'],
    ['Magento\ObjectManager', 'Magento\Framework\ObjectManager'],
    ['Magento\Translate', 'Magento\Framework\Translate'],
    ['Magento\TranslateInterface', 'Magento\Framework\TranslateInterface'],
    ['Magento\Locale', 'Magento\Framework\Locale'],
    ['Magento\LocaleFactory', 'Magento\Framework\LocaleFactory'],
    ['Magento\LocaleInterface', 'Magento\Framework\LocaleInterface'],
    ['Magento\Logger', 'Magento\Framework\Logger'],
    ['Magento\Phrase', 'Magento\Framework\Phrase'],
    ['Magento\Pear', 'Magento\Framework\Pear'],
    [
        'Magento\ImportExport\Model\Export\Product\Type\AbstractType',
        'Magento\CatalogImportExport\Model\Export\Product\Type\AbstractType'
    ],
    [
        'Magento\ImportExport\Model\Export\Product\Type\Factory',
        'Magento\CatalogImportExport\Model\Export\Product\Type\Factory'
    ],
    [
        'Magento\ImportExport\Model\Export\Product\Type\Simple',
        'Magento\CatalogImportExport\Model\Export\Product\Type\Simple'
    ],
    ['Magento\ImportExport\Model\Export\Product', 'Magento\CatalogImportExport\Model\Export\Product'],
    [
        'Magento\ImportExport\Model\Export\RowCustomizer\Composite',
        'Magento\CatalogImportExport\Model\Export\RowCustomizer\Composite'
    ],
    [
        'Magento\ImportExport\Model\Export\RowCustomizerInterface',
        'Magento\CatalogImportExport\Model\Export\RowCustomizerInterface'
    ],
    [
        'Magento\ImportExport\Model\Import\Product\Type\AbstractType',
        'Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType'
    ],
    [
        'Magento\ImportExport\Model\Import\Product\Type\Factory',
        'Magento\CatalogImportExport\Model\Import\Product\Type\Factory'
    ],
    [
        'Magento\ImportExport\Model\Import\Product\Type\Simple',
        'Magento\CatalogImportExport\Model\Import\Product\Type\Simple'
    ],
    [
        'Magento\ImportExport\Model\Import\Product\Option',
        'Magento\CatalogImportExport\Model\Import\Product\Option'
    ],
    ['Magento\ImportExport\Model\Import\Product', 'Magento\CatalogImportExport\Model\Import\Product'],
    [
        'Magento\ImportExport\Model\Import\Proxy\Product',
        'Magento\CatalogImportExport\Model\Import\Proxy\Product'
    ],
    [
        'Magento\ImportExport\Model\Import\Proxy\Product\Resource',
        'Magento\CatalogImportExport\Model\Import\Proxy\Product\Resource'
    ],
    [
        'Magento\ImportExport\Model\Import\Uploader',
        'Magento\CatalogImportExport\Model\Import\Uploader'
    ],
    [
        'Magento\ImportExport\Model\Export\Entity\Customer\Finance',
        'Magento\CustomerFinance\Model\Export\Customer\Finance'
    ],
    [
        'Magento\ImportExport\Model\Import\Entity\Eav\Customer\Finance',
        'Magento\CustomerFinance\Model\Import\Entity\Eav\Customer\Finance'
    ],
    [
        'Magento\ImportExport\Model\Resource\Customer\Attribute\Finance\Collection',
        'Magento\CustomerFinance\Model\Resource\Customer\Attribute\Finance\Collection'
    ],
    [
        'Magento\ImportExport\Model\Resource\Customer\Collection',
        'Magento\CustomerFinance\Model\Resource\Customer\Collection'
    ],
    ['Magento\Profiler', 'Magento\Framework\Profiler'],
    ['Magento\Shell', 'Magento\Framework\Shell'],
    ['Magento\Url', 'Magento\Framework\Url'],
    ['Magento\UrlFactory', 'Magento\Framework\UrlFactory'],
    ['Magento\UrlInterface', 'Magento\Framework\UrlInterface'],
    ['Magento\Validator', 'Magento\Framework\Validator'],
    ['Magento\ValidatorFactory', 'Magento\Framework\ValidatorFactory'],
    ['Magento\Flag', 'Magento\Framework\Flag'],
    ['Magento\FlagFactory', 'Magento\Framework\FlagFactory'],
    ['Magento\Image', 'Magento\Framework\Image'],
    ['Magento\Object', 'Magento\Framework\Object'],
    ['Magento\Currency', 'Magento\Framework\Currency'],
    ['Magento\CurrencyFactory', 'Magento\Framework\CurrencyFactory'],
    ['Magento\CurrencyInterface', 'Magento\Framework\CurrencyInterface'],
    ['Magento\Debug', 'Magento\Framework\Debug'],
    ['Magento\Escaper', 'Magento\Framework\Escaper'],
    ['Magento\OsInfo', 'Magento\Framework\OsInfo'],
    ['Magento\Registry', 'Magento\Framework\Registry'],
    ['Magento\Util', 'Magento\Framework\Util'],
    ['Magento\BootstrapException', 'Magento\Framework\App\InitException'],
    ['Magento\Framework\BootstrapException', 'Magento\Framework\App\InitException'],
    ['Magento\Checkout\Helper\Url'],
    [
        'Magento\Customer\Service\V1\CustomerCurrentService',
        'Magento\Customer\Helper\Session\CurrentCustomer'
    ],
    [
        'Magento\Customer\Service\V1\CustomerCurrentServiceInterface',
        'Magento\Customer\Helper\Session\CurrentCustomer'
    ],
    [
        'Magento\Customer\Service\V1\CustomerAddressCurrentService',
        'Magento\Customer\Helper\Session\CurrentCustomerAddress'
    ],
    [
        'Magento\Customer\Service\V1\CustomerAddressCurrentServiceInterface',
        'Magento\Customer\Helper\Session\CurrentCustomerAddress'
    ],
    [
        'Magento\SalesArchive\Block\Adminhtml\Sales\Order\Grid\Button',
        'Magento\SalesArchive\Block\Adminhtml\Sales\Order\Grid'
    ],
    ['Magento\OfflinePayments\Block\Form\Ccsave'],
    ['Magento\OfflinePayments\Block\Info\Ccsave'],
    ['Magento\OfflinePayments\Model\Ccsave'],
    ['Magento\Sales\Model\Payment\Method\Converter'],
    ['Magento\Payment\Model\Config\Source\Allowedmethods'],
    ['Magento\Paypal\Model\PayflowDirect'],
    ['Magento\Paypal\Block\Adminhtml\System\Config\Fieldset\Store'],
    ['Magento\Framework\View\Url', 'Magento\Framework\View\Asset\Repository'],
    ['Magento\Less\File\Source\Base', 'Magento\Framework\View\File\Collector\Base'],
    ['Magento\Less\File\Source\Theme', 'Magento\Framework\View\File\Collector\ThemeModular'],
    [
        'Magento\Framework\View\Layout\File\FileList\CollateInterface',
        'Magento\Framework\View\File\FileList\CollateInterface'
    ],
    ['Magento\Framework\View\Layout\File\FileList\Collator', 'Magento\Framework\View\File\FileList\Collator'],
    ['Magento\Framework\View\Layout\File\FileList\Factory', 'Magento\Framework\View\File\FileList\Factory'],
    [
        'Magento\Framework\View\Layout\File\Source\Decorator\ModuleDependency',
        'Magento\Framework\View\File\Collector\Decorator\ModuleDependency'
    ],
    [
        'Magento\Framework\View\Layout\File\Source\Decorator\ModuleOutput',
        'Magento\Framework\View\File\Collector\Decorator\ModuleOutput'
    ],
    ['Magento\Framework\View\Layout\File\Source\Override\Base', 'Magento\Framework\View\File\Collector\Override\Base'],
    [
        'Magento\Framework\View\Layout\File\Source\Override\Theme',
        'Magento\Framework\View\File\Collector\Override\ThemeModular'
    ],
    ['Magento\Framework\View\Layout\File\Source\Base', 'Magento\Framework\View\File\Collector\Base'],
    ['Magento\Framework\View\Layout\File\Source\Theme', 'Magento\Framework\View\File\Collector\ThemeModular'],
    ['Magento\Framework\View\Layout\File\Factory', 'Magento\Framework\View\File\Factory'],
    ['Magento\Framework\View\Layout\File\FileList', 'Magento\Framework\View\File\FileList'],
    ['Magento\Framework\View\Layout\File\SourceInterface', 'Magento\Framework\View\File\CollectorInterface'],
    ['Magento\Framework\View\Layout\File', 'Magento\Framework\View\File'],
    ['Magento\Framework\View\Url\Resolver', 'Magento\Framework\View\Asset\Repository'],
    [
        'Magento\DesignEditor\Block\Adminhtml\Editor\Tools\Code\Css\Group',
        'Magento\DesignEditor\Block\Adminhtml\Editor\Tools\Code\Css'
    ],
    ['Magento\Framework\Filter\GridArray\Grid'],
    ['Magento\Css\PreProcessor\Composite'],
    ['Magento\Css\PreProcessor\UrlResolver', 'Magento\Framework\View\Asset\PreProcessor\ModuleNotation'],
    ['Magento\Less\PreProcessor\File\FileList'],
    ['Magento\Less\PreProcessor\File\FileListFactory'],
    ['Magento\Less\PreProcessor\File\Less', 'Magento\Framework\View\Asset\File'],
    ['Magento\Less\PreProcessor\File\LessFactory'],
    ['Magento\Less\PreProcessor\InstructionFactory'],
    ['Magento\Less\PreProcessor', 'Magento\Framework\Less\FileGenerator'],
    ['Magento\Less\PreProcessorInterface', 'Magento\Framework\View\Asset\PreProcessorInterface'],
    ['Magento\Framework\View\Asset\PreProcessorFactory'],
    ['Magento\Framework\View\Asset\PreProcessor\Composite'],
    [
        'Magento\Framework\View\Asset\PreProcessor\PreProcessorInterface',
        'Magento\Framework\View\Asset\PreProcessorInterface'
    ],
    ['Magento\Framework\View\Publisher', '\Magento\Framework\App\View\Asset\Publisher'],
    ['Magento\Framework\View\Publisher\FileAbstract'],
    ['Magento\Framework\View\Publisher\File'],
    ['Magento\Framework\View\Publisher\FileFactory'],
    ['Magento\Framework\View\Publisher\CssFile'],
    ['Magento\Framework\View\RelatedFile'],
    ['Magento\Css\PreProcessor\Cache\Plugin\Less', 'Magento\Framework\View\Asset\PreProcessing\Cache'],
    ['Magento\Css\PreProcessor\Cache\Import\Cache'],
    ['Magento\Css\PreProcessor\Cache\Plugin\ImportCleaner'],
    ['Magento\Css\PreProcessor\Cache\Import\Map\Storage', 'Magento\Framework\View\Asset\PreProcessing\Cache'],
    ['Magento\Css\PreProcessor\Cache\Import\ImportEntity'],
    ['Magento\Css\PreProcessor\Cache\Import\ImportEntityFactory'],
    ['Magento\Css\PreProcessor\Cache\Import\ImportEntityInterface'],
    ['Magento\Css\PreProcessor\Cache\CacheFactory'],
    ['Magento\Css\PreProcessor\Cache\CacheInterface'],
    ['Magento\Css\PreProcessor\Cache\CacheManager'],
    ['Magento\Framework\View\Design\FileResolution\Strategy\ViewInterface'],
    [
        'Magento\ImportExport\Model\Import\Entity\Product',
        'Magento\CatalogImportExport\Model\Import\Product'
    ],
    [
        'Magento\ImportExport\Model\Import\Entity\Product\Option',
        'Magento\CatalogImportExport\Model\Import\Product\Option'
    ],
    [
        'Magento\ImportExport\Model\Import\Entity\Product\Type\AbstractType',
        'Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType'
    ],
    [
        'Magento\ImportExport\Model\Import\Entity\Product\Type\Factory',
        'Magento\CatalogImportExport\Model\Import\Product\Type\Factory'
    ],
    [
        'Magento\ImportExport\Model\Import\Entity\Product\Type\Simple',
        'Magento\CatalogImportExport\Model\Import\Product\Type\Simple'
    ],
    [
        'Magento\ImportExport\Model\Export\Entity\Product',
        'Magento\CatalogImportExport\Model\Export\Product'
    ],
    [
        'Magento\ImportExport\Model\Export\Entity\Product\Type\AbstractType',
        'Magento\CatalogImportExport\Model\Export\Product\Type\AbstractType'
    ],
    [
        'Magento\ImportExport\Model\Export\Entity\Product\Type\Factory',
        'Magento\CatalogImportExport\Model\Export\Product\Type\Factory'
    ],
    [
        'Magento\ImportExport\Model\Export\Entity\Product\Type\Simple',
        'Magento\CatalogImportExport\Model\Export\Product\Type\Simple'
    ],
    [
        'Magento\Bundle\Pricing\Price\BasePrice',
        'Magento\Catalog\Pricing\Price\BasePrice'
    ],
    ['Magento\Cataloginventory\Model\Resource\Indexer\Stock'],
    ['Magento\Catalog\Model\Product\Indexer\Eav'],
    ['Magento\Bundle\Pricing\Price\BasePriceInterface'],
    ['Magento\Banner\Helper\Data'],
    ['Magento\Cms\Helper\Data'],
    ['Magento\Cron\Helper\Data'],
    ['Magento\Email\Helper\Data'],
    ['Magento\GiftMessage\Helper\Data'],
    ['Magento\Index\Helper\Data'],
    ['Magento\Install\Helper\Data'],
    ['Magento\Log\Helper\Data'],
    ['Magento\Ogone\Helper\Data'],
    ['Magento\Rule\Helper\Data'],
    ['Magento\Theme\Helper\Data'],
    ['Magento\Widget\Helper\Data'],
    ['Magento\Tax\Model\Resource\Calculation\Grid\Collection'],
    ['Magento\Tax\Model\Resource\Rule\Grid\Collection'],
    ['Magento\Tax\Model\Resource\Rule\Grid\Options\CustomerTaxClass'],
    ['Magento\Tax\Model\Resource\Rule\Grid\Options\HashOptimized'],
    ['Magento\Tax\Model\Resource\Rule\Grid\Options\ProductTaxClass'],
    ['Magento\SalesArchive\Block\Adminhtml\Sales\Order\Grid\Massaction'],
    ['Magento\Framework\System\Args'],
    ['Magento\Framework\Autoload\Simple'],
    ['Magento\Catalog\Helper\Product\Price'],
    ['Magento\Tax\Model\Config\Source\TaxClass\Product', 'Magento\Tax\Model\TaxClass\Source\Product'],
    ['Magento\Tax\Model\Config\Source\TaxClass\Customer', 'Magento\Tax\Model\TaxClass\Source\Customer'],
    ['Magento\AdminNotification\Model\System\MessageInterface', 'Magento\Framework\Notification\MessageInterface'],
    ['Magento\AdminNotification\Model\System\MessageList', 'Magento\Framework\Notification\MessageList'],
    [
        'Magento\CatalogImportExport\Model\Import\Product\Type\Configurable',
        'Magento\ConfigurableImportExport\Model\Import\Product\Type\Configurable'
    ],
    [
        'Magento\CatalogImportExport\Model\Export\Product\Type\Configurable',
        'Magento\ConfigurableImportExport\Model\Export\Product\Type\Configurable'
    ],
    [
        'Magento\CatalogImportExport\Model\Export\RowCustomizer',
        'Magento\ConfigurableImportExport\Model\Export\RowCustomizer'
    ],
    [
        'Magento\CatalogImportExport\Model\Export\Product\Type\Grouped',
        'Magento\GroupedImportExport\Model\Export\Product\Type\Grouped'
    ],
    [
        'Magento\CatalogImportExport\Model\Import\Product\Type\Grouped',
        'Magento\GroupedImportExport\Model\Import\Product\Type\Grouped'
    ],
    ['Magento\Catalog\Model\Observer\Reindex'],
    ['Magento\CatalogSearch\Model\Fulltext\Observer'],
    ['Magento\CatalogSearch\Model\Resource\Indexer\Fulltext'],
    [
        'Magento\Tax\Block\Adminhtml\Rate\Grid\Renderer\Country',
        'Magento\TaxImportExport\Block\Adminhtml\Rate\Grid\Renderer\Country'
    ],
    ['Magento\Tax\Block\Adminhtml\Rate\ImportExport', 'Magento\TaxImportExport\Block\Adminhtml\Rate\ImportExport'],
    [
        'Magento\Tax\Block\Adminhtml\Rate\ImportExportHeader',
        'Magento\TaxImportExport\Block\Adminhtml\Rate\ImportExportHeader'
    ],
    ['Magento\Tax\Controller\Adminhtml\Rate\ExportCsv', 'Magento\TaxImportExport\Controller\Adminhtml\Rate\ExportCsv'],
    [
        'Magento\Tax\Controller\Adminhtml\Rate\ExportPost',
        'Magento\TaxImportExport\Controller\Adminhtml\Rate\ExportPost'
    ],
    ['Magento\Tax\Controller\Adminhtml\Rate\ExportXml', 'Magento\TaxImportExport\Controller\Adminhtml\Rate\ExportXml'],
    [
        'Magento\Tax\Controller\Adminhtml\Rate\ImportExport',
        'Magento\TaxImportExport\Controller\Adminhtml\Rate\ImportExport'
    ],
    [
        'Magento\Tax\Controller\Adminhtml\Rate\ImportPost',
        'Magento\TaxImportExport\Controller\Adminhtml\Rate\ImportPost'
    ],
    ['Magento\Tax\Model\Rate\CsvImportHandler', 'Magento\TaxImportExport\Model\Rate\CsvImportHandler'],
    ['\Magento\Theme\Helper\Layout'],
    ['Magento\Framework\Stdlib\Cookie', 'Magento\Framework\Stdlib\CookieManager'],
    ['Magento\Framework\View\Design\Theme\Provider'],
    ['Magento\Install\Controller\Index'],
    ['Magento\Install\Controller\Wizard'],
    ['Magento\Install\Controller\Wizard\Administrator'],
    ['Magento\Install\Controller\Wizard\AdministratorPost'],
    ['Magento\Install\Controller\Wizard\Begin'],
    ['Magento\Install\Controller\Wizard\BeginPost'],
    ['Magento\Install\Controller\Wizard\Config'],
    ['Magento\Install\Controller\Wizard\ConfigPost'],
    ['Magento\Install\Controller\Wizard\Download'],
    ['Magento\Install\Controller\Wizard\DownloadAuto'],
    ['Magento\Install\Controller\Wizard\DownloadManual'],
    ['Magento\Install\Controller\Wizard\DownloadPost'],
    ['Magento\Install\Controller\Wizard\End'],
    ['Magento\Install\Controller\Wizard\Index'],
    ['Magento\Install\Controller\Wizard\Install'],
    ['Magento\Install\Controller\Wizard\InstallDb'],
    ['Magento\Install\Controller\Wizard\Locale'],
    ['Magento\Install\Controller\Wizard\LocaleChange'],
    ['Magento\Install\Controller\Wizard\LocalePost'],
    ['Magento\Install\App\Action\Plugin\Dir'],
    ['\Magento\Framework\App\EntryPoint\EntryPoint', '\Magento\Framework\App\Bootstrap'],
    ['\Magento\Framework\App\EntryPointInterface', '\Magento\Framework\App\Bootstrap'],
    ['Magento\Framework\Module\FrontController\Plugin\Install', '\Magento\Framework\Module\Plugin\DbStatusValidator'],
    ['Magento\Framework\Module\UpdaterInterface'],
    ['Magento\Framework\App\EntryPoint\EntryPoint', 'Magento\Framework\App\Bootstrap'],
    ['Magento\Framework\App\EntryPointInterface', 'Magento\Framework\App\Bootstrap'],
    ['Magento\Install\Model\Installer\AbstractInstaller', 'Magento\Install\Model\Installer\Console'],
    ['Magento\Install\App\Action\Plugin\Install', 'Magento\Framework\App\Bootstrap'],
    ['\Magento\Cron\App\Cron\Plugin\ApplicationInitializer', 'Magento\Framework\App\Bootstrap'],
    ['Magento\Framework\App\Error\Handler', 'Magento\Framework\App\Http'],
    ['Magento\Framework\App\State\MaintenanceMode', 'Magento\Framework\App\MaintenanceMode'],
    ['Magento\Framework\Error\Handler', 'Magento\Framework\App\ErrorHandler'],
    ['Magento\Framework\Error\HandlerInterface', 'Magento\Framework\App\ErrorHandler'],
    ['Magento\Index'],
    ['Magento\Catalog\Model\Resource\Product\Indexer\Eav'],
    ['\Magento\Framework\Service\Data\Eav\AbstractObject', 'Magento\Framework\Service\Data\AbstractExtensibleObject'],
    ['\Magento\Framework\Service\Data\AbstractObject', 'Magento\Framework\Service\Data\AbstractSimpleObject'],
    [
        '\Magento\Framework\Service\Data\Eav\AbstractObjectBuilder',
        'Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder'
    ],
    [
        '\Magento\Framework\Service\Data\AbstractObjectBuilder',
        'Magento\Framework\Service\Data\AbstractSimpleObjectBuilder'
    ],
    ['Magento\Catalog\Block\Product'],
    ['\Magento\Sales\Model\Observer'],
    ['\Magento\Checkout\Service\V1\QuoteLoader', '\Magento\Sales\Model\QuoteRepository'],
    ['Magento\PageCache\Model\Observer'],
    ['Magento\Rss\Block\Order\Info\Buttons\Rss'],
    ['Magento\Rss\Block\Order\NewOrder'],
    ['Magento\Rss\Block\Order\Status'],
    ['Magento\Rss\Controller\Adminhtml\Order\NewAction'],
    ['Magento\Rss\Controller\Order\Status'],
    ['Magento\Rss\Helper\Order'],
    ['Magento\Rss\Block\Order\Details', 'Magento\Sales\Block\Order\Details'],
    ['Magento\Rss\Model\Resource\Order', 'Magento\Sales\Model\Resource\Order\Rss\OrderStatus'],
    ['Magento\Rss\Block\Catalog\AbstractCatalog'],
    ['Magento\Rss\Block\Catalog\NewCatalog'],
    ['Magento\Rss\Block\Catalog\Review'],
    ['Magento\Rss\Block\AbstractBlock'],
    ['Magento\Rss\Block\ListBlock'],
    ['Magento\Rss\Controller\Adminhtml\Catalog\Notifystock'],
    ['Magento\Rss\Controller\Adminhtml\Catalog\Review'],
    ['Magento\Rss\Controller\Catalog\Category'],
    ['Magento\Rss\Controller\Catalog\NewAction'],
    ['Magento\Rss\Controller\Catalog\Salesrule'],
    ['Magento\Rss\Controller\Catalog\Special'],
    ['Magento\Rss\Controller\Index\Nofeed'],
    ['Magento\Rss\Controller\Catalog'],
    ['Magento\Wishlist\Block\Rss'],
    ['Magento\Wishlist\Controller\Index\Rss'],
);
