<?php
/**
 * Obsolete classes
 *
 * Format: array(<class_name>[, <replacement>])
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
return [
    ['Mage_Admin_Helper_Data', 'Magento\Backend\Helper\Data'],
    ['Mage_Admin_Model_Acl', 'Magento_Acl'],
    ['Mage_Admin_Model_Acl_Role'],
    ['Mage_Admin_Model_Acl_Resource', 'Magento\Framework\Acl\Resource'],
    ['Mage_Admin_Model_Acl_Role_Registry', 'Magento\Framework\Acl\Role\Registry'],
    ['Mage_Admin_Model_Acl_Role_Generic', 'Magento\Authorization\Model\Acl\Role\Generic'],
    ['Mage_Admin_Model_Acl_Role_Group', 'Magento\Authorization\Model\Acl\Role\Group'],
    ['Mage_Admin_Model_Acl_Role_User', 'Magento\Authorization\Model\Acl\Role\User'],
    ['Mage_Admin_Model_Resource_Acl', 'Magento\User\Model\Resource\Acl'],
    ['Mage_Admin_Model_Observer'],
    ['Mage_Admin_Model_Session', 'Magento\Backend\Model\Auth\Session'],
    ['Mage_Admin_Model_Resource_Acl_Role'],
    ['Mage_Admin_Model_Resource_Acl_Role_Collection'],
    ['Mage_Admin_Model_User', 'Magento\User\Model\User'],
    ['Mage_Admin_Model_Config'],
    ['Mage_Admin_Model_Resource_User', 'Magento\User\Model\Resource\User'],
    ['Mage_Admin_Model_Resource_User_Collection', 'Magento\User\Model\Resource\User\Collection'],
    ['Mage_Admin_Model_Role', 'Magento\Authorization\Model\Role'],
    ['Mage_Admin_Model_Roles', 'Magento\Authorization\Model\Roles'],
    ['Mage_Admin_Model_Rules', 'Magento\Authorization\Model\Rules'],
    ['Mage_Admin_Model_Resource_Role', 'Magento\Authorization\Model\Resource\Role'],
    ['Mage_Admin_Model_Resource_Roles', 'Magento\User\Model\Resource\Roles'],
    ['Mage_Admin_Model_Resource_Rules', 'Magento\Authorization\Model\Resource\Rules'],
    ['Mage_Admin_Model_Resource_Role_Collection', 'Magento\Authorization\Model\Resource\Role\Collection'],
    ['Mage_Admin_Model_Resource_Roles_Collection', 'Magento\User\Model\Resource\Roles\Collection'],
    ['Mage_Admin_Model_Resource_Roles_User_Collection', 'Magento\User\Model\Resource\Roles\User\Collection'],
    ['Mage_Admin_Model_Resource_Rules_Collection', 'Magento\Authorization\Model\Resource\Rules\Collection'],
    [
        'Mage_Admin_Model_Resource_Permissions_Collection',
        'Magento\Authorization\Model\Resource\Permissions\Collection'
    ],
    ['Mage_Adminhtml_Block_Abstract', 'Magento\Backend\Block\AbstractBlock'],
    ['Mage_Adminhtml_Block_Backup_Grid'],
    ['Mage_Adminhtml_Block_Cache_Grid'],
    ['Mage_Adminhtml_Block_Catalog'],
    ['Mage_Adminhtml_Block_Catalog_Product_Attribute_Set_Grid'],
    ['Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Config_Grid'],
    ['Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Group_Grid'],
    ['Mage_Adminhtml_Block_Catalog_Search_Grid'],
    ['Mage_Adminhtml_Block_Cms_Block_Grid'],
    ['Mage_Adminhtml_Block_Customer_Group_Grid'],
    ['Mage_Adminhtml_Block_Customer_Online_Grid'],
    ['Mage_Adminhtml_Block_Newsletter_Problem_Grid'],
    ['Mage_Adminhtml_Block_Newsletter_Queue'],
    ['Mage_Adminhtml_Block_Newsletter_Queue_Grid'],
    ['Mage_Adminhtml_Block_Page_Menu', 'Magento\Backend\Block\Menu'],
    ['Mage_Adminhtml_Block_Permissions_User'],
    ['Mage_Adminhtml_Block_Permissions_User_Grid'],
    ['Mage_Adminhtml_Block_Permissions_User_Edit'],
    ['Mage_Adminhtml_Block_Permissions_User_Edit_Tabs'],
    ['Mage_Adminhtml_Block_Permissions_User_Edit_Tab_Main'],
    ['Mage_Adminhtml_Block_Permissions_User_Edit_Tab_Roles'],
    ['Mage_Adminhtml_Block_Permissions_User_Edit_Form'],
    ['Mage_Adminhtml_Block_Permissions_Role'],
    ['Mage_Adminhtml_Block_Permissions_Buttons'],
    ['Mage_Adminhtml_Block_Permissions_Role_Grid_User'],
    ['Mage_Adminhtml_Block_Permissions_Grid_Role'],
    ['Mage_Adminhtml_Block_Permissions_Grid_User'],
    ['Mage_Adminhtml_Block_Permissions_Tab_Roleinfo'],
    ['Mage_Adminhtml_Block_Permissions_Tab_Rolesedit'],
    ['Mage_Adminhtml_Block_Permissions_Tab_Rolesusers'],
    ['Mage_Adminhtml_Block_Permissions_Tab_Useredit'],
    ['Mage_Adminhtml_Block_Permissions_Editroles'],
    ['Mage_Adminhtml_Block_Permissions_Roles'],
    ['Mage_Adminhtml_Block_Permissions_Users'],
    ['Mage_Adminhtml_Block_Permissions_Edituser'],
    ['Mage_Adminhtml_Block_Permissions_Tab_Userroles'],
    ['Mage_Adminhtml_Block_Permissions_Usernroles'],
    ['Mage_Adminhtml_Block_Promo_Catalog_Grid'],
    ['Mage_Adminhtml_Block_Promo_Quote_Grid'],
    ['Mage_Adminhtml_Block_Rating_Grid'],
    ['Mage_Adminhtml_Block_System_Store_Grid'],
    ['Mage_Adminhtml_Permissions_UserController'],
    ['Mage_Adminhtml_Permissions_RoleController'],
    ['Mage_Adminhtml_Block_Report_Grid', 'Magento\Reports\Block\Adminhtml\Grid'],
    ['Mage_Adminhtml_Block_Report_Customer_Accounts', 'Magento\Reports\Block\Adminhtml\Customer\Accounts'],
    ['Mage_Adminhtml_Block_Report_Customer_Accounts_Grid'],
    ['Mage_Adminhtml_Block_Report_Customer_Totals', 'Magento\Reports\Block\Adminhtml\Customer\Totals'],
    ['Mage_Adminhtml_Block_Report_Customer_Totals_Grid'],
    ['Mage_Adminhtml_Block_Report_Product_Sold', 'Magento\Reports\Block\Adminhtml\Product\Sold'],
    ['Mage_Adminhtml_Block_Report_Product_Sold_Grid'],
    ['Mage_Adminhtml_Block_Report_Review_Customer_Grid'],
    ['Mage_Adminhtml_Block_Report_Customer_Orders', 'Magento\Reports\Block\Adminhtml\Customer\Orders'],
    ['Mage_Adminhtml_Block_Report_Customer_Orders_Grid'],
    ['Mage_Adminhtml_Block_Report_Product_Ordered'],
    ['Mage_Adminhtml_Block_Report_Product_Ordered_Grid'],
    ['Mage_Adminhtml_Block_Report_Review_Product_Grid'],
    ['Mage_Adminhtml_Block_Report_Refresh_Statistics', 'Magento\Reports\Block\Adminhtml\Refresh\Statistics'],
    ['Mage_Adminhtml_Block_Report_Refresh_Statistics_Grid'],
    ['Mage_Adminhtml_Block_Report_Search_Grid'],
    ['Mage_Adminhtml_Block_Sales'],
    ['Magento\GoogleCheckout'], // removed module
    ['Magento\Sales\Block\Adminhtml\Order\Shipment\Create\Form', 'Magento\Shipping\Block\Adminhtml\Create\Form'],
    [
        'Magento\Sales\Block\Adminhtml\Order\Shipment\Create\Items',
        'Magento\Shipping\Block\Adminhtml\Create\Items'
    ],
    [
        'Magento\Sales\Block\Adminhtml\Order\Shipment\View\Comments',
        'Magento\Shipping\Block\Adminhtml\View\Comments'
    ],
    ['Magento\Sales\Block\Adminhtml\Order\Shipment\View\Form', 'Magento\Shipping\Block\Adminhtml\View\Form'],
    ['Magento\Sales\Block\Adminhtml\Order\Shipment\View\Items', 'Magento\Shipping\Block\Adminhtml\View\Items'],
    ['Magento\Sales\Block\Adminhtml\Order\Shipment\Create', 'Magento\Shipping\Block\Adminhtml\Create'],
    ['Magento\Sales\Block\Adminhtml\Order\Shipment\View', 'Magento\Shipping\Block\Adminhtml\View'],
    ['Magento\Sales\Block\Order\Shipment\Items', 'Magento\Shipping\Block\Items'],
    ['Magento\Sales\Controller\Adminhtml\Order\Shipment', 'Magento\Shipping\Controller\Adminhtml\Order\Shipment'],
    ['Magento\Sales\Block\Order\Shipment', 'Magento\Shipping\Block\Order\Shipment'],
    ['Mage_Adminhtml_Block_Sales_Order_Create_Customer_Grid'],
    ['Mage_Adminhtml_Block_Sales_Order_Create_Search_Grid_Renderer_Giftmessage'],
    ['Mage_Adminhtml_Block_Sales_Order_Status_Grid'],
    ['Mage_Adminhtml_Block_Sitemap_Grid'],
    ['Mage_Adminhtml_Block_System_Config_Edit', 'Magento\Backend\Block\System\Config\Edit'],
    ['Mage_Adminhtml_Block_System_Config_Form', 'Magento\Backend\Block\System\Config\Form'],
    ['Mage_Adminhtml_Block_System_Config_Tabs', 'Magento\Backend\Block\System\Config\Tabs'],
    [
        'Mage_Adminhtml_Block_System_Config_System_Storage_Media_Synchronize',
        'Magento\Backend\Block\System\Config\System\Storage\Media\Synchronize'
    ],
    [
        'Mage_Adminhtml_Block_System_Config_Form_Fieldset_Modules_DisableOutput',
        'Magento\Backend\Block\System\Config\Form\Fieldset\Modules\DisableOutput'
    ],
    [
        'Mage_Adminhtml_Block_System_Config_Form_Field_Regexceptions',
        'Magento\Backend\Block\System\Config\Form\Field\Regexceptions'
    ],
    [
        'Mage_Adminhtml_Block_System_Config_Form_Field_Notification',
        'Magento\Backend\Block\System\Config\Form\Field\Notification'
    ],
    [
        'Mage_Adminhtml_Block_System_Config_Form_Field_Heading',
        'Magento\Backend\Block\System\Config\Form\Field\Heading'
    ],
    [
        'Mage_Adminhtml_Block_System_Config_Form_Field_Datetime',
        'Magento\Backend\Block\System\Config\Form\Field\Datetime'
    ],
    [
        'Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract',
        'Magento\Backend\Block\System\Config\Form\Field\Array\AbstractArray'
    ],
    ['Mage_Adminhtml_Block_System_Config_Form_Fieldset', 'Magento\Backend\Block\System\Config\Form\Fieldset'],
    ['Mage_Adminhtml_Block_System_Config_Form_Field', 'Magento\Backend\Block\System\Config\Form\Field'],
    [
        'Mage_Adminhtml_Block_System_Config_Form_Field_Import',
        'Magento\Backend\Block\System\Config\Form\Field\Import'
    ],
    [
        'Mage_Adminhtml_Block_System_Config_Form_Field_Image',
        'Magento\Backend\Block\System\Config\Form\Field\Image'
    ],
    [
        'Mage_Adminhtml_Block_System_Config_Form_Field_Export',
        'Magento\Backend\Block\System\Config\Form\Field\Export'
    ],
    [
        'Mage_Adminhtml_Block_System_Config_Form_Field_Select_Allowspecific',
        'Magento\Backend\Block\System\Config\Form\Field\Select\Allowspecific'
    ],
    ['Mage_Adminhtml_Block_System_Config_Form_Field_File', 'Magento\Backend\Block\System\Config\Form\Field\File'],
    [
        'Mage_Adminhtml_Block_System_Config_Form_Field_Select_Flatproduct',
        'Magento\Catalog\Block\Adminhtml\System\Config\Form\Field\Select\Flatproduct'
    ],
    [
        'Mage_Adminhtml_Block_System_Config_Form_Field_Select_Flatcatalog',
        'Magento\Catalog\Block\Adminhtml\System\Config\Form\Field\Select\Flatcatalog'
    ],
    [
        'Mage_Adminhtml_Block_System_Config_Form_Fieldset_Order_Statuses',
        'Magento\Sales\Block\Adminhtml\System\Config\Form\Fieldset\Order\Statuses'
    ],
    ['Mage_Adminhtml_Block_System_Config_Dwstree', 'Magento\Backend\Block\System\Config\Dwstree'],
    ['Mage_Adminhtml_Block_System_Config_Switcher', 'Magento\Backend\Block\System\Config\Switcher'],
    ['Mage_Adminhtml_Block_System_Design_Grid'],
    ['Magento\Adminhtml\Block\System\Email\Template', 'Magento\Email\Block\Adminhtml\Template'],
    ['Magento\Adminhtml\Block\System\Email\Template\Edit', 'Magento\Email\Block\Adminhtml\Template\Edit'],
    [
        'Magento\Adminhtml\Block\System\Email\Template\Edit\Form',
        'Magento\Email\Block\Adminhtml\Template\Edit\Form'
    ],
    ['Magento\Adminhtml\Block\System\Email\Template\Preview', 'Magento\Email\Block\Adminhtml\Template\Preview'],
    ['Mage_Adminhtml_Block_System_Email_Template_Grid'],
    [
        'Magento\Adminhtml\Block\System\Email\Template\Grid\Renderer\Action',
        'Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Action'
    ],
    [
        'Magento\Adminhtml\Block\System\Email\Template\Grid\Renderer\Sender',
        'Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Sender'
    ],
    [
        'Magento\Adminhtml\Block\System\Email\Template\Grid\Renderer\Type',
        'Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Type'
    ],
    [
        'Magento\Adminhtml\Block\System\Email\Template\Grid\Filter\Type',
        'Magento\Email\Block\Adminhtml\Template\Grid\Filter\Type'
    ],
    ['Mage_Adminhtml_Block_System_Variable_Grid'],
    ['Mage_Adminhtml_Block_Store_Switcher', 'Magento\Backend\Block\Store\Switcher'],
    [
        'Mage_Adminhtml_Block_Store_Switcher_Form_Renderer_Fieldset',
        'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset'
    ],
    [
        'Mage_Adminhtml_Block_Store_Switcher_Form_Renderer_Fieldset_Element',
        'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
    ],
    ['Mage_Adminhtml_Block_Tag_Tag_Edit'],
    ['Mage_Adminhtml_Block_Tag_Tag_Edit_Form'],
    ['Mage_Adminhtml_Block_Tax_Rate_Grid'],
    ['Mage_Adminhtml_Block_Tax_Rule_Grid'],
    ['Mage_Adminhtml_Block_Tree'],
    ['Mage_Adminhtml_Block_Urlrewrite_Grid'],
    ['Magento\Adminhtml\Controller\System\Email\Template', 'Magento\Email\Controller\Adminhtml\Template'],
    ['Mage_Adminhtml_Helper_Rss'],
    ['Mage_Adminhtml_Model_Config', 'Magento\Backend\Model\Config\Structure'],
    ['Mage_Adminhtml_Model_Config_Data', 'Magento\Backend\Model\Config'],
    ['Magento\Adminhtml\Model\Email\Template', 'Magento\Email\Model\Adminhtml\Template'],
    ['Mage_Adminhtml_Model_Extension'],
    ['Mage_Adminhtml_Model_System_Config_Source_Shipping_Allowedmethods'],
    [
        'Mage_Adminhtml_Model_System_Config_Backend_Admin_Password_Link_Expirationperiod',
        'Magento\Backend\Model\Config\Backend\Admin\Password\Link\Expirationperiod'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Backend_Admin_Custom',
        'Magento\Backend\Model\Config\Backend\Admin\Custom'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Backend_Admin_Custompath',
        'Magento\Backend\Model\Config\Backend\Admin\Custompath'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Backend_Admin_Observer',
        'Magento\Backend\Model\Config\Backend\Admin\Observer'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Backend_Admin_Robots',
        'Magento\Backend\Model\Config\Backend\Admin\Robots'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Backend_Admin_Usecustom',
        'Magento\Backend\Model\Config\Backend\Admin\Usecustom'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Backend_Admin_Usecustompath',
        'Magento\Backend\Model\Config\Backend\Admin\Custompath'
    ],
    [
        'Magento\Backend\Model\Config\Backend\Admin\Usecustompath',
        'Magento\Backend\Model\Config\Backend\Admin\Custompath'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Backend_Admin_Usesecretkey',
        'Magento\Backend\Model\Config\Backend\Admin\Usesecretkey'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Backend_Catalog_Inventory_Managestock',
        'Magento\CatalogInventory\Model\Config\Backend\Managestock'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Backend_Catalog_Search_Type',
        'Magento\CatalogSearch\Model\Config\Backend\Search\Type'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Backend_Currency_Abstract',
        'Magento\Backend\Model\Config\Backend\Currency\AbstractCurrency'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Backend_Currency_Allow',
        'Magento\Backend\Model\Config\Backend\Currency\Allow'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Backend_Currency_Base',
        'Magento\Backend\Model\Config\Backend\Currency\Base'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Backend_Currency_Cron',
        'Magento\Backend\Model\Config\Backend\Currency\Cron'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Backend_Currency_Default',
        'Magento\Backend\Model\Config\Backend\Currency\DefaultCurrency'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Backend_Customer_Address_Street',
        'Magento\Customer\Model\Config\Backend\Address\Street'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Backend_Customer_Password_Link_Expirationperiod',
        'Magento\Customer\Model\Config\Backend\Password\Link\Expirationperiod'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Backend_Customer_Show_Address',
        'Magento\Customer\Model\Config\Backend\Show\Address'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Backend_Customer_Show_Customer',
        'Magento\Customer\Model\Config\Backend\Show\Customer'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Backend_Design_Exception',
        'Magento\Backend\Model\Config\Backend\Design\Exception'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Backend_Email_Address',
        'Magento\Backend\Model\Config\Backend\Email\Address'
    ],
    ['Mage_Adminhtml_Model_System_Config_Backend_Email_Logo', 'Magento\Backend\Model\Config\Backend\Email\Logo'],
    [
        'Mage_Adminhtml_Model_System_Config_Backend_Email_Sender',
        'Magento\Backend\Model\Config\Backend\Email\Sender'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Backend_Image_Adapter',
        'Magento\Backend\Model\Config\Backend\Image\Adapter'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Backend_Image_Favicon',
        'Magento\Backend\Model\Config\Backend\Image\Favicon'
    ],
    ['Mage_Adminhtml_Model_System_Config_Backend_Image_Pdf', 'Magento\Backend\Model\Config\Backend\Image\Pdf'],
    [
        'Mage_Adminhtml_Model_System_Config_Backend_Locale_Timezone',
        'Magento\Backend\Model\Config\Backend\Locale\Timezone'
    ],
    ['Mage_Adminhtml_Model_System_Config_Backend_Log_Cron', 'Magento\Backend\Model\Config\Backend\Log\Cron'],
    ['Mage_Adminhtml_Model_System_Config_Backend_Price_Scope'],
    [
        'Mage_Adminhtml_Model_System_Config_Backend_Product_Alert_Cron',
        'Magento\Cron\Model\Config\Backend\Product\Alert'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Backend_Seo_Product',
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Backend_Serialized_Array',
        'Magento\Backend\Model\Config\Backend\Serialized\Array'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Backend_Shipping_Tablerate',
        'Magento\OfflineShipping\Model\Config\Backend\Tablerate'
    ],
    ['Mage_Adminhtml_Model_System_Config_Backend_Sitemap_Cron', 'Magento\Cron\Model\Config\Backend\Sitemap'],
    [
        'Mage_Adminhtml_Model_System_Config_Backend_Storage_Media_Database',
        'Magento\Backend\Model\Config\Backend\Storage\Media\Database'
    ],
    ['Mage_Adminhtml_Model_System_Config_Backend_Baseurl', 'Magento\Backend\Model\Config\Backend\Baseurl'],
    ['Mage_Adminhtml_Model_System_Config_Backend_Cache', 'Magento\Backend\Model\Config\Backend\Cache'],
    ['Mage_Adminhtml_Model_System_Config_Backend_Category', 'Magento\Catalog\Model\Config\Backend\Category'],
    ['Mage_Adminhtml_Model_System_Config_Backend_Cookie', 'Magento\Backend\Model\Config\Backend\Cookie'],
    ['Mage_Adminhtml_Model_System_Config_Backend_Datashare', 'Magento\Backend\Model\Config\Backend\Datashare'],
    ['Mage_Adminhtml_Model_System_Config_Backend_Encrypted', 'Magento\Backend\Model\Config\Backend\Encrypted'],
    ['Mage_Adminhtml_Model_System_Config_Backend_File', 'Magento\Backend\Model\Config\Backend\File'],
    ['Mage_Adminhtml_Model_System_Config_Backend_Filename', 'Magento\Backend\Model\Config\Backend\Filename'],
    ['Mage_Adminhtml_Model_System_Config_Backend_Image', 'Magento\Backend\Model\Config\Backend\Image'],
    ['Mage_Adminhtml_Model_System_Config_Backend_Locale', 'Magento\Backend\Model\Config\Backend\Locale'],
    ['Mage_Adminhtml_Model_System_Config_Backend_Secure', 'Magento\Backend\Model\Config\Backend\Secure'],
    ['Mage_Adminhtml_Model_System_Config_Backend_Serialized', 'Magento\Backend\Model\Config\Backend\Serialized'],
    ['Mage_Adminhtml_Model_System_Config_Backend_Sitemap', 'Magento\Sitemap\Model\Config\Backend\Priority'],
    ['Mage_Adminhtml_Model_System_Config_Backend_Store', 'Magento\Backend\Model\Config\Backend\Store'],
    ['Mage_Adminhtml_Model_System_Config_Backend_Translate', 'Magento\Backend\Model\Config\Backend\Translate'],
    [
        'Mage_Adminhtml_Model_System_Config_Clone_Media_Image',
        'Magento\Catalog\Model\Config\CatalogClone\Media\Image'
    ],
    ['Mage_Adminhtml_Model_System_Config_Source_Admin_Page', 'Magento\Backend\Model\Config\Source\Admin\Page'],
    [
        'Mage_Adminhtml_Model_System_Config_Source_Catalog_Search_Type',
        'Magento\CatalogSearch\Model\Config\Source\Search\Type'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Source_Catalog_GridPerPage',
        'Magento\Catalog\Model\Config\Source\GridPerPage'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Source_Catalog_ListMode',
        'Magento\Catalog\Model\Config\Source\ListMode'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Source_Catalog_ListPerPage',
        'Magento\Catalog\Model\Config\Source\ListPerPage'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Source_Catalog_ListSort',
        'Magento\Catalog\Model\Config\Source\ListSort'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Source_Catalog_TimeFormat',
        'Magento\Catalog\Model\Config\Source\TimeFormat'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Source_Cms_Wysiwyg_Enabled',
        'Magento\Cms\Model\Config\Source\Wysiwyg\Enabled'
    ],
    ['Mage_Adminhtml_Model_System_Config_Source_Cms_Page', 'Magento\Cms\Model\Config\Source\Page'],
    [
        'Mage_Adminhtml_Model_System_Config_Source_Country_Full',
        'Magento\Directory\Model\Config\Source\Country\Full'
    ],
    ['Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency', 'Magento\Cron\Model\Config\Source\Frequency'],
    [
        'Mage_Adminhtml_Model_System_Config_Source_Currency_Service',
        'Magento\Backend\Model\Config\Source\Currency'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Source_Customer_Address_Type',
        'Magento\Customer\Model\Config\Source\Address\Type'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Source_Customer_Group_Multiselect',
        'Magento\Customer\Model\Config\Source\Group\Multiselect'
    ],
    ['Mage_Adminhtml_Model_System_Config_Source_Customer_Group', 'Magento\Customer\Model\Config\Source\Group'],
    ['Mage_Adminhtml_Model_System_Config_Source_Date_Short', 'Magento\Backend\Model\Config\Source\Date\Short'],
    ['Mage_Adminhtml_Model_System_Config_Source_Design_Package'],
    [
        'Mage_Adminhtml_Model_System_Config_Source_Design_Robots',
        'Magento\Backend\Model\Config\Source\Design\Robots'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Source_Dev_Dbautoup',
        'Magento\Backend\Model\Config\Source\Dev\Dbautoup'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Source_Email_Identity',
        'Magento\Backend\Model\Config\Source\Email\Identity'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Source_Email_Method',
        'Magento\Backend\Model\Config\Source\Email\Method'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Source_Email_Smtpauth',
        'Magento\Backend\Model\Config\Source\Email\Smtpauth'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Source_Email_Template',
        'Magento\Backend\Model\Config\Source\Email\Template'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Source_Image_Adapter',
        'Magento\Backend\Model\Config\Source\Image\Adapter'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Source_Locale_Country',
        'Magento\Backend\Model\Config\Source\Locale\Country'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Source_Locale_Currency_All',
        'Magento\Backend\Model\Config\Source\Locale\Currency\All'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Source_Locale_Currency',
        'Magento\Backend\Model\Config\Source\Locale\Currency'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Source_Locale_Timezone',
        'Magento\Backend\Model\Config\Source\Locale\Timezone'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Source_Locale_Weekdays',
        'Magento\Backend\Model\Config\Source\Locale\Weekdays'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Source_Notification_Frequency',
        'Magento\AdminNotification\Model\Config\Source\Frequency'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Source_Order_Status_New',
        'Magento\Sales\Model\Config\Source\Order\Status\NewStatus'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Source_Order_Status_Newprocessing',
        'Magento\Sales\Model\Config\Source\Order\Status\Newprocessing'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Source_Order_Status_Processing',
        'Magento\Sales\Model\Config\Source\Order\Status\Processing'
    ],
    ['Mage_Adminhtml_Model_System_Config_Source_Order_Status', 'Magento\Sales\Model\Config\Source\Order\Status'],
    [
        'Mage_Adminhtml_Model_System_Config_Source_Payment_Allmethods',
        'Magento\Payment\Model\Config\Source\Allmethods'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Source_Payment_Allowedmethods',
        'Magento\Payment\Model\Config\Source\Allowedmethods'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Source_Payment_Allspecificcountries',
        'Magento\Payment\Model\Config\Source\Allspecificcountries'
    ],
    ['Mage_Adminhtml_Model_System_Config_Source_Payment_Cctype', 'Magento\Payment\Model\Config\Source\Cctype'],
    ['Mage_Adminhtml_Model_System_Config_Source_Price_Scope', 'Magento\Catalog\Model\Config\Source\Price\Scope'],
    ['Mage_Adminhtml_Model_System_Config_Source_Price_Step', 'Magento\Catalog\Model\Config\Source\Price\Step'],
    [
        'Mage_Adminhtml_Model_System_Config_Source_Product_Options_Price',
        'Magento\Catalog\Model\Config\Source\Product\Options\Price'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Source_Product_Options_Type',
        'Magento\Catalog\Model\Config\Source\Product\Options\Type'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Source_Product_Thumbnail',
        'Magento\Catalog\Model\Config\Source\Product\Thumbnail'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Source_Reports_Scope',
        'Magento\Backend\Model\Config\Source\Reports\Scope'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Source_Shipping_Allmethods',
        'Magento\Shipping\Model\Config\Source\Allmethods'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Source_Shipping_Allspecificcountries',
        'Magento\Shipping\Model\Config\Source\Allspecificcountries'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Source_Shipping_Flatrate',
        'Magento\OfflineShipping\Model\Config\Source\Flatrate'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Source_Shipping_Tablerate',
        'Magento\OfflineShipping\Model\Config\Source\Tablerate'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Source_Shipping_Taxclass',
        'Magento\Tax\Model\TaxClass\Source\Product'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Source_Storage_Media_Database',
        'Magento\Backend\Model\Config\Source\Storage\Media\Database'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Source_Storage_Media_Storage',
        'Magento\Backend\Model\Config\Source\Storage\Media\Storage'
    ],
    ['Mage_Adminhtml_Model_System_Config_Source_Tax_Apply_On', 'Magento\Tax\Model\Config\Source\Apply\On'],
    ['Mage_Adminhtml_Model_System_Config_Source_Tax_Basedon', 'Magento\Tax\Model\Config\Source\Basedon'],
    ['Mage_Adminhtml_Model_System_Config_Source_Tax_Catalog', 'Magento\Tax\Model\Config\Source\Catalog'],
    [
        'Mage_Adminhtml_Model_System_Config_Source_Watermark_Position',
        'Magento\Catalog\Model\Config\Source\Watermark\Position'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Source_Web_Protocol',
        'Magento\Backend\Model\Config\Source\Web\Protocol'
    ],
    [
        'Mage_Adminhtml_Model_System_Config_Source_Web_Redirect',
        'Magento\Backend\Model\Config\Source\Web\Redirect'
    ],
    ['Mage_Adminhtml_Model_System_Config_Source_Allregion', 'Magento\Directory\Model\Config\Source\Allregion'],
    ['Mage_Adminhtml_Model_System_Config_Source_Category', 'Magento\Catalog\Model\Config\Source\Category'],
    ['Mage_Adminhtml_Model_System_Config_Source_Checktype', 'Magento\Backend\Model\Config\Source\Checktype'],
    ['Mage_Adminhtml_Model_System_Config_Source_Country', 'Magento\Directory\Model\Config\Source\Country'],
    ['Mage_Adminhtml_Model_System_Config_Source_Currency', 'Magento\Backend\Model\Config\Source\Currency'],
    [
        'Mage_Adminhtml_Model_System_Config_Source_Enabledisable',
        'Magento\Backend\Model\Config\Source\Enabledisable'
    ],
    ['Mage_Adminhtml_Model_System_Config_Source_Frequency', 'Magento\Sitemap\Model\Config\Source\Frequency'],
    ['Mage_Adminhtml_Model_System_Config_Source_Locale', 'Magento\Backend\Model\Config\Source\Locale'],
    ['Mage_Adminhtml_Model_System_Config_Source_Nooptreq', 'Magento\Backend\Model\Config\Source\Nooptreq'],
    ['Mage_Adminhtml_Model_System_Config_Source_Store', 'Magento\Backend\Model\Config\Source\Store'],
    ['Mage_Adminhtml_Model_System_Config_Source_Website', 'Magento\Backend\Model\Config\Source\Website'],
    ['Mage_Adminhtml_Model_System_Config_Source_Yesno', 'Magento\Backend\Model\Config\Source\Yesno'],
    [
        'Mage_Adminhtml_Model_System_Config_Source_Yesnocustom',
        'Magento\Backend\Model\Config\Source\Yesnocustom'
    ],
    ['Mage_Adminhtml_Model_System_Store', 'Magento\Store\Model\System\Store'],
    ['Mage_Adminhtml_Model_Url', 'Magento\Backend\Model\UrlInterface'],
    ['Mage_Adminhtml_Rss_CatalogController'],
    ['Mage_Adminhtml_Rss_OrderController'],
    ['Mage_Adminhtml_SystemController', 'Magento\Backend\Controller\Adminhtml\System'],
    ['Mage_Adminhtml_System_ConfigController', 'Magento\Backend\Controller\Adminhtml\System\Config'],
    [
        'Magento\Backend\Model\Config\Source\Currency\Service',
        'Magento\Directory\Model\Currency\Import\Source\Service'
    ],
    ['Mage_Backend_Model_Menu_Config_Menu'],
    ['Mage_Backend_Model_Menu_Director_Dom'],
    ['Mage_Backend_Model_Menu_Factory', 'Mage_Backend_Model_MenuFactory'],
    ['Mage_Bundle_Product_EditController', 'Mage_Bundle_Controller_Adminhtml_Bundle_Selection'],
    ['Mage_Bundle_SelectionController', 'Mage_Bundle_Controller_Adminhtml_Bundle_Selection'],
    ['Magento\Catalog\Block\Adminhtml\System\Config\Form\Field\Select\Flatproduct'],
    ['Mage_Catalog_Model_Category_Limitation'],
    ['Mage_Catalog_Model_Convert'],
    ['Mage_Catalog_Model_Convert_Adapter_Catalog'],
    ['Mage_Catalog_Model_Convert_Adapter_Product'],
    ['Mage_Catalog_Model_Convert_Parser_Product'],
    ['Mage_Catalog_Model_Entity_Product_Attribute_Frontend_Image'],
    ['Magento\Catalog\Model\Product\Flat\Flag'],
    ['Magento\Catalog\Model\Product\Flat\Indexer'],
    ['Magento\Catalog\Model\Product\Flat\Observer'],
    ['Magento\Catalog\Model\Product\Indexer\Flat'],
    ['Mage_Catalog_Model_Product_Limitation'],
    ['Mage_Catalog_Model_Resource_Product_Attribute_Frontend_Image'],
    ['Mage_Catalog_Model_Resource_Product_Attribute_Frontend_Tierprice'],
    [
        'Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab\Variations\Main',
        'Magento\ConfigurableProduct\Block\Adminhtml\Product\Attribute\Edit\Tab\Variations\Main'
    ],
    [
        'Magento\Catalog\Block\Adminhtml\Product\Attribute\NewAttribute\Product\Created',
        'Magento\ConfigurableProduct\Block\Adminhtml\Product\Attribute\NewAttribute\Product\Created'
    ],
    [
        'Magento\Catalog\Block\Adminhtml\Product\Composite\Fieldset\Configurable',
        'Magento\ConfigurableProduct\Block\Adminhtml\Product\Composite\Fieldset\Configurable'
    ],
    ['Magento\Catalog\Block\Adminhtml\Product\Created'],
    [
        'Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Super\Config\Grid\Filter\Inventory',
        'Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Super\Config\Grid\Filter\Inventory'
    ],
    [
        'Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Super\Config\Grid\Renderer\Checkbox',
        'Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Super\Config\Grid\Renderer\Checkbox'
    ],
    [
        'Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Super\Config\Grid\Renderer\Inventory',
        'Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Super\Config\Grid\Renderer\Inventory'
    ],
    [
        'Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Super\Config\Attribute',
        'Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Super\Config\Attribute'
    ],
    [
        '\Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Super\Config\Matrix',
        '\Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Super\Config\Matrix'
    ],
    [
        'Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Super\Config\Simple'
    ],
    [
        'Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Super\Config',
        'Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Super\Config'
    ],
    [
        '\Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Super\Settings',
        '\Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Super\Settings'
    ],
    [
        'Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs\Configurable',
        'Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tabs\Configurable'
    ],
    [
        'Magento\Catalog\Block\Product\Configurable\AssociatedSelector\Backend\Grid\ColumnSet',
        'Magento\ConfigurableProduct\Block\Product\Configurable\AssociatedSelector\Backend\Grid\ColumnSet'
    ],
    [
        'Magento\Catalog\Block\Product\Configurable\AssociatedSelector\Renderer\Id',
        'Magento\ConfigurableProduct\Block\Product\Configurable\AssociatedSelector\Renderer\Id'
    ],
    [
        'Magento\Catalog\Block\Product\Configurable\AttributeSelector',
        'Magento\ConfigurableProduct\Block\Product\Configurable\AttributeSelector'
    ],
    [
        'Magento\Catalog\Block\Product\View\Type\Configurable',
        'Magento\ConfigurableProduct\Block\Product\View\Type\Configurable'
    ],
    [
        'Magento\Catalog\Block\Layer\Filter\AbstractFilter',
        'Magento\LayeredNavigation\Block\Navigation\FilterRenderer'
    ],
    ['Magento\Catalog\Block\Layer\Filter\Attribute', 'Magento\LayeredNavigation\Block\Navigation\FilterRenderer'],
    ['Magento\Catalog\Block\Layer\Filter\Category', 'Magento\LayeredNavigation\Block\Navigation\FilterRenderer'],
    ['Magento\Catalog\Block\Layer\Filter\Decimal', 'Magento\LayeredNavigation\Block\Navigation\FilterRenderer'],
    ['Magento\Catalog\Block\Layer\Filter\Price', 'Magento\LayeredNavigation\Block\Navigation\FilterRenderer'],
    ['Magento\Catalog\Block\Layer\State', 'Magento\LayeredNavigation\Block\Navigation\State'],
    ['Magento\Catalog\Block\Layer\View', 'Magento\LayeredNavigation\Block\Navigation'],
    ['Magento\CatalogSearch\Block\Layer', 'Magento\LayeredNavigation\Block\Navigation'],
    [
        'Magento\CatalogSearch\Block\Layer\Filter\Attribute',
        'Magento\LayeredNavigation\Block\Navigation\FilterRenderer'
    ],
    ['Magento\CatalogSearch\Model\Layer', 'Magento\Catalog\Model\Layer'],
    ['Magento\Solr\Block\Catalog\Layer\View', 'Magento\LayeredNavigation\Block\Navigation'],
    [
        'Magento\Solr\Block\Catalog\Layer\Filter\Attribute',
        'Magento\LayeredNavigation\Block\Navigation\FilterRenderer'
    ],
    [
        'Magento\Solr\Block\Catalog\Layer\Filter\Category',
        'Magento\LayeredNavigation\Block\Navigation\FilterRenderer'
    ],
    [
        'Magento\Solr\Block\Catalog\Layer\Filter\Decimal',
        'Magento\LayeredNavigation\Block\Navigation\FilterRenderer'
    ],
    [
        'Magento\Solr\Block\Catalog\Layer\Filter\Price',
        'Magento\LayeredNavigation\Block\Navigation\FilterRenderer'
    ],
    [
        'Magento\Solr\Block\Catalogsearch\Layer\Filter\Attribute',
        'Magento\LayeredNavigation\Block\Navigation\FilterRenderer'
    ],
    ['Magento\Solr\Block\Catalogsearch\Layer', 'Magento\LayeredNavigation\Block\Navigation\FilterRenderer'],
    ['Magento\Solr\Model\Catalog\Layer', 'Magento\Catalog\Model\Layer\Category'],
    ['Magento\Solr\Model\Catalog\Layer\Filter\Attribute', 'Magento\Catalog\Model\Layer\Filter\Attribute'],
    ['Magento\Solr\Model\Catalog\Layer\Filter\Category', 'Magento\Catalog\Model\Layer\Filter\Category'],
    ['Magento\Solr\Model\Catalog\Layer\Filter\Decimal', 'Magento\Catalog\Model\Layer\Filter\Decimal'],
    ['Magento\Solr\Model\Catalog\Layer\Filter\Price', 'Magento\Catalog\Model\Layer\Filter\Price'],
    ['Magento\Solr\Model\Search\Layer\Filter\Attribute', 'Magento\Catalog\Model\Layer\Filter\Attribute'],
    ['Magento\Solr\Model\Search\Layer', 'Magento\Catalog\Model\Layer'],
    [
        'Magento\Catalog\Model\Product\Type\Configurable',
        'Magento\ConfigurableProduct\Model\Product\Type\Configurable'
    ],
    [
        'Magento\Catalog\Model\Resource\Product\Type\Configurable\Attribute',
        'Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Attribute'
    ],
    [
        'Magento\Catalog\Model\Resource\Product\Type\Configurable\Product\Collection',
        'Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Product\Collection'
    ],
    [
        'Magento\Catalog\Model\Resource\Product\Type\Configurable\Attribute\Collection',
        'Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Attribute\Collection'
    ],
    [
        'Magento\Catalog\Model\Resource\Product\Type\Configurable',
        'Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable'
    ],
    [
        'Magento\Catalog\Model\Resource\Product\Indexer\Price\Configurable',
        'Magento\ConfigurableProduct\Model\Resource\Product\Indexer\Price\Configurable'
    ],
    [
        'Magento\Catalog\Model\Product\Type\Configurable\Price',
        'Magento\ConfigurableProduct\Model\Product\Type\Configurable\Price'
    ],
    [
        'Magento\Checkout\Block\Cart\Item\Renderer\Configurable',
        'Magento\ConfigurableProduct\Block\Cart\Item\Renderer\Configurable'
    ],
    ['Magento\Catalog\Model\Resource\Product\Flat\Indexer'],
    ['Magento\Catalog\Model\System\Config\Backend\Catalog\Product\Flat'],
    ['Mage_Checkout_Block_Links'],
    ['Mage_Core_Block_Flush'],
    ['Mage_Core_Block_Template_Facade'],
    ['Mage_Core_Block_Template_Smarty'],
    ['Mage_Core_Block_Template_Zend'],
    ['Mage_Core_Controller_Magento_Router_Admin', 'Magento\Backend\App\Router'],
    ['Mage_Core_Model_Convert'],
    ['Mage_Core_Model_Config_Fieldset', 'Magento\Core\Model\Fieldset\Config'],
    ['Mage_Core_Model_Config_Options', 'Magento\Framework\Filesystem'],
    ['Magento\Framework\App\Dir', 'Magento\Framework\Filesystem'],
    ['Magento\Framework\Filesystem\Adapter\Local', 'Magento\Framework\Filesystem\Driver\File'],
    ['Magento\Framework\Filesystem\Adapter\Zlib', 'Magento\Framework\Filesystem\Driver\Zlib'],
    ['Magento\Framework\Filesystem\AdapterInterface'],
    ['Magento\Framework\Filesystem\Stream\FactoryInterface'],
    ['Magento\Framework\Filesystem\Stream\Local'],
    ['Magento\Framework\Filesystem\Stream\Mode'],
    ['Magento\Framework\Filesystem\Stream\Zlib'],
    ['Magento\Framework\Filesystem\Stream\Mode\Zlib'],
    ['Mage_Core_Model_Config_Module'],
    ['Mage_Core_Model_Config_System'],
    ['Mage_Core_Model_Design_Source_Apply'],
    ['Mage_Core_Model_Language'],
    ['Magento\Core\Model\Flag', 'Magento\Framework\Flag'],
    ['Magento\Core\Exception', 'Magento\Framework\Model\Exception'],
    ['Magento\Core\Model\AbstractModel', 'Magento\Framework\Model\AbstractModel'],
    ['Magento\Core\Model\Email\Info', 'Magento\Framework\Mail\MessageInterface'],
    ['Magento\Core\Model\Email\Sender', 'Magento\Framework\Mail\Template\TransportBuilder'],
    ['Magento\Core\Model\Email\Template\Mailer', 'Magento\Framework\Mail\Template\TransportBuilder'],
    ['Magento\Core\Model\Resource\AbstractResource', 'Magento\Framework\Model\Resource\AbstractResource'],
    ['Magento\Core\Model\Resource\Db\AbstractDb', 'Magento\Framework\Model\Resource\Db\AbstractDb'],
    ['Magento\Core\Model\Resource\Db\Profiler', 'Magento\Framework\Model\Resource\Db\Profiler'],
    [
        'Magento\Core\Model\Resource\Entity\AbstractEntity',
        'Magento\Framework\Model\Resource\Entity\AbstractEntity'
    ],
    ['Magento\Core\Model\Resource\Entity\Table', 'Magento\Framework\Model\Resource\Entity\Table'],
    ['Magento\Core\Model\Resource\Flag', 'Magento\Framework\Flag\Resource'],
    ['Magento\Core\Model\Resource\Iterator', 'Magento\Framework\Model\Resource\Iterator'],
    ['Magento\Core\Model\Resource\Resource', 'Magento\Framework\Module\Resource'],
    ['Magento\Core\Model\Resource\Type\AbstractType', 'Magento\Framework\Model\Resource\Type\AbstractType'],
    ['Magento\Core\Model\Resource\Type\Db', 'Magento\Framework\Model\Resource\Type\Db'],
    ['Magento\Core\Model\Resource\Type\Db\Pdo\Mysql', 'Magento\Framework\Model\Resource\Type\Db\Pdo\Mysql'],
    [
        'Magento\Core\Model\Resource\Db\Collection\AbstractCollection',
        'Magento\Framework\Model\Resource\Db\Collection\AbstractCollection'
    ],
    ['Magento\Email\Model\Info', 'Magento\Framework\Mail\MessageInterface'],
    ['Magento\Email\Model\Sender', 'Magento\Framework\Mail\Template\TransportBuilder'],
    ['Magento\Email\Model\Template\Mailer', 'Magento\Framework\Mail\Template\TransportBuilder'],
    ['Magento\Core\Model\Email\Template', 'Magento\Email\Model\Template'],
    ['Magento\Core\Model\Email\Transport', 'Magento\Email\Model\Transport'],
    ['Magento\Core\Model\Email\Template\Config', 'Magento\Email\Model\Template\Config'],
    ['Magento\Core\Model\Email\Template\Filter', 'Magento\Email\Model\Template\Filter'],
    ['Magento\Core\Model\Email\Template\Config\Converter', 'Magento\Email\Model\Template\Config\Converter'],
    ['Magento\Core\Model\Template\Config\Data', 'Magento\Email\Model\Template\Config\Data'],
    ['Magento\Core\Model\Template\Config\SchemaLocator', 'Magento\Email\Model\Template\Config\SchemaLocator'],
    ['Magento\Core\Model\Resource\Email\Template', 'Magento\Email\Model\Resource\Template'],
    ['Magento\Core\Model\Resource\Email\Template\Collection', 'Magento\Email\Model\Resource\Template\Collection'],
    ['Mage_Core_Model_Resource_Language'],
    ['Mage_Core_Model_Resource_Language_Collection'],
    ['Mage_Core_Model_Resource_Setup_Query_Modifier'],
    ['Mage_Core_Model_Session_Abstract_Varien'],
    ['Mage_Core_Model_Session_Abstract_Zend'],
    ['Magento\Core\Model\Source\Email\Variables', 'Magento\Email\Model\Source\Variables'],
    ['Magento\Core\Model\Store\ListInterface', 'Magento\Store\Model\StoreManagerInterface'],
    ['Magento\Core\Model\Store\StorageInterface', 'Magento\Store\Model\StoreManagerInterface'],
    ['Mage_Core_Model_Store_Group_Limitation'],
    ['Mage_Core_Model_Store_Limitation'],
    ['Magento\Core\Model\Variable\Observer'],
    ['Mage_Core_Model_Website_Limitation'],
    ['Mage_Core_Model_Layout_Data', 'Magento\Core\Model\Layout\Update'],
    ['Mage_Core_Model_Theme_Customization_Link'],
    ['Mage_Customer_Block_Account'],
    ['Mage_Customer_Block_Account_Navigation'],
    ['Mage_Customer_Model_Convert_Adapter_Customer'],
    ['Mage_Customer_Model_Convert_Parser_Customer'],
    [
        'Mage_Customer_Model_Resource_Address_Attribute_Backend_Street',
        'Mage_Eav_Model_Entity_Attribute_Backend_Default'
    ],
    ['Mage_DesignEditor_Block_Page_Html_Head_Vde'],
    ['Mage_DesignEditor_Block_Page_Html_Head'],
    ['Mage_Directory_Model_Resource_Currency_Collection'],
    ['Mage_Downloadable_FileController', 'Magento\Downloadable\Controller\Adminhtml\Downloadable\File'],
    ['Mage_Downloadable_Product_EditController', 'Magento\Backend\Controller\Catalog\Product'],
    ['Mage_Eav_Model_Convert_Adapter_Entity'],
    ['Mage_Eav_Model_Convert_Adapter_Grid'],
    ['Mage_Eav_Model_Convert_Parser_Abstract'],
    ['Mage_Eav_Model_Entity_Collection'],
    ['Mage_GiftMessage_Block_Message_Form'],
    ['Mage_GiftMessage_Block_Message_Helper'],
    ['Mage_GiftMessage_IndexController'],
    ['Mage_GiftMessage_Model_Entity_Attribute_Backend_Boolean_Config'],
    ['Mage_GiftMessage_Model_Entity_Attribute_Source_Boolean_Config'],
    ['Mage_GoogleOptimizer_IndexController', 'Magento\GoogleOptimizer\Adminhtml\Googleoptimizer\IndexController'],
    ['Mage_GoogleShopping_Block_Adminhtml_Types_Grid'],
    ['Mage_GoogleShopping_Helper_SiteVerification', 'Mage_GoogleShopping_Block_SiteVerification'],
    ['Mage_ImportExport_Model_Import_Adapter_Abstract', 'Mage_ImportExport_Model_Import_SourceAbstract'],
    ['Mage_ImportExport_Model_Import_Adapter_Csv', 'Mage_ImportExport_Model_Import_Source_Csv'],
    ['Mage_Install_Model_Installer_Env'],
    ['Mage_Ogone_Model_Api_Debug'],
    ['Mage_Ogone_Model_Resource_Api_Debug'],
    ['Mage_Page_Block_Html_Toplinks'],
    ['Mage_Page_Block_Html_Wrapper'],
    ['Mage_Page_Block_Template_Links'],
    ['Mage_Paypal_Block_Adminhtml_Settlement_Report_Grid'],
    ['Mage_ProductAlert_Block_Price'],
    ['Mage_ProductAlert_Block_Stock'],
    ['Mage_Reports_Model_Resource_Coupons_Collection'],
    ['Mage_Reports_Model_Resource_Invoiced_Collection'],
    ['Mage_Reports_Model_Resource_Product_Ordered_Collection'],
    [
        'Mage_Reports_Model_Resource_Product_Viewed_Collection',
        'Magento\Reports\Model\Resource\Report\Product\Viewed\Collection'
    ],
    ['Mage_Reports_Model_Resource_Refunded_Collection'],
    ['Mage_Reports_Model_Resource_Shipping_Collection'],
    ['Mage_Reports_Model_Report'],
    ['Mage_Reports_Model_Test'],
    ['Mage_Rss_Model_Observer'],
    ['Mage_Rss_Model_Session', 'Magento_Backend_Model_Auth and \Magento\Backend\Model\Auth\Session'],
    [
        'Magento\Sales\Block\Adminhtml\Order\Invoice\Create\Tracking',
        '\Magento\Shipping\Block\Adminhtml\Order\Tracking'
    ],
    [
        'Magento\Sales\Block\Adminhtml\Order\Shipment\Create\Tracking',
        'Magento\Shipping\Block\Adminhtml\Order\Tracking'
    ],
    [
        'Magento\Sales\Block\Adminhtml\Order\Shipment\Packaging',
        '\Magento\Shipping\Block\Adminhtml\Order\Packaging'
    ],
    [
        'Magento\Sales\Block\Adminhtml\Order\Shipment\Packaging\Grid',
        '\Magento\Shipping\Block\Adminhtml\Order\Packaging\Grid'
    ],
    [
        'Magento\Sales\Block\Adminhtml\Order\Shipment\Tracking\Info',
        '\Magento\Shipping\Block\Adminhtml\Order\Tracking'
    ],
    [
        'Magento\Sales\Block\Adminhtml\Order\Shipment\View\Tracking',
        'Magento\Shipping\Block\Adminhtml\Order\Tracking\View'
    ],
    ['Mage_Sales_Block_Order_Details'],
    ['Mage_Sales_Block_Order_Tax'],
    ['Mage_Sales_Block_Guest_Links'],
    ['Mage_Sales_Model_Entity_Order'],
    ['Mage_Sales_Model_Entity_Order_Address'],
    ['Mage_Sales_Model_Entity_Order_Address_Collection'],
    ['Mage_Sales_Model_Entity_Order_Attribute_Backend_Billing'],
    ['Mage_Sales_Model_Entity_Order_Attribute_Backend_Child'],
    ['Mage_Sales_Model_Entity_Order_Attribute_Backend_Parent'],
    ['Mage_Sales_Model_Entity_Order_Attribute_Backend_Shipping'],
    ['Mage_Sales_Model_Entity_Order_Collection'],
    ['Mage_Sales_Model_Entity_Order_Creditmemo'],
    ['Mage_Sales_Model_Entity_Order_Creditmemo_Attribute_Backend_Child'],
    ['Mage_Sales_Model_Entity_Order_Creditmemo_Attribute_Backend_Parent'],
    ['Mage_Sales_Model_Entity_Order_Creditmemo_Collection'],
    ['Mage_Sales_Model_Entity_Order_Creditmemo_Comment'],
    ['Mage_Sales_Model_Entity_Order_Creditmemo_Comment_Collection'],
    ['Mage_Sales_Model_Entity_Order_Creditmemo_Item'],
    ['Mage_Sales_Model_Entity_Order_Creditmemo_Item_Collection'],
    ['Mage_Sales_Model_Entity_Order_Invoice'],
    ['Mage_Sales_Model_Entity_Order_Invoice_Attribute_Backend_Child'],
    ['Mage_Sales_Model_Entity_Order_Invoice_Attribute_Backend_Item'],
    ['Mage_Sales_Model_Entity_Order_Invoice_Attribute_Backend_Order'],
    ['Mage_Sales_Model_Entity_Order_Invoice_Attribute_Backend_Parent'],
    ['Mage_Sales_Model_Entity_Order_Invoice_Collection'],
    ['Mage_Sales_Model_Entity_Order_Invoice_Comment'],
    ['Mage_Sales_Model_Entity_Order_Invoice_Comment_Collection'],
    ['Mage_Sales_Model_Entity_Order_Invoice_Item'],
    ['Mage_Sales_Model_Entity_Order_Invoice_Item_Collection'],
    ['Mage_Sales_Model_Entity_Order_Item'],
    ['Mage_Sales_Model_Entity_Order_Item_Collection'],
    ['Mage_Sales_Model_Entity_Order_Payment'],
    ['Mage_Sales_Model_Entity_Order_Payment_Collection'],
    ['Mage_Sales_Model_Entity_Order_Shipment'],
    ['Mage_Sales_Model_Entity_Order_Shipment_Attribute_Backend_Child'],
    ['Mage_Sales_Model_Entity_Order_Shipment_Attribute_Backend_Parent'],
    ['Mage_Sales_Model_Entity_Order_Shipment_Collection'],
    ['Mage_Sales_Model_Entity_Order_Shipment_Comment'],
    ['Mage_Sales_Model_Entity_Order_Shipment_Comment_Collection'],
    ['Mage_Sales_Model_Entity_Order_Shipment_Item'],
    ['Mage_Sales_Model_Entity_Order_Shipment_Item_Collection'],
    ['Mage_Sales_Model_Entity_Order_Shipment_Track'],
    ['Mage_Sales_Model_Entity_Order_Shipment_Track_Collection'],
    ['Mage_Sales_Model_Entity_Order_Status_History'],
    ['Mage_Sales_Model_Entity_Order_Status_History_Collection'],
    ['Mage_Sales_Model_Entity_Quote'],
    ['Mage_Sales_Model_Entity_Quote_Address'],
    ['Mage_Sales_Model_Entity_Quote_Address_Attribute_Backend'],
    ['Mage_Sales_Model_Entity_Quote_Address_Attribute_Backend_Child'],
    ['Mage_Sales_Model_Entity_Quote_Address_Attribute_Backend_Parent'],
    ['Mage_Sales_Model_Entity_Quote_Address_Attribute_Backend_Region'],
    ['Mage_Sales_Model_Entity_Quote_Address_Attribute_Frontend'],
    ['Mage_Sales_Model_Entity_Quote_Address_Attribute_Frontend_Custbalance'],
    ['Mage_Sales_Model_Entity_Quote_Address_Attribute_Frontend_Discount'],
    ['Mage_Sales_Model_Entity_Quote_Address_Attribute_Frontend_Grand'],
    ['Mage_Sales_Model_Entity_Quote_Address_Attribute_Frontend_Shipping'],
    ['Mage_Sales_Model_Entity_Quote_Address_Attribute_Frontend_Subtotal'],
    ['Mage_Sales_Model_Entity_Quote_Address_Attribute_Frontend_Tax'],
    ['Mage_Sales_Model_Entity_Quote_Address_Collection'],
    ['Mage_Sales_Model_Entity_Quote_Address_Item'],
    ['Mage_Sales_Model_Entity_Quote_Address_Item_Collection'],
    ['Mage_Sales_Model_Entity_Quote_Address_Rate'],
    ['Mage_Sales_Model_Entity_Quote_Address_Rate_Collection'],
    ['Mage_Sales_Model_Entity_Quote_Collection'],
    ['Mage_Sales_Model_Entity_Quote_Item'],
    ['Mage_Sales_Model_Entity_Quote_Item_Collection'],
    ['Mage_Sales_Model_Entity_Quote_Payment'],
    ['Mage_Sales_Model_Entity_Quote_Payment_Collection'],
    ['Mage_Sales_Model_Entity_Sale_Collection'],
    ['Mage_Sales_Model_Entity_Setup'],
    ['Mage_Shipping_ShippingController'],
    ['Mage_Tag_Block_Adminhtml_Report_Customer_Detail_Grid'],
    ['Mage_Tag_Block_Adminhtml_Report_Customer_Grid'],
    ['Mage_Tag_Block_Adminhtml_Report_Popular_Detail_Grid'],
    ['Mage_Tag_Block_Adminhtml_Report_Product_Detail_Grid'],
    ['Mage_Tag_Block_Adminhtml_Report_Product_Grid'],
    ['Mage_Tag_Block_Customer_Edit'],
    ['Mage_Theme_Block_Adminhtml_System_Design_Theme_Grid'],
    ['Mage_User_Block_Role_Grid'],
    ['Mage_User_Block_User_Grid'],
    ['Mage_User_Model_Roles'],
    ['Mage_User_Model_Resource_Roles'],
    ['Mage_User_Model_Resource_Roles_Collection'],
    ['Mage_User_Model_Resource_Roles_User_Collection'],
    ['Mage_Widget_Block_Adminhtml_Widget_Instance_Grid'],
    ['Magento\Widget\Model\Observer'],
    ['Mage_Wishlist_Model_Resource_Product_Collection'],
    ['Varien_Convert_Action'],
    ['Varien_Convert_Action_Abstract'],
    ['Varien_Convert_Action_Interface'],
    ['Varien_Convert_Adapter_Abstract'],
    ['Varien_Convert_Adapter_Db_Table'],
    ['Varien_Convert_Adapter_Http'],
    ['Varien_Convert_Adapter_Http_Curl'],
    ['Varien_Convert_Adapter_Interface'],
    ['Varien_Convert_Adapter_Io'],
    ['Varien_Convert_Adapter_Soap'],
    ['Varien_Convert_Adapter_Std'],
    ['Varien_Convert_Adapter_Zend_Cache'],
    ['Varien_Convert_Adapter_Zend_Db'],
    ['Varien_Convert_Container_Collection'],
    ['Varien_Convert_Container_Generic'],
    ['Varien_Convert_Container_Interface'],
    ['Varien_Convert_Mapper_Abstract'],
    ['Varien_Convert_Parser_Abstract'],
    ['Varien_Convert_Parser_Csv'],
    ['Varien_Convert_Parser_Interface'],
    ['Varien_Convert_Parser_Serialize'],
    ['Varien_Convert_Parser_Xml_Excel'],
    ['Varien_Convert_Profile'],
    ['Varien_Convert_Profile_Abstract'],
    ['Varien_Convert_Profile_Collection'],
    ['Varien_Convert_Validator_Abstract'],
    ['Varien_Convert_Validator_Column'],
    ['Varien_Convert_Validator_Dryrun'],
    ['Varien_Convert_Validator_Interface'],
    ['Mage_File_Uploader_Image'],
    ['Varien_Profiler', 'Magento_Framework_Profiler'],
    ['Mage_Adminhtml_Block_Notification_Window', 'Magento\AdminNotification\Block\Window'],
    ['Mage_Adminhtml_Block_Notification_Toolbar'],
    ['Mage_Adminhtml_Block_Notification_Survey'],
    ['Mage_Adminhtml_Block_Notification_Security'],
    ['Mage_Adminhtml_Block_Notification_Inbox'],
    ['Mage_Adminhtml_Block_Notification_Grid', 'Magento\AdminNotification\Block\Notification\Grid'],
    ['Mage_Adminhtml_Block_Notification_Baseurl'],
    [
        'Mage_Adminhtml_Block_Notification_Grid_Renderer_Severity',
        'Magento\AdminNotification\Block\Grid\Renderer\Severity'
    ],
    [
        'Mage_Adminhtml_Block_Notification_Grid_Renderer_Notice',
        'Magento\AdminNotification\Block\Grid\Renderer\Notice'
    ],
    [
        'Mage_Adminhtml_Block_Notification_Grid_Renderer_Actions',
        'Magento\AdminNotification\Block\Grid\Renderer\Actions'
    ],
    ['Mage_Adminhtml_Block_Cache_Notifications'],
    ['Mage_AdminNotification_Block_Grid'],
    ['Mage_Core_Model_Design_Package'],
    ['Mage_Core_Model_Design_PackageInterface'],
    ['Mage_Core_Model_Resource_Type_Db_Mysqli_Setup'],
    ['Mage_Core_Model_Resource_Type_Db_Mysqli'],
    ['Varien_Db_Adapter_Mysqli'],
    ['Mage_DB_Mysqli'],
    ['Mage_DB_Exception'],
    [
        'Magento\Catalog\Block\Product\View\Media',
        'Decomposed into \Magento\Catalog\Block\Product\View\Gallery' .
        ' and \Magento\Catalog\Block\Product\View\BaseImage classes'
    ],
    ['Magento\Wishlist\Block\Links', 'Magento\Wishlist\Block\Link'],
    ['Magento\Wishlist\Block\Render\Item\Price'],
    ['Mage_Adminhtml_Block_Api_Tab_Userroles'],
    ['Mage_Adminhtml_Block_Api_Tab_Roleinfo'],
    ['Mage_Adminhtml_Block_Api_Tab_Rolesusers'],
    ['Mage_Adminhtml_Block_Api_Tab_Rolesedit'],
    ['Mage_Adminhtml_Block_Api_Editroles'],
    ['Mage_Adminhtml_Block_Api_Buttons'],
    ['Mage_Adminhtml_Block_Api_Users'],
    ['Mage_Adminhtml_Block_Api_Role_Grid_User'],
    ['Mage_Adminhtml_Block_Api_Grid_Role'],
    ['Mage_Adminhtml_Block_Api_Roles'],
    ['Mage_Adminhtml_Block_Api_User_Edit_Tab_Main'],
    ['Mage_Adminhtml_Block_Api_User_Edit_Tab_Roles'],
    ['Mage_Adminhtml_Block_Api_User_Edit_Tabs'],
    ['Mage_Adminhtml_Block_Api_User_Edit_Form'],
    ['Mage_Adminhtml_Block_Api_User_Grid'],
    ['Mage_Adminhtml_Block_Api_User_Edit'],
    ['Mage_Adminhtml_Block_Api_Role'],
    ['Mage_Adminhtml_Block_Api_User'],
    ['Mage_Adminhtml_Block_Api_Edituser'],
    ['Mage_Api_Exception'],
    ['Mage_Api_Controller_Action'],
    ['Mage_Api_Model_Acl_Role_Generic'],
    ['Mage_Api_Model_Acl_Role_Group'],
    ['Mage_Api_Model_Acl_Role_Registry'],
    ['Mage_Api_Model_Acl_Role_User'],
    ['Mage_Api_Model_Acl_Assert_Ip'],
    ['Mage_Api_Model_Acl_Assert_Time'],
    ['Mage_Api_Model_Acl_Role'],
    ['Mage_Api_Model_Acl_Resource'],
    ['Mage_Api_Model_Rules'],
    ['Mage_Api_Model_Wsdl_Config'],
    ['Mage_Api_Model_Wsdl_Config_Base'],
    ['Mage_Api_Model_Wsdl_Config_Element'],
    ['Mage_Api_Model_Server'],
    ['Mage_Api_Model_Mysql4_Acl_Role_Collection'],
    ['Mage_Api_Model_Mysql4_Acl_Role'],
    ['Mage_Api_Model_Mysql4_Rules'],
    ['Mage_Api_Model_Mysql4_Role_Collection'],
    ['Mage_Api_Model_Mysql4_Rules_Collection'],
    ['Mage_Api_Model_Mysql4_Roles'],
    ['Mage_Api_Model_Mysql4_Permissions_Collection'],
    ['Mage_Api_Model_Mysql4_User_Collection'],
    ['Mage_Api_Model_Mysql4_Roles_Collection'],
    ['Mage_Api_Model_Mysql4_Roles_User_Collection'],
    ['Mage_Api_Model_Mysql4_Role'],
    ['Mage_Api_Model_Mysql4_Acl'],
    ['Mage_Api_Model_Mysql4_User'],
    ['Mage_Api_Model_Session'],
    ['Mage_Api_Model_Config'],
    ['Mage_Api_Model_Server_V2_Adapter_Soap'],
    ['Mage_Api_Model_Server_V2_Handler'],
    ['Mage_Api_Model_Server_Adapter_Soap'],
    ['Mage_Api_Model_Server_Adapter_Xmlrpc'],
    ['Mage_Api_Model_Server_WSI_Adapter_Soap'],
    ['Mage_Api_Model_Server_WSI_Handler'],
    ['Mage_Api_Model_Server_Handler'],
    ['Mage_Api_Model_Roles'],
    ['Mage_Api_Model_Role'],
    ['Mage_Api_Model_Acl'],
    ['Mage_Api_Model_Resource_Acl_Role_Collection'],
    ['Mage_Api_Model_Resource_Acl_Role'],
    ['Mage_Api_Model_Resource_Rules'],
    ['Mage_Api_Model_Resource_Role_Collection'],
    ['Mage_Api_Model_Resource_Rules_Collection'],
    ['Mage_Api_Model_Resource_Roles'],
    ['Mage_Api_Model_Resource_Permissions_Collection'],
    ['Mage_Api_Model_Resource_User_Collection'],
    ['Mage_Api_Model_Resource_Roles_Collection'],
    ['Mage_Api_Model_Resource_Roles_User_Collection'],
    ['Mage_Api_Model_Resource_Role'],
    ['Mage_Api_Model_Resource_Acl'],
    ['Mage_Api_Model_Resource_Abstract'],
    ['Mage_Api_Model_Resource_User'],
    ['Mage_Api_Model_User'],
    ['Mage_Api_Helper_Data'],
    ['Mage_Api_XmlrpcController'],
    ['Mage_Api_V2_SoapController'],
    ['Mage_Api_SoapController'],
    ['Mage_Api_IndexController'],
    ['Mage_Catalog_Model_Api_Resource'],
    ['Mage_Catalog_Model_Api2_Product_Website'],
    ['Mage_Catalog_Model_Api2_Product_Website_Rest_Admin_V1'],
    ['Mage_Catalog_Model_Api2_Product_Website_Validator_Admin_Website'],
    ['Mage_Catalog_Model_Api2_Product_Rest_Customer_V1'],
    ['Mage_Catalog_Model_Api2_Product_Rest_Guest_V1'],
    ['Mage_Catalog_Model_Api2_Product_Rest_Admin_V1'],
    ['Mage_Catalog_Model_Api2_Product_Category'],
    ['Mage_Catalog_Model_Api2_Product_Image'],
    ['Mage_Catalog_Model_Api2_Product_Category_Rest_Customer_V1'],
    ['Mage_Catalog_Model_Api2_Product_Category_Rest_Guest_V1'],
    ['Mage_Catalog_Model_Api2_Product_Category_Rest_Admin_V1'],
    ['Mage_Catalog_Model_Api2_Product_Image_Rest_Customer_V1'],
    ['Mage_Catalog_Model_Api2_Product_Image_Rest_Guest_V1'],
    ['Mage_Catalog_Model_Api2_Product_Image_Rest_Admin_V1'],
    ['Mage_Catalog_Model_Api2_Product_Image_Validator_Image'],
    ['Mage_Catalog_Model_Api2_Product_Validator_Product'],
    ['Mage_Catalog_Model_Api2_Product'],
    ['Mage_Catalog_Model_Product_Api_V2'],
    ['Mage_Catalog_Model_Product_Api'],
    ['Mage_Catalog_Model_Product_Option_Api_V2'],
    ['Mage_Catalog_Model_Product_Option_Value_Api_V2'],
    ['Mage_Catalog_Model_Product_Option_Value_Api'],
    ['Mage_Catalog_Model_Product_Option_Api'],
    ['Mage_Catalog_Model_Product_Type_Api_V2'],
    ['Mage_Catalog_Model_Product_Type_Api'],
    ['Mage_Catalog_Model_Product_Attribute_Tierprice_Api_V2'],
    ['Mage_Catalog_Model_Product_Attribute_Tierprice_Api'],
    ['Mage_Catalog_Model_Product_Attribute_Media_Api_V2'],
    ['Mage_Catalog_Model_Product_Attribute_Media_Api'],
    ['Mage_Catalog_Model_Product_Attribute_Api_V2'],
    ['Mage_Catalog_Model_Product_Attribute_Set_Api_V2'],
    ['Mage_Catalog_Model_Product_Attribute_Set_Api'],
    ['Mage_Catalog_Model_Product_Attribute_Api'],
    ['Mage_Catalog_Model_Product_Link_Api_V2'],
    ['Mage_Catalog_Model_Product_Link_Api'],
    ['Mage_Catalog_Model_Category_Api_V2'],
    ['Mage_Catalog_Model_Category_Api'],
    ['Mage_Catalog_Model_Category_Attribute_Api_V2'],
    ['Mage_Catalog_Model_Category_Attribute_Api'],
    ['Mage_Checkout_Model_Api_Resource'],
    ['Mage_Checkout_Model_Api_Resource_Customer'],
    ['Mage_Checkout_Model_Api_Resource_Product'],
    ['Mage_Checkout_Model_Cart_Api_V2'],
    ['Mage_Checkout_Model_Cart_Payment_Api'],
    ['Mage_Checkout_Model_Cart_Customer_Api_V2'],
    ['Mage_Checkout_Model_Cart_Customer_Api'],
    ['Mage_Checkout_Model_Cart_Api'],
    ['Mage_Checkout_Model_Cart_Product_Api_V2'],
    ['Mage_Checkout_Model_Cart_Product_Api'],
    ['Mage_Checkout_Model_Cart_Shipping_Api_V2'],
    ['Mage_Checkout_Model_Cart_Shipping_Api'],
    ['Mage_Checkout_Model_Cart_Coupon_Api_V2'],
    ['Mage_Checkout_Model_Cart_Coupon_Api'],
    ['Mage_Core_Model_Store_Api_V2'],
    ['Mage_Core_Model_Store_Api'],
    ['Mage_Core_Model_Magento_Api_V2'],
    ['Mage_Core_Model_Magento_Api'],
    ['Mage_Customer_Model_Group_Api_V2'],
    ['Mage_Customer_Model_Group_Api'],
    ['Mage_Customer_Model_Api_Resource'],
    ['Mage_Customer_Model_Customer_Api_V2'],
    ['Mage_Customer_Model_Customer_Api'],
    ['Mage_Customer_Model_Api2_Customer'],
    ['Mage_Customer_Model_Api2_Customer_Address'],
    ['Mage_Customer_Model_Api2_Customer_Rest_Customer_V1'],
    ['Mage_Customer_Model_Api2_Customer_Rest_Admin_V1'],
    ['Mage_Customer_Model_Api2_Customer_Address_Validator'],
    ['Mage_Customer_Model_Api2_Customer_Address_Rest_Customer_V1'],
    ['Mage_Customer_Model_Api2_Customer_Address_Rest_Admin_V1'],
    ['Mage_Customer_Model_Address_Api_V2'],
    ['Mage_Customer_Model_Address_Api'],
    ['Mage_Directory_Model_Region_Api_V2'],
    ['Mage_Directory_Model_Region_Api'],
    ['Mage_Directory_Model_Country_Api_V2'],
    ['Mage_Directory_Model_Country_Api'],
    ['Mage_Downloadable_Model_Link_Api_V2'],
    ['Mage_Downloadable_Model_Link_Api_Validator'],
    ['Mage_Downloadable_Model_Link_Api_Uploader'],
    ['Mage_Downloadable_Model_Link_Api'],
    ['Mage_GiftMessage_Model_Api_V2'],
    ['Mage_GiftMessage_Model_Api'],
    ['Mage_Sales_Model_Api_Resource'],
    ['Mage_Sales_Model_Api2_Order_Item_Rest_Customer_V1'],
    ['Mage_Sales_Model_Api2_Order_Item_Rest_Admin_V1'],
    ['Mage_Sales_Model_Api2_Order_Comment_Rest_Customer_V1'],
    ['Mage_Sales_Model_Api2_Order_Comment_Rest_Admin_V1'],
    ['Mage_Sales_Model_Api2_Order_Item'],
    ['Mage_Sales_Model_Api2_Order_Comment'],
    ['Mage_Sales_Model_Api2_Order_Address'],
    ['Mage_Sales_Model_Api2_Order_Rest_Customer_V1'],
    ['Mage_Sales_Model_Api2_Order_Rest_Admin_V1'],
    ['Mage_Sales_Model_Api2_Order_Address_Rest_Customer_V1'],
    ['Mage_Sales_Model_Api2_Order_Address_Rest_Admin_V1'],
    ['Mage_Sales_Model_Api2_Order'],
    ['Mage_Sales_Model_Order_Api_V2'],
    ['Mage_Sales_Model_Order_Shipment_Api_V2'],
    ['Mage_Sales_Model_Order_Shipment_Api'],
    ['Mage_Sales_Model_Order_Invoice_Api_V2'],
    ['Mage_Sales_Model_Order_Invoice_Api'],
    ['Mage_Sales_Model_Order_Api'],
    ['Mage_Sales_Model_Order_Creditmemo_Api_V2'],
    ['Mage_Sales_Model_Order_Creditmemo_Api'],
    ['Magento\ImportExport\Model\Config'],
    ['Magento\Install\Model\EntryPoint\Console'],
    ['Magento\Install\Model\EntryPoint\Output'],
    ['Magento\Framework\Data\Collection\Factory', 'Magento\Framework\Data\CollectionFactory'],
    ['Magento\Customer\Block\Adminhtml\System\Config\ValidatevatFactory'],
    ['Magento\Customer\Model\Attribute\Data'],
    ['Magento\Eav\Model\Attribute\Data'],
    ['Magento\Log\Model\Resource\Helper\Mysql4', 'Magento\Log\Model\Resource\Helper'],
    ['Magento\CatalogSearch\Model\Resource\Helper\Mysql4', 'Magento\CatalogSearch\Model\Resource\Helper'],
    ['Magento\ImportExport\Model\Resource\Helper\Mysql4', 'Magento\ImportExport\Model\Resource\Helper'],
    ['Magento\Reports\Model\Resource\Helper\Mysql4', 'Magento\Reports\Model\Resource\Helper'],
    ['Magento\Backup\Model\Resource\Helper\Mysql4', 'Magento\Backup\Model\Resource\Helper'],
    ['Magento\Sales\Model\CarrierFactory', 'Magento\Shipping\Model\CarrierFactory'],
    ['Magento\Sales\Model\Order\Pdf\Shipment\Packaging', 'Magento\Shipping\Model\Order\Pdf\Packaging'],
    ['Magento\Sales\Model\ResourceFactory'],
    ['Magento\Sales\Model\Resource\Helper\Mysql4', 'Magento\Sales\Model\Resource\Helper'],
    ['Magento\Core\Model\Resource\Helper\Mysql4', 'Magento\Framework\DB\Helper'],
    ['Magento\Core\Model\Resource\Helper', 'Magento\Framework\DB\Helper'],
    ['Magento\Core\Model\Resource\Helper\AbstractHelper', 'Magento\Framework\DB\Helper\AbstractHelper'],
    ['Magento\Core\Model\Resource\HelperFactory'],
    ['Magento\Core\Model\Resource\HelperPool'],
    ['Magento\Core\Model\Resource\Transaction', 'Magento\Framework\DB\Transaction'],
    ['Magento\Catalog\Model\Resource\Helper\Mysql4', 'Magento\Catalog\Model\Resource\Helper'],
    ['Magento\Eav\Model\Resource\Helper\Mysql4', 'Magento\Eav\Model\Resource\Helper'],
    [
        'Magento\Eav\Model\Entity\Attribute\Backend\Array',
        'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend'
    ],
    ['Magento\Sales\Model\Resource\Helper\HelperInterface', 'Magento\Sales\Model\Resource\HelperInterface'],
    ['Magento\Reports\Model\Resource\Helper\HelperInterface', 'Magento\Reports\Model\Resource\HelperInterface'],
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
    ['Magento\Poll\Block\ActivePoll'],
    ['Magento\Poll\Controller\Vote'],
    ['Magento\Poll\Helper\Data'],
    ['Magento\Poll\Model\Poll'],
    ['Magento\Poll\Model\Poll\Answer'],
    ['Magento\Poll\Model\Poll\Vote'],
    ['Magento\Poll\Model\Resource\Poll'],
    ['Magento\Poll\Model\Resource\Poll\Answer'],
    ['Magento\Poll\Model\Resource\Poll\Answer\Collection'],
    ['Magento\Poll\Model\Resource\Poll\Collection'],
    ['Magento\Poll\Model\Resource\Poll\Vote'],
    ['Magento\Framework\Backup'],
    ['Magento\Core\Controller\Front\Router'],
    ['Magento\Core\Controller\Request\HttpProxy'],
    ['Magento\Core\Controller\Response\Http', 'Magento\Framework\App\Response\Http'],
    ['Magento\Core\Controller\Varien\Action\Forward', 'Magento\Framework\App\Action\Forward'],
    ['Magento\Core\Controller\Varien\Action\Redirect', 'Magento\Framework\App\Action\Redirect'],
    ['Magento\Core\Controller\Varien\DispatchableInterface'],
    ['Magento\Core\Controller\Varien\Front', 'Magento\Framework\App\FrontController'],
    ['Magento\Core\Controller\FrontInterface', 'Magento\Framework\App\FrontControllerInterface'],
    ['Magento\Core\Model\App\Handler'],
    ['Magento\Core\Model\App\Proxy'],
    ['Magento\Core\Model\Event\Config\SchemaLocator', 'Magento\Framework\Event\Config\SchemaLocator'],
    ['Magento\Core\Controller\Varien\Router\AbstractRouter'],
    ['Magento\Core\Controller\Varien\AbstractAction'],
    ['Magento\Core\Controller\Varien\Exception'],
    ['Magento\Framework\HTTP\HandlerFactory'],
    ['Magento\Core\Controller\Request\Http'],
    ['Magento\Core\Controller\Varien\Router\DefaultRouter'],
    ['Magento\Core\Model\NoRouteHandlerList'],
    ['Magento\Core\Controller\Varien\Action\Factory'],
    ['Magento\Core\Model\Config\Loader\Primary'],
    ['Magento\Core\Model\Config\AbstractStorage'],
    ['Magento\Core\Model\Config\Loader'],
    ['Magento\Core\Model\Config\LoaderInterface'],
    ['Magento\Core\Model\Config\Primary'],
    ['Magento\Core\Model\Config\Storage'],
    ['Magento\Core\Model\Config\StorageInterface'],
    ['Magento\Core\Model\Dir'],
    ['Magento\Core\Model\ModuleList'],
    ['Magento\Core\Model\ModuleListInterface'],
    ['Magento\Core\Model\RouterList'],
    ['Magento\Core\Model\App\State'],
    ['Magento\Core\Model\App'],
    ['Magento\Core\Model\Event\Config\Converter'],
    ['Magento\Core\Model\Event\Config\Data'],
    ['Magento\Core\Model\Event\Config\Reader'],
    ['Magento\Core\Model\Event\Invoker\InvokerDefault'],
    ['Magento\Core\Model\Event\Config'],
    ['Magento\Core\Model\Event\ConfigInterface'],
    ['Magento\Core\Model\Event\InvokerInterface'],
    ['Magento\Core\Model\Event\Manager'],
    ['Magento\Framework\HTTP\Handler\Composite'],
    ['Magento\Framework\HTTP\HandlerInterface'],
    ['Magento\Backend\Model\Request\PathInfoProcessor'],
    ['Magento\Backend\Model\Router\NoRouteHandler'],
    ['Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Super\Config\Simple'],
    ['Magento\Core\Model\Request\PathInfoProcessor', 'Magento\Store\App\Request\PathInfoProcessor'],
    ['Magento\Core\Model\Request\RewriteService'],
    ['Magento\Core\Model\Router\NoRouteHandler'],
    ['Magento\Core\Model\Resource\SetupFactory'],
    ['Magento\Core\Model\Dir\Verification'],
    ['Magento\Core\Model\Module\Declaration\Converter\Dom'],
    ['Magento\Core\Model\Module\Declaration\Reader\Filesystem'],
    ['Magento\Core\Model\Module\Dir'],
    ['Magento\Core\Model\Module\Declaration\FileResolver'],
    ['Magento\Core\Model\Module\Declaration\SchemaLocator'],
    ['Magento\Core\Model\Module\Dir\ReverseResolver'],
    ['Magento\Core\Model\Module\ResourceResolver'],
    ['Magento\Core\Model\Module\ResourceResolverInterface'],
    ['Magento\Core\Model\Resource\SetupInterface'],
    ['Magento\Core\Model\Db\UpdaterInterface'],
    ['Magento\Core\Model\Router\NoRouteHandlerInterface'],
    ['Magento\Core\Model\UrlInterface'],
    ['Magento\Sales\Model\AdminOrder'],
    ['Magento\Sales\Model\AdminOrder\Random'],
    ['Magento\Sales\Model\Resource\Order\Attribute\Backend\Parent'],
    ['Magento\Sales\Model\Resource\Order\Creditmemo\Attribute\Backend\Parent'],
    ['Magento\Sales\Model\Resource\Order\Invoice\Attribute\Backend\Parent'],
    ['Magento\Sales\Model\Resource\Order\Shipment\Attribute\Backend\Parent'],
    ['Magento\Sales\Model\Resource\Quote\Address\Attribute\Backend\Parent'],
    ['Magento\Core\Helper\Http'],
    ['Magento\Core\Model\ThemeInterface', 'Magento\Framework\View\Design\ThemeInterface'],
    ['Magento\Core\Model\View\DesignInterface', 'Magento\Framework\View\DesignInterface'],
    ['Magento\Core\Model\Layout\Element', 'Magento\Framework\View\Layout\Element'],
    ['Magento\Core\Helper\Hint', 'Magento\Backend\Block\Store\Switcher'],
    [
        'Magento\Core\Model\Design\Fallback\Rule\ModularSwitch',
        'Magento\Framework\View\Design\Fallback\Rule\ModularSwitch'
    ],
    [
        'Magento\Core\Model\Design\Fallback\Rule\RuleInterface',
        'Magento\Framework\View\Design\Fallback\Rule\RuleInterface'
    ],
    ['Magento\Core\Model\Design\Fallback\Rule\Simple', 'Magento\Framework\View\Design\Fallback\Rule\Simple'],
    ['Magento\Core\Model\Design\Fallback\Factory', 'Magento\Framework\View\Design\Fallback\RulePool'],
    ['Magento\Core\Model\Design\FileResolution\Strategy\Fallback\CachingProxy'],
    ['Magento\Framework\View\Design\FileResolution\Strategy\View\NotifiableInterface'],
    ['Magento\Framework\View\Design\FileResolution\Strategy\View\FileInterface'],
    ['Magento\Framework\View\Design\FileResolution\Strategy\View\LocaleInterface'],
    ['Magento\Framework\View\Design\FileResolution\Strategy\View\ViewInterface'],
    ['Magento\Framework\View\Design\FileResolution\Strategy\Fallback\CachingProxy'],
    [
        'Magento\Core\Model\Design\FileResolution\Strategy\Fallback',
        'Magento\Framework\View\Design\FileResolution\Fallback\{File,ViewFile,LocaleFile,TemplateFile}'
    ],
    ['Magento\Core\Model\Design\FileResolution\StrategyPool'],
    ['Magento\Framework\View\Design\FileResolution\StrategyPool'],
    ['Magento\Core\Model\Layout\File', 'Magento\Framework\View\File'],
    ['Magento\Core\Model\Layout\File\Factory', 'Magento\Framework\View\File\Factory'],
    ['Magento\Core\Model\Layout\File\FileList\Factory', 'Magento\Framework\View\File\FileList\Factory'],
    ['Magento\Core\Model\Layout\File\ListFile', 'Magento\Framework\View\File\FileList'],
    [
        'Magento\Core\Model\Layout\File\Source\Aggregated',
        'Magento\Framework\View\Layout\File\Collector\Aggregated'
    ],
    ['Magento\Core\Model\Layout\File\Source\Base', 'Magento\Framework\View\Layout\File\Source\Base'],
    [
        'Magento\Core\Model\Layout\File\Source\Decorator\ModuleDependency',
        'Magento\Framework\View\File\Collector\Decorator\ModuleDependency'
    ],
    [
        'Magento\Core\Model\Layout\File\Source\Decorator\ModuleOutput',
        'Magento\Framework\View\File\Collector\Decorator\ModuleOutput'
    ],
    ['Magento\Core\Model\Layout\File\Source\Override\Base', 'Magento\Framework\View\Layout\File\Override\Base'],
    ['Magento\Core\Model\Layout\File\Source\Override\Theme', 'Magento\Framework\View\Layout\File\Override\Theme'],
    ['Magento\Core\Model\Layout\File\Source\Theme', 'Magento\Framework\View\Layout\File\Source\Theme'],
    ['Magento\Core\Model\Layout\File\SourceInterface', 'Magento\Framework\View\File\CollectorInterface'],
    ['Magento\Core\Model\LayoutFactory', 'Magento\Framework\View\LayoutFactory'],
    ['Magento\Core\Model\TemplateEngine\EngineInterface', 'Magento\Framework\View\TemplateEngineInterface'],
    ['Magento\Core\Model\TemplateEngine\Factory', 'Magento\Framework\View\TemplateEngineFactory'],
    ['Magento\Core\Model\TemplateEngine\Php', 'Magento\Framework\View\TemplateEngine\Php'],
    ['Magento\Core\Model\TemplateEngine\Pool', 'Magento\Framework\View\TemplateEnginePool'],
    ['Magento\Media\Model\File\Image'],
    ['Magento\Media\Model\Image'],
    ['Magento\Media\Helper\Data'],
    [
        'Magento\Adminhtml\Block\Promo\Catalog\Edit\Form',
        'Magento\CatalogRule\Block\Adminhtml\Promo\Catalog\Edit\Form'
    ],
    [
        'Magento\Adminhtml\Block\Promo\Catalog\Edit\Js',
        'Magento\CatalogRule\Block\Adminhtml\Promo\Catalog\Edit\Js'
    ],
    [
        'Magento\Adminhtml\Block\Promo\Catalog\Edit\Tab\Actions',
        'Magento\CatalogRule\Block\Adminhtml\Promo\Catalog\Edit\Tab\Actions'
    ],
    [
        'Magento\Adminhtml\Block\Promo\Catalog\Edit\Tab\Conditions',
        'Magento\CatalogRule\Block\Adminhtml\Promo\Catalog\Edit\Tab\Conditions'
    ],
    [
        'Magento\Adminhtml\Block\Promo\Catalog\Edit\Tab\Main',
        'Magento\CatalogRule\Block\Adminhtml\Promo\Catalog\Edit\Tab\Main'
    ],
    [
        'Magento\Adminhtml\Block\Promo\Catalog\Edit\Tabs',
        'Magento\CatalogRule\Block\Adminhtml\Promo\Catalog\Edit\Tabs'
    ],
    ['Magento\Adminhtml\Block\Promo\Catalog\Edit', 'Magento\CatalogRule\Block\Adminhtml\Promo\Catalog\Edit'],
    ['Magento\Adminhtml\Block\Promo\Catalog', 'Magento\CatalogRule\Block\Adminhtml\Promo\Catalog'],
    [
        'Magento\Adminhtml\Block\Promo\Widget\Chooser\Sku',
        'Magento\CatalogRule\Block\Adminhtml\Widget\Chooser\Sku'
    ],
    ['Magento\Adminhtml\Block\Promo\Widget\Chooser', 'Magento\CatalogRule\Block\Adminhtml\Widget\Chooser'],
    ['Magento\Adminhtml\Block\Promo\Quote\Edit\Form', 'Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Form'],
    [
        'Magento\Adminhtml\Block\Promo\Quote\Edit\Tab\Actions',
        'Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Actions'
    ],
    [
        'Magento\Adminhtml\Block\Promo\Quote\Edit\Tab\Conditions',
        'Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Conditions'
    ],
    [
        'Magento\Adminhtml\Block\Promo\Quote\Edit\Tab\Coupons\Form',
        'Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons\Form'
    ],
    [
        'Magento\Adminhtml\Block\Promo\Quote\Edit\Tab\Coupons\Grid\Column\Renderer\Used',
        'Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons\Grid\Column\Renderer\Used'
    ],
    [
        'Magento\Adminhtml\Block\Promo\Quote\Edit\Tab\Coupons\Grid',
        'Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons\Grid'
    ],
    [
        'Magento\Adminhtml\Block\Promo\Quote\Edit\Tab\Coupons',
        'Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons'
    ],
    [
        'Magento\Adminhtml\Block\Promo\Quote\Edit\Tab\Labels',
        'Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Labels'
    ],
    [
        'Magento\Adminhtml\Block\Promo\Quote\Edit\Tab\Main\Renderer\Checkbox',
        'Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Main\Renderer\Checkbox'
    ],
    [
        'Magento\Adminhtml\Block\Promo\Quote\Edit\Tab\Main',
        'Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Main'
    ],
    ['Magento\Adminhtml\Block\Promo\Quote\Edit\Tabs', 'Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tabs'],
    ['Magento\Adminhtml\Block\Promo\Quote\Edit', 'Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit'],
    ['Magento\Adminhtml\Block\Promo\Quote', 'Magento\SalesRule\Block\Adminhtml\Promo\Quote'],
    ['Magento\Adminhtml\Controller\Promo\Catalog', 'Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog'],
    ['Magento\Adminhtml\Controller\Promo\Quote', 'Magento\SalesRule\Controller\Adminhtml\Promo\Quote'],
    ['Magento\Adminhtml\Controller\Promo\Widget', 'Magento\CatalogRule\Controller\Adminhtml\Promo\Widget'],
    ['Magento\Adminhtml\Controller\Promo', 'Magento\CatalogRule\Controller\Adminhtml\Promo'],
    ['Magento\Adminhtml\Controller\System\Account', 'Magento\Backend\Controller\Adminhtml\System\Account'],
    ['Magento\Adminhtml\Controller\System\Backup', 'Magento\Backend\Controller\Adminhtml\System\Backup'],
    [
        'Magento\Adminhtml\Controller\System\Config\System\Storage',
        'Magento\Backend\Controller\Adminhtml\System\Config\System\Storage'
    ],
    ['Magento\Adminhtml\Controller\System\Design', 'Magento\Backend\Controller\Adminhtml\System\Design'],
    ['Magento\Adminhtml\Controller\System\Store', 'Magento\Backend\Controller\Adminhtml\System\Store'],
    ['Magento\Adminhtml\Controller\System\Variable', 'Magento\Backend\Controller\Adminhtml\System\Variable'],
    ['Magento\Adminhtml\Block\System\Account\Edit\Form', 'Magento\Backend\Block\System\Account\Edit\Form'],
    ['Magento\Adminhtml\Block\System\Account\Edit', 'Magento\Backend\Block\System\Account\Edit'],
    ['Magento\Adminhtml\Block\System\Cache\Edit', 'Magento\Backend\Block\System\Cache\Edit'],
    ['Magento\Adminhtml\Block\System\Cache\Form', 'Magento\Backend\Block\System\Cache\Form'],
    [
        'Magento\Adminhtml\Block\System\Design\Edit\Tab\General',
        'Magento\Backend\Block\System\Design\Edit\Tab\General'
    ],
    ['Magento\Adminhtml\Block\System\Design\Edit\Tabs', 'Magento\Backend\Block\System\Design\Edit\Tabs'],
    ['Magento\Adminhtml\Block\System\Design\Edit', 'Magento\Backend\Block\System\Design\Edit'],
    ['Magento\Adminhtml\Block\System\Design', 'Magento\Backend\Block\System\Design'],
    ['Magento\Adminhtml\Block\System\Shipping\Ups', 'Magento\Backend\Block\System\Shipping\Ups'],
    ['Magento\Adminhtml\Block\System\Store\Delete\Form', 'Magento\Backend\Block\System\Store\Delete\Form'],
    ['Magento\Adminhtml\Block\System\Store\Delete\Group', 'Magento\Backend\Block\System\Store\Delete\Group'],
    ['Magento\Adminhtml\Block\System\Store\Delete\Website', 'Magento\Backend\Block\System\Store\Delete\Website'],
    ['Magento\Adminhtml\Block\System\Store\Delete', 'Magento\Backend\Block\System\Store\Delete'],
    [
        'Magento\Adminhtml\Block\System\Store\Edit\AbstractForm',
        'Magento\Backend\Block\System\Store\Edit\AbstractForm'
    ],
    [
        'Magento\Adminhtml\Block\System\Store\Edit\Form\Group',
        'Magento\Backend\Block\System\Store\Edit\Form\Group'
    ],
    [
        'Magento\Adminhtml\Block\System\Store\Edit\Form\Store',
        'Magento\Backend\Block\System\Store\Edit\Form\Store'
    ],
    [
        'Magento\Adminhtml\Block\System\Store\Edit\Form\Website',
        'Magento\Backend\Block\System\Store\Edit\Form\Website'
    ],
    ['Magento\Adminhtml\Block\System\Store\Edit', 'Magento\Backend\Block\System\Store\Edit'],
    [
        'Magento\Adminhtml\Block\System\Store\Grid\Render\Group',
        'Magento\Backend\Block\System\Store\Grid\Render\Group'
    ],
    [
        'Magento\Adminhtml\Block\System\Store\Grid\Render\Store',
        'Magento\Backend\Block\System\Store\Grid\Render\Store'
    ],
    [
        'Magento\Adminhtml\Block\System\Store\Grid\Render\Website',
        'Magento\Backend\Block\System\Store\Grid\Render\Website'
    ],
    ['Magento\Adminhtml\Block\System\Store\Store', 'Magento\Backend\Block\System\Store\Store'],
    ['Magento\Adminhtml\Block\System\Variable\Edit\Form', 'Magento\Backend\Block\System\Variable\Edit\Form'],
    ['Magento\Adminhtml\Block\System\Variable\Edit', 'Magento\Backend\Block\System\Variable\Edit'],
    ['Magento\Adminhtml\Block\System\Variable', 'Magento\Backend\Block\System\Variable'],
    [
        'Magento\Adminhtml\Block\Checkout\Agreement\Edit\Form',
        'Magento\CheckoutAgreements\Block\Adminhtml\Agreement\Edit\Form'
    ],
    [
        'Magento\Adminhtml\Block\Checkout\Agreement\Edit',
        'Magento\CheckoutAgreements\Block\Adminhtml\Agreement\Edit'
    ],
    [
        'Magento\Adminhtml\Block\Checkout\Agreement\Grid',
        'Magento\CheckoutAgreements\Block\Adminhtml\Agreement\Grid'
    ],
    ['Magento\Adminhtml\Block\Checkout\Agreement', 'Magento\CheckoutAgreements\Block\Adminhtml\Agreement'],
    ['Magento\Adminhtml\Controller\Checkout\Agreement', 'Magento\Checkout\Controller\Adminhtml\Agreement'],
    ['Magento\Core\Model\View\PublicFilesManagerInterface', 'Magento\Framework\View\Asset\SourceFileInterface'],
    ['Magento\Core\Model\View\DeployedFilesManager', 'Magento\Framework\View\AssetInterface'],
    ['Magento\Framework\View\DeployedFilesManager', 'Magento\Framework\View\AssetInterface'],
    ['Magento\Core\Model\View\Publisher', 'Magento\Framework\View\Publisher'],
    ['Magento\Core\Model\View\FileSystem', 'Magento\Framework\View\FileSystem'],
    ['Magento\Core\Model\View\Service', 'Magento\Framework\View\Asset\Repository'],
    ['Magento\Core\Model\View\Url', 'Magento\Framework\View\Asset\Repository'],
    ['Magento\Core\Model\View\Config', 'Magento\Framework\View\Config'],
    ['Magento\Core\Model\Image\Factory', 'Magento\Framework\Image\Factory'],
    ['Magento\Core\Model\Theme\Image', 'Magento\Framework\View\Design\Theme\Image'],
    ['Magento\Core\Model\Theme\FlyweightFactory', 'Magento\Framework\View\Design\Theme\FlyweightFactory'],
    ['Magento\Core\Model\Image\AdapterFactory', 'Magento\Framework\Image\AdapterFactory'],
    ['Magento\Core\Model\EntryPoint\Cron', 'Magento\Framework\App\Cron'],
    [
        'Magento\Checkout\Block\Cart\Item\Renderer\Grouped',
        'Magento\GroupedProduct\Block\Cart\Item\Renderer\Grouped'
    ],
    ['Magento\Log\Model\EntryPoint\Shell', 'Magento\Log\App\Shell'],
    ['Magento\Core\Model\Config\Modules\Reader', 'Magento\Framework\Module\Dir\Reader'],
    ['Magento\Framework\Data\Form\Factory', 'Magento\Framework\Data\FormFactory'],
    ['Magento\Framework\App\Cache\Config', 'Magento\Framework\Cache\Config'],
    ['Magento\Framework\App\Cache\Config\Converter', 'Magento\Framework\Cache\Config\Converter'],
    ['Magento\Framework\App\Cache\Config\Data', 'Magento\Framework\Cache\Config\Data'],
    ['Magento\Framework\App\Cache\Config\Reader', 'Magento\Framework\Cache\Config\Reader'],
    ['Magento\Framework\App\Cache\Config\SchemaLocator', 'Magento\Framework\Cache\Config\SchemaLocator'],
    ['Magento\Core\Model\Fieldset\Config', 'Magento\Framework\Object\Copy\Config'],
    ['Magento\Core\Model\Fieldset\Config\Converter', 'Magento\Framework\Object\Copy\Config\Converter'],
    ['Magento\Core\Model\Fieldset\Config\Data', 'Magento\Framework\Object\Copy\Config\Data'],
    ['Magento\Core\Model\Fieldset\Config\Reader', 'Magento\Framework\Object\Copy\Config\Reader'],
    ['Magento\Core\Model\Fieldset\Config\SchemaLocator', 'Magento\Framework\Object\Copy\Config\SchemaLocator'],
    ['Magento\Core\Model\ModuleManager', 'Magento\Framework\Module\Manager'],
    ['Magento\Core\Model\EntryPoint\Media', 'Magento\Core\App\Media'],
    ['Magento\Core\Controller\Varien\Action', 'Magento\Framework\App\Action\Action'],
    ['Magento\Core\Controller\Varien\Action\Context', 'Magento\Framework\App\Action\Context'],
    ['Magento\Backend\Controller\AbstractAction', 'Magento\Backend\App\AbstractAction'],
    ['Magento\Backend\Controller\Context', 'Magento\Backend\App\Action\Context'],
    ['Magento\Backend\Controller\Adminhtml\Action', 'Magento\Backend\App\Action'],
    ['Magento\Backend\Block\System\Shipping\Ups', 'Magento\Ups\Block\Backend\System\CarrierConfig'],
    ['Magento\Core\Block\Text', 'Magento\Framework\View\Element\Text'],
    ['Magento\Core\Block\Text\ListText', 'Magento\Framework\View\Element\Text\ListText'],
    ['Magento\Core\Block\Text\TextList\Item', 'Magento\Framework\View\Element\Text\TextList\Item'],
    ['Magento\Core\Block\Text\TextList\Link', 'Magento\Framework\View\Element\Text\TextList\Link'],
    ['Magento\Core\Block\Messages', 'Magento\Framework\View\Element\Messages'],
    ['Magento\Core\Model\Message', 'Magento\Framework\Message\Factory'],
    ['Magento\Core\Model\Message\AbstractMessage', 'Magento\Framework\Message\AbstractMessage'],
    ['Magento\Core\Model\Message\Collection', 'Magento\Framework\Message\Collection'],
    ['Magento\Core\Model\Message\CollectionFactory', 'Magento\Framework\Message\CollectionFactory'],
    ['Magento\Core\Model\Message\Error', 'Magento\Framework\Message\Error'],
    ['Magento\Core\Model\Message\Warning', 'Magento\Framework\Message\Warning'],
    ['Magento\Core\Model\Message\Notice', 'Magento\Framework\Message\Notice'],
    ['Magento\Core\Model\Message\Success', 'Magento\Framework\Message\Success'],
    ['Magento\Core\Block\Html\Date', 'Magento\Framework\View\Element\Html\Date'],
    ['Magento\Core\Block\Html\Select', 'Magento\Framework\View\Element\Html\Select'],
    ['Magento\Core\Block\AbstractBlock', 'Magento\Framework\View\Element\AbstractBlock'],
    ['Magento\Core\Block\Template', 'Magento\Framework\View\Element\Template'],
    ['Magento\Core\Block\Html\Calendar', 'Magento\Framework\View\Element\Html\Calendar'],
    ['Magento\Core\Block\Html\Link', 'Magento\Framework\View\Element\Html\Link'],
    ['Magento\Core\Block\Context', 'Magento\Framework\View\Element\Context'],
    ['Magento\Core\Model\Factory\Helper'],
    ['Magento\Framework\App\Helper\HelperFactory'],
    ['Magento\Core\Helper\AbstractHelper', 'Magento\Framework\App\Helper\AbstractHelper'],
    ['Magento\Core\Helper\Context', 'Magento\Framework\App\Helper\Context'],
    ['Magento\Adminhtml\Controller\Report\AbstractReport', 'Magento\Reports\Controller\Adminhtml\AbstractReport'],
    ['Magento\Adminhtml\Controller\Report\Customer', 'Magento\Reports\Controller\Adminhtml\Customer'],
    ['Magento\Adminhtml\Controller\Report\Product', 'Magento\Reports\Controller\Adminhtml\Product'],
    ['Magento\Adminhtml\Controller\Report\Review', 'Magento\Reports\Controller\Adminhtml\Review'],
    ['Magento\Adminhtml\Controller\Report\Sales', 'Magento\Reports\Controller\Adminhtml\Sales'],
    ['Magento\Adminhtml\Controller\Report\Shopcart', 'Magento\Reports\Controller\Adminhtml\Shopcart'],
    ['Magento\Adminhtml\Controller\Report\Statistics', 'Magento\Reports\Controller\Adminhtml\Statistics'],
    [
        'Magento\Adminhtml\Block\Report\Config\Form\Field\MtdStart',
        'Magento\Reports\Block\Adminhtml\Config\Form\Field\MtdStart'
    ],
    [
        'Magento\Adminhtml\Block\Report\Config\Form\Field\YtdStart',
        'Magento\Reports\Block\Adminhtml\Config\Form\Field\YtdStart'
    ],
    ['Magento\Adminhtml\Block\Report\Filter\Form', 'Magento\Reports\Block\Adminhtml\Filter\Form'],
    ['Magento\Adminhtml\Block\Report\Grid\AbstractGrid', 'Magento\Reports\Block\Adminhtml\Grid\AbstractGrid'],
    [
        'Magento\Adminhtml\Block\Report\Grid\Column\Renderer\Blanknumber',
        'Magento\Reports\Block\Adminhtml\Grid\Column\Renderer\Blanknumber'
    ],
    [
        'Magento\Adminhtml\Block\Report\Grid\Column\Renderer\Currency',
        'Magento\Reports\Block\Adminhtml\Grid\Column\Renderer\Currency'
    ],
    [
        'Magento\Adminhtml\Block\Report\Grid\Column\Renderer\Customer',
        'Magento\Reports\Block\Adminhtml\Grid\Column\Renderer\Customer'
    ],
    [
        'Magento\Adminhtml\Block\Report\Grid\Column\Renderer\Product',
        'Magento\Reports\Block\Adminhtml\Grid\Column\Renderer\Product'
    ],
    ['Magento\Adminhtml\Block\Report\Grid\Shopcart', 'Magento\Reports\Block\Adminhtml\Grid\Shopcart'],
    [
        'Magento\Adminhtml\Block\Report\Product\Downloads\Grid',
        'Magento\Reports\Block\Adminhtml\Product\Downloads\Grid'
    ],
    [
        'Magento\Adminhtml\Block\Report\Product\Downloads\Renderer\Purchases',
        'Magento\Reports\Block\Adminhtml\Product\Downloads\Renderer\Purchases'
    ],
    ['Magento\Adminhtml\Block\Report\Product\Downloads', 'Magento\Reports\Block\Adminhtml\Product\Downloads'],
    ['Magento\Adminhtml\Block\Report\Product\Grid', 'Magento\Reports\Block\Adminhtml\Product\Grid'],
    [
        'Magento\Adminhtml\Block\Report\Product\Lowstock\Grid',
        'Magento\Reports\Block\Adminhtml\Product\Lowstock\Grid'
    ],
    ['Magento\Adminhtml\Block\Report\Product\Lowstock', 'Magento\Reports\Block\Adminhtml\Product\Lowstock'],
    ['Magento\Adminhtml\Block\Report\Product\Viewed\Grid', 'Magento\Reports\Block\Adminhtml\Product\Viewed\Grid'],
    ['Magento\Adminhtml\Block\Report\Product\Viewed', 'Magento\Reports\Block\Adminhtml\Product\Viewed'],
    ['Magento\Adminhtml\Block\Report\Product', 'Magento\Reports\Block\Adminhtml\Product'],
    ['Magento\Adminhtml\Block\Report\Review\Customer', 'Magento\Reports\Block\Adminhtml\Review\Customer'],
    ['Magento\Adminhtml\Block\Report\Review\Detail\Grid', 'Magento\Reports\Block\Adminhtml\Review\Detail\Grid'],
    ['Magento\Adminhtml\Block\Report\Review\Detail', 'Magento\Reports\Block\Adminhtml\Review\Detail'],
    ['Magento\Adminhtml\Block\Report\Review\Product', 'Magento\Reports\Block\Adminhtml\Review\Product'],
    [
        'Magento\Adminhtml\Block\Report\Sales\Bestsellers\Grid',
        'Magento\Reports\Block\Adminhtml\Sales\Bestsellers\Grid'
    ],
    ['Magento\Adminhtml\Block\Report\Sales\Bestsellers', 'Magento\Reports\Block\Adminhtml\Sales\Bestsellers'],
    ['Magento\Adminhtml\Block\Report\Sales\Coupons\Grid', 'Magento\Reports\Block\Adminhtml\Sales\Coupons\Grid'],
    ['Magento\Adminhtml\Block\Report\Sales\Coupons', 'Magento\Reports\Block\Adminhtml\Sales\Coupons'],
    [
        'Magento\Adminhtml\Block\Report\Sales\Grid\Column\Renderer\Date',
        'Magento\Reports\Block\Adminhtml\Sales\Grid\Column\Renderer\Date'
    ],
    ['Magento\Adminhtml\Block\Report\Sales\Invoiced\Grid', 'Magento\Reports\Block\Adminhtml\Sales\Invoiced\Grid'],
    ['Magento\Adminhtml\Block\Report\Sales\Invoiced', 'Magento\Reports\Block\Adminhtml\Sales\Invoiced'],
    ['Magento\Adminhtml\Block\Report\Sales\Refunded\Grid', 'Magento\Reports\Block\Adminhtml\Sales\Refunded\Grid'],
    ['Magento\Adminhtml\Block\Report\Sales\Refunded', 'Magento\Reports\Block\Adminhtml\Sales\Refunded'],
    ['Magento\Adminhtml\Block\Report\Sales\Sales\Grid', 'Magento\Reports\Block\Adminhtml\Sales\Sales\Grid'],
    ['Magento\Adminhtml\Block\Report\Sales\Sales', 'Magento\Reports\Block\Adminhtml\Sales\Sales'],
    ['Magento\Adminhtml\Block\Report\Sales\Shipping\Grid', 'Magento\Reports\Block\Adminhtml\Sales\Shipping\Grid'],
    ['Magento\Adminhtml\Block\Report\Sales\Shipping', 'Magento\Reports\Block\Adminhtml\Sales\Shipping'],
    ['Magento\Adminhtml\Block\Report\Sales\Tax\Grid', 'Magento\Reports\Block\Adminhtml\Sales\Tax\Grid'],
    ['Magento\Adminhtml\Block\Report\Sales\Tax', 'Magento\Reports\Block\Adminhtml\Sales\Tax'],
    ['Magento\Adminhtml\Block\Report\Search', 'Magento\Search\Block\Adminhtml\Reports\Search'],
    [
        'Magento\Adminhtml\Block\Report\Shopcart\Abandoned\Grid',
        'Magento\Reports\Block\Adminhtml\Shopcart\Abandoned\Grid'
    ],
    ['Magento\Adminhtml\Block\Report\Shopcart\Abandoned', 'Magento\Reports\Block\Adminhtml\Shopcart\Abandoned'],
    [
        'Magento\Adminhtml\Block\Report\Shopcart\Customer\Grid',
        'Magento\Reports\Block\Adminhtml\Shopcart\Customer\Grid'
    ],
    ['Magento\Adminhtml\Block\Report\Shopcart\Customer', 'Magento\Reports\Block\Adminhtml\Shopcart\Customer'],
    [
        'Magento\Adminhtml\Block\Report\Shopcart\Product\Grid',
        'Magento\Reports\Block\Adminhtml\Shopcart\Product\Grid'
    ],
    ['Magento\Adminhtml\Block\Report\Shopcart\Product', 'Magento\Reports\Block\Adminhtml\Shopcart\Product'],
    ['Magento\Adminhtml\Block\Report\Wishlist\Grid', 'Magento\Reports\Block\Adminhtml\Wishlist\Grid'],
    ['Magento\Adminhtml\Block\Report\Wishlist', 'Magento\Reports\Block\Adminhtml\Wishlist'],
    ['Magento\Backend\Helper\Addresses'],
    ['Magento\Core\Model\Cookie', 'Magento\Framework\Stdlib\Cookie'],
    ['Magento\Core\Model\Logger', 'Psr\Log\LoggerInterface'],
    ['Magento\Core\Block\Template\Context', 'Magento\Framework\View\Element\Template\Context'],
    ['Magento\Page\Block\Template\Container'],
    ['Magento\Page\Block\Redirect', 'Magento\Framework\View\Element\Redirect'],
    ['Magento\Page\Block\Js\Translate'],
    ['Magento\Page\Block\Js\Components', 'Magento\Framework\View\Element\Js\Components'],
    ['Magento\Page\Block\Js\Cookie', 'Magento\Framework\View\Element\Js\Cookie'],
    ['Magento\Page\Block\Html', 'Magento\Theme\Block\Html'],
    ['Magento\Page\Block\Html\Breadcrumbs', 'Magento\Theme\Block\Html\Breadcrumbs'],
    ['Magento\Page\Block\Html\Footer', 'Magento\Theme\Block\Html\Footer'],
    ['Magento\Page\Block\Html\Head', 'Magento\Theme\Block\Html\Head'],
    ['Magento\Page\Block\Html\Header', 'Magento\Theme\Block\Html\Header'],
    ['Magento\Page\Block\Html\Notices', 'Magento\Theme\Block\Html\Notices'],
    ['Magento\Page\Block\Html\Pager', 'Magento\Theme\Block\Html\Pager'],
    ['Magento\Page\Block\Html\Title', 'Magento\Theme\Block\Html\Title'],
    ['Magento\Page\Block\Html\Topmenu', 'Magento\Theme\Block\Html\Topmenu'],
    ['Magento\Page\Block\Html\Welcome', 'Magento\Theme\Block\Html\Welcome'],
    ['Magento\Page\Helper\Layout', 'Magento\Theme\Helper\Layout'],
    ['Magento\Page\Model\Source\Layout', 'Magento\Theme\Model\Layout\Source\Layout'],
    ['Magento\Page\Model\Config\Converter', 'Magento\Theme\Model\Layout\Config\Converter'],
    ['Magento\Page\Model\Config\Reader', 'Magento\Theme\Model\Layout\Config\Reader'],
    ['Magento\Page\Model\Config\SchemaLocator', 'Magento\Theme\Model\Layout\Config\SchemaLocator'],
    ['Magento\Page\Helper\Data'],
    ['Magento\Page\Helper\Html'],
    ['Magento\Page\Helper\Robots'],
    ['Magento\Core\Model\Page'],
    ['Magento\Core\Model\Page\Asset\AssetInterface', 'Magento\Framework\View\Asset\AssetInterface'],
    ['Magento\Core\Model\Page\Asset\Collection', 'Magento\Framework\View\Asset\Collection'],
    ['Magento\Core\Model\Page\Asset\LocalInterface', 'Magento\Framework\View\Asset\LocalInterface'],
    ['Magento\Core\Model\Page\Asset\MergeService', 'Magento\Framework\View\Asset\MergeService'],
    [
        'Magento\Core\Model\Page\Asset\MergeStrategy\Checksum',
        'Magento\Framework\View\Asset\MergeStrategy\Checksum'
    ],
    ['Magento\Core\Model\Page\Asset\MergeStrategy\Direct', 'Magento\Framework\View\Asset\MergeStrategy\Direct'],
    [
        'Magento\Core\Model\Page\Asset\MergeStrategy\FileExists',
        'Magento\Framework\View\Asset\MergeStrategy\FileExists'
    ],
    [
        'Magento\Core\Model\Page\Asset\MergeStrategyInterface',
        'Magento\Framework\View\Asset\MergeStrategyInterface'
    ],
    ['Magento\Core\Model\Page\Asset\MergeableInterface', 'Magento\Framework\View\Asset\MergeableInterface'],
    ['Magento\Core\Model\Page\Asset\Merged', 'Magento\Framework\View\Asset\Merged'],
    ['Magento\Core\Model\Page\Asset\Minified', 'Magento\Framework\View\Asset\Minified'],
    ['Magento\Core\Model\Page\Asset\MinifyService', 'Magento\Framework\View\Asset\MinifyService'],
    ['Magento\Core\Model\Page\Asset\PublicFile', 'Magento\Framework\View\Asset\PublicFile'],
    ['Magento\Core\Model\Page\Asset\Remote', 'Magento\Framework\View\Asset\Remote'],
    ['Magento\Core\Model\Page\Asset\ViewFile', 'Magento\Framework\View\Asset\File'],
    ['Magento\Page\Block\Html\Head\AssetBlock', 'Magento\Theme\Block\Html\Head\AssetBlockInterface'],
    ['Magento\Page\Block\Html\Head\Css', 'Magento\Theme\Block\Html\Head\Css'],
    ['Magento\Page\Block\Html\Head\Link', 'Magento\Theme\Block\Html\Head\Link'],
    ['Magento\Page\Block\Html\Head\Script', 'Magento\Theme\Block\Html\Head\Script'],
    ['Magento\Page\Model\Asset\GroupedCollection', 'Magento\Framework\View\Asset\GroupedCollection'],
    ['Magento\Page\Model\Asset\PropertyGroup', 'Magento\Framework\View\Asset\PropertyGroup'],
    ['Magento\Page\Block\Template\Links\Block'],
    ['Magento\Page\Block\Link\Current', 'Magento\Framework\View\Element\Html\Link\Current'],
    ['Magento\Page\Block\Links', 'Magento\Framework\View\Element\Html\Links'],
    ['Magento\Page\Block\Link', 'Magento\Framework\View\Element\Html\Link'],
    [
        'Magento\Core\Model\Layout\Argument\HandlerInterface',
        'Magento\Framework\View\Layout\Argument\HandlerInterface'
    ],
    ['Magento\Core\Model\Layout\Argument\HandlerFactory', 'Magento\Framework\View\Layout\Argument\HandlerFactory'],
    ['Magento\Core\Model\Theme\Label', 'Magento\Framework\View\Design\Theme\Label'],
    ['Magento\Core\Model\Theme\LabelFactory', 'Magento\Framework\View\Design\Theme\LabelFactory'],
    ['Magento\Core\Model\DesignLoader', 'Magento\Framework\View\DesignLoader'],
    ['Magento\Page\Block\Switcher', 'Magento\Store\Block\Switcher'],
    ['Magento\Core\Model\Layout\PageType\Config', 'Magento\Framework\View\Layout\PageType\Config'],
    [
        'Magento\Core\Model\Layout\PageType\Config\Converter',
        'Magento\Framework\View\Layout\PageType\Config\Converter'
    ],
    ['Magento\Core\Model\Layout\PageType\Config\Reader', 'Magento\Framework\View\Layout\PageType\Config\Reader'],
    [
        'Magento\Core\Model\Layout\PageType\Config\SchemaLocator',
        'Magento\Framework\View\Layout\PageType\Config\SchemaLocator'
    ],
    ['Magento\Core\Model\Theme\CopyService', 'Magento\Theme\Model\CopyService'],
    ['Magento\Core\Model\Resource\Session', 'Magento\Framework\Session\SaveHandler\DbTable'],
    ['Magento\Core\Model\Session\Exception', 'Magento\Framework\Session\Exception'],
    ['Magento\Core\Model\Session\Context'],
    ['Magento\Core\Model\Session\AbstractSession', 'Magento\Framework\Session\SessionManager'],
    ['Magento\Catalog\Model\Resource\Convert'],
    ['Magento\Reminder\Model\Resource\HelperFactory'],
    ['Magento\Reminder\Model\Resource\Helper'],
    ['Magento\Core\Model\ConfigInterface', 'Magento\Framework\App\Config\ScopeConfigInterface'],
    ['Magento\CatalogRule\Block\Adminhtml\Promo\Widget\Chooser'],
    [
        'Magento\Catalog\Model\Product\Type\Grouped\Backend',
        'Magento\GroupedProduct\Model\Product\Type\Grouped\Backend'
    ],
    [
        'Magento\Catalog\Model\Product\Type\Grouped\Price',
        'Magento\GroupedProduct\Model\Product\Type\Grouped\Price'
    ],
    [
        'Magento\Catalog\Model\Resource\Product\Indexer\Price\Grouped',
        'Magento\GroupedProduct\Model\Resource\Product\Indexer\Price\Grouped'
    ],
    [
        'Magento\Catalog\Model\Resource\Product\Type\Grouped\AssociatedProductsCollection',
        'Magento\GroupedProduct\Model\Resource\Product\Type\Grouped\AssociatedProductsCollection'
    ],
    ['Magento\Catalog\Model\Product\Type\Grouped', 'Magento\GroupedProduct\Model\Product\Type\Grouped'],
    [
        'Magento\Catalog\Block\Adminhtml\Product\Composite\Fieldset\Grouped',
        'Magento\GroupedProduct\Block\Adminhtml\Product\Composite\Fieldset\Grouped'
    ],
    ['Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs\Grouped'],
    [
        'Magento\Catalog\Block\Product\Grouped\AssociatedProducts',
        'Magento\GroupedProduct\Block\Product\Grouped\AssociatedProducts'
    ],
    [
        'Magento\Catalog\Block\Product\Grouped\AssociatedProducts\ListAssociatedProducts',
        'Magento\GroupedProduct\Block\Product\Grouped\AssociatedProducts\ListAssociatedProducts'
    ],
    ['Magento\Catalog\Block\Product\View\Type\Grouped', 'Magento\GroupedProduct\Block\Product\View\Type\Grouped'],
    [
        'Magento\Sales\Block\Adminhtml\Items\Column\Name\Grouped',
        'Magento\GroupedProduct\Block\Adminhtml\Items\Column\Name\Grouped'
    ],
    [
        'Magento\Sales\Model\Order\Pdf\Items\Invoice\Grouped',
        'Magento\GroupedProduct\Model\Order\Pdf\Items\Invoice\Grouped'
    ],
    [
        'Magento\Sales\Block\Order\Item\Renderer\Grouped',
        'Magento\GroupedProduct\Block\Order\Item\Renderer\Grouped'
    ],
    [
        'Magento\ImportExport\Model\Export\Entity\Product\Type\Grouped',
        'Magento\CatalogImportExport\Model\Export\Entity\Product\Type\Grouped'
    ],
    [
        'Magento\ImportExport\Model\Import\Entity\Product\Type\Grouped',
        'Magento\CatalogImportExport\Model\Import\Entity\Product\Type\Grouped'
    ],
    [
        'Magento\GroupedProduct\Model\Export\Entity\Product\Type\Grouped',
        'Magento\CatalogImportExport\Model\Export\Entity\Product\Type\Grouped'
    ],
    [
        'Magento\GroupedProduct\Model\Import\Entity\Product\Type\Grouped',
        'Magento\CatalogImportExport\Model\Import\Entity\Product\Type\Grouped'
    ],
    ['CollFactory', 'CollectionFactory'], // no need to shorten anymore
    [
        'Magento\Shipping\Model\Rate\Result\AbstractResult',
        'Magento\Sales\Model\Quote\Address\RateResult\AbstractResult'
    ],
    ['Magento\Shipping\Model\Rate\Result\Error', 'Magento\Sales\Model\Quote\Address\RateResult\Error'],
    ['Magento\Shipping\Model\Rate\Result\Method', 'Magento\Sales\Model\Quote\Address\RateResult\Method'],
    [
        'Magento\Shipping\Model\Rate\AbstractRate',
        'Magento\Sales\Model\Quote\Address\Rate + Magento\Shipping\Model\CarrierFactory'
    ],
    ['Magento\Shipping\Model\Rate\Request', 'Magento\Sales\Model\Quote\Address\RateRequest'],
    ['Magento\PageCache\Block\Adminhtml\Cache\Additional'],
    ['Magento\PageCache\Model\Control\ControlInterface'],
    ['Magento\PageCache\Model\Control\Zend'],
    ['Magento\PageCache\Model\System\Config\Source\Controls'],
    ['Magento\PageCache\Model\CacheControlFactory'],
    ['Magento\Catalog\Block\Adminhtml\System\Config\Form\Field\Select\Flatcatalog'],
    ['Magento\Catalog\Helper\Category\Flat'],
    ['Magento\Catalog\Model\Category\Indexer\Flat'],
    ['Magento\Framework\Config\Dom\Converter\ArrayConverter'],
    ['Magento\Framework\Acl\Resource\Config\Dom'],
    ['Magento\Validator\Composite\VarienObject', 'Magento\Framework\Validator\Object'],
    ['Magento\GoogleShopping\Helper\Price', 'Magento\Catalog\Model\Product\CatalogPrice'],
    [
        'Magento\Core\Model\Layout\Argument\Handler\ArrayHandler',
        'Magento\Framework\Data\Argument\Interpreter\ArrayType'
    ],
    ['Magento\Core\Model\Layout\Argument\Handler\String', 'Magento\Framework\Data\Argument\Interpreter\String'],
    ['Magento\Core\Model\Layout\Argument\Handler\Number', 'Magento\Framework\Data\Argument\Interpreter\Number'],
    ['Magento\Core\Model\Layout\Argument\Handler\Boolean', 'Magento\Framework\Data\Argument\Interpreter\Boolean'],
    [
        'Magento\Core\Model\Layout\Argument\Handler\Object',
        'Magento\Framework\View\Layout\Argument\Interpreter\Object'
    ],
    [
        'Magento\Core\Model\Layout\Argument\Handler\Options',
        'Magento\Framework\View\Layout\Argument\Interpreter\Options'
    ],
    ['Magento\Core\Model\Layout\Argument\Handler\Url', 'Magento\Framework\View\Layout\Argument\Interpreter\Url'],
    [
        'Magento\Core\Model\Layout\Argument\Handler\Helper',
        'Magento\Framework\View\Layout\Argument\Interpreter\HelperMethod'
    ],
    [
        'Magento\Core\Model\Layout\Argument\AbstractHandler',
        'Magento\Framework\View\Layout\Argument\Interpreter\Decorator\Updater'
    ],
    [
        'Magento\Core\Model\Layout\Argument\Processor',
        'Magento\Framework\View\Layout\Argument\Interpreter\Decorator\Updater'
    ],
    [
        'Magento\Core\Model\Layout\Argument\Updater',
        'Magento\Framework\View\Layout\Argument\Interpreter\Decorator\Updater'
    ],
    [
        'Magento\Core\Model\Layout\Argument\UpdaterInterface',
        'Magento\Framework\View\Layout\Argument\UpdaterInterface'
    ],
    ['Magento\Core\Model\Layout\Filter\Acl', 'Magento\Backend\Model\Layout\Filter\Acl'],
    [
        'Magento\Framework\View\Layout\Argument\HandlerInterface',
        'Magento\Framework\Data\Argument\InterpreterInterface'
    ],
    [
        'Magento\Framework\View\Layout\Argument\HandlerFactory',
        'Magento\Framework\Data\Argument\Interpreter\Composite'
    ],
    ['Magento\Framework\Phrase\Renderer\Factory'],
    ['Magento\Catalog\Model\Category\Indexer\Product'],
    ['Magento\Catalog\Model\Resource\Category\Indexer\Product'],
    ['Magento\Catalog\Model\Index'],
    ['Magento\Catalog\Model\Product\Status', 'Magento\Catalog\Model\Product\Attribute\Source\Status'],
    ['Magento\Catalog\Model\Resource\Product\Status'],
    [
        'Magento\CatalogInventory\Block\Stockqty\Type\Configurable',
        'Magento\ConfigurableProduct\Block\Stockqty\Type\Configurable'
    ],
    [
        'Magento\CatalogInventory\Model\Resource\Indexer\Stock\Configurable',
        'Magento\ConfigurableProduct\Model\Resource\Indexer\Stock\Configurable'
    ],
    [
        'Magento\ImportExport\Model\Export\Entity\Product\Type\Configurable',
        'Magento\CatalogImportExport\Model\Export\Product\Type\Configurable'
    ],
    [
        'Magento\ConfigurableProduct\Model\Export\Entity\Product\Type\Configurable',
        'Magento\CatalogImportExport\Model\Export\Product\Type\Configurable'
    ],
    [
        'Magento\ImportExport\Model\Import\Entity\Product\Type\Configurable',
        'Magento\CatalogImportExport\Model\Import\Product\Type\Configurable'
    ],
    [
        'Magento\ConfigurableProduct\Model\Import\Entity\Product\Type\Configurable',
        'Magento\CatalogImportExport\Model\Import\Product\Type\Configurable'
    ],
    ['Magento\Sales\Block\Adminhtml\Items\Renderer\Configurable'],
    [
        'Magento\Catalog\Model\Resource\Product\Collection\AssociatedProduct',
        'Magento\ConfigurableProduct\Model\Resource\Product\Collection\AssociatedProduct'
    ],
    ['Magento\Catalog\Model\Resource\Product\Collection\AssociatedProductUpdater'],
    ['Magento\Core\Model\Image\Adapter\Config', 'Magento\Framework\Image\Adapter\Config'],
    ['Magento\Core\Model\AbstractShell', 'Magento\Framework\App\AbstractShell'],
    ['Magento\Core\Model\Calculator', 'Magento\Framework\Math\Calculator'],
    ['Magento\Core\Model\Log\Adapter', 'Magento\Framework\Logger\Adapter'],
    ['Magento\Core\Model\Input\Filter', 'Magento\Framework\Filter\Input'],
    ['Magento\Core\Model\Input\Filter\MaliciousCode', 'Magento\Framework\Filter\Input\MaliciousCode'],
    ['Magento\Core\Model\Option\ArrayInterface', 'Magento\Framework\Option\ArrayInterface'],
    ['Magento\Core\Model\Option\ArrayPool', 'Magento\Framework\Option\ArrayPool'],
    ['Magento\Core\Helper\String', 'Magento\Framework\Code\NameBuilder'],
    ['Magento\Core\Model\Context', 'Magento\Framework\Model\Context'],
    ['Magento\Core\Model\Registry', 'Magento\Framework\Registry'],
    ['Magento\Framework\Code\Plugin\InvocationChain'],
    ['Magento\Catalog\Helper\Product\Flat'],
    ['Magento\Catalog\Helper\Flat\AbstractFlat'],
    ['Magento\Core\App\Action\Plugin\Install', 'Magento\Framework\App\Bootstrap'],
    ['Magento\Core\App\Action\Plugin\Session', 'Magento\Core\Block\RequireCookie'],
    [
        'Magento\Core\Model\LocaleInterface',
        'Magento\Framework\Locale\ResolverInterface, Magento\Framework\Locale\CurrencyInterface,' .
        'Magento\Framework\Locale\FormatInterface, Magento\Framework\Stdlib\DateTime\TimezoneInterface'
    ],
    [
        'Magento\Core\Model\Locale',
        'Magento\Framework\Locale\Resolver, Magento\Framework\Locale\Currency, Magento\Framework\Locale\Format, ' .
        'Magento\Framework\Stdlib\DateTime\Timezone, Magento\Framework\Locale\Lists'
    ],
    ['Magento\Framework\Locale\Hierarchy\Config\Converter', 'Magento\Framework\App\Language\Dictionary'],
    ['Magento\Framework\Locale\Hierarchy\Config\FileResolver', 'Magento\Framework\App\Language\Dictionary'],
    ['Magento\Framework\Locale\Hierarchy\Config\Reader', 'Magento\Framework\App\Language\Dictionary'],
    ['Magento\Framework\Locale\Hierarchy\Config\SchemaLocator', 'Magento\Framework\App\Language\Dictionary'],
    ['Magento\Framework\Locale\Hierarchy\Config', 'Magento\Framework\App\Language\Dictionary'],
    ['Magento\Core\Model\Locale\Config', 'Magento\Framework\Locale\Config'],
    ['Magento\Core\Model\Locale\Validator', 'Magento\Framework\Locale\Validator'],
    ['Magento\Core\Model\Date', 'Magento\Framework\Stdlib\DateTime\DateTime'],
    ['Magento\Shipping\Model\Config\Source\Flatrate', 'Magento\OfflineShipping\Model\Config\Source\Flatrate'],
    ['Magento\Shipping\Model\Carrier\Flatrate', 'Magento\OfflineShipping\Model\Carrier\Flatrate'],
    ['Magento\Usa\Block\Adminhtml\Dhl\Unitofmeasure', 'Magento\Dhl\Block\Adminhtml\Unitofmeasure'],
    ['Magento\Usa\Model\Shipping\Carrier\Dhl\International', 'Magento\Dhl\Model\Carrier'],
    [
        'Magento\Usa\Model\Shipping\Carrier\Dhl\International\Source\Method\AbstractMethod',
        'Magento\Dhl\Model\Source\Method\AbstractMethod'
    ],
    [
        'Magento\Usa\Model\Shipping\Carrier\Dhl\International\Source\Method\Doc',
        'Magento\Dhl\Model\Source\Method\Doc'
    ],
    [
        'Magento\Usa\Model\Shipping\Carrier\Dhl\International\Source\Method\Freedoc',
        'Magento\Dhl\Model\Source\Method\Freedoc'
    ],
    [
        'Magento\Usa\Model\Shipping\Carrier\Dhl\International\Source\Method\Freenondoc',
        'Magento\Dhl\Model\Source\Method\Freenondoc'
    ],
    [
        'Magento\Usa\Model\Shipping\Carrier\Dhl\International\Source\Method\Generic',
        'Magento\Dhl\Model\Source\Method\Generic'
    ],
    [
        'Magento\Usa\Model\Shipping\Carrier\Dhl\International\Source\Method\Nondoc',
        'Magento\Dhl\Model\Source\Method\Nondoc'
    ],
    [
        'Magento\Usa\Model\Shipping\Carrier\Dhl\International\Source\Method\Size',
        'Magento\Dhl\Model\Source\Method\Size'
    ],
    [
        'Magento\Usa\Model\Shipping\Carrier\Dhl\International\Source\Method\Unitofmeasure',
        'Magento\Dhl\Model\Source\Method\Unitofmeasure'
    ],
    ['Magento\Usa\Model\Shipping\Carrier\Dhl\AbstractDhl', 'Magento\Dhl\Model\AbstractDhl'],
    ['Magento\Usa\Model\Shipping\Carrier\Dhl'],
    ['Magento\Usa\Model\Shipping\Carrier\Fedex', 'Magento\Fedex\Model\Carrier'],
    ['Magento\Usa\Model\Shipping\Carrier\Fedex\Source\Droppff', 'Magento\Fedex\Model\Source\Droppff'],
    ['Magento\Usa\Model\Shipping\Carrier\Fedex\Source\Freemethod', 'Magento\Fedex\Model\Source\Freemethod'],
    ['Magento\Usa\Model\Shipping\Carrier\Fedex\Source\Generic', 'Magento\Fedex\Model\Source\Generic'],
    ['Magento\Usa\Model\Shipping\Carrier\Fedex\Source\Method', 'Magento\Fedex\Model\Source\Method'],
    ['Magento\Usa\Model\Shipping\Carrier\Fedex\Source\Packaging', 'Magento\Fedex\Model\Source\Packaging'],
    ['Magento\Rma\Model\CarrierFactory'],
    ['Magento\Usa\Helper\Data'],
    ['Magento\Usa\Model\Shipping\Carrier\Ups\Source\Mode'],
    ['Magento\Usa\Model\Shipping\Carrier\Ups\Source\Container', 'Magento\Ups\Model\Config\Source\Container'],
    ['Magento\Usa\Model\Shipping\Carrier\Ups\Source\DestType', 'Magento\Ups\Model\Config\Source\DestType'],
    ['Magento\Usa\Model\Shipping\Carrier\Ups\Source\Freemethod', 'Magento\Ups\Model\Config\Source\Freemethod'],
    ['Magento\Usa\Model\Shipping\Carrier\Ups\Source\Generic', 'Magento\Ups\Model\Config\Source\Generic'],
    ['Magento\Usa\Model\Shipping\Carrier\Ups\Source\Method', 'Magento\Ups\Model\Config\Source\Method'],
    [
        'Magento\Usa\Model\Shipping\Carrier\Ups\Source\OriginShipment',
        'Magento\Ups\Model\Config\Source\OriginShipment'
    ],
    ['Magento\Usa\Model\Shipping\Carrier\Ups\Source\Pickup', 'Magento\Ups\Model\Config\Source\Pickup'],
    ['Magento\Usa\Model\Shipping\Carrier\Ups\Source\Type', 'Magento\Ups\Model\Config\Source\Type'],
    [
        'Magento\Usa\Model\Shipping\Carrier\Ups\Source\Unitofmeasure',
        'Magento\Ups\Model\Config\Source\Unitofmeasure'
    ],
    ['Magento\Usa\Model\Shipping\Carrier\Usps\Source\Container', 'Magento\Usps\Model\Source\Container'],
    ['Magento\Usa\Model\Shipping\Carrier\Usps\Source\Freemethod', 'Magento\Usps\Model\Source\Freemethod'],
    ['Magento\Usa\Model\Shipping\Carrier\Usps\Source\Generic', 'Magento\Usps\Model\Source\Generic'],
    ['Magento\Usa\Model\Shipping\Carrier\Usps\Source\Machinable', 'Magento\Usps\Model\Source\Machinable'],
    ['Magento\Usa\Model\Shipping\Carrier\Usps\Source\Method', 'Magento\Usps\Model\Source\Method'],
    ['Magento\Usa\Model\Shipping\Carrier\Usps\Source\Size', 'Magento\Usps\Model\Source\Size'],
    ['Magento\Usa\Model\Shipping\Carrier\Usps', 'Magento\Usps\Model\Carrier'],
    ['Magento\Usa\Model\Shipping\Carrier\Ups', 'Magento\Ups\Model\Carrier'],
    ['Magento\Usa\Model\Simplexml\Element', 'Magento\Shipping\Model\Simplexml\Element'],
    [
        'Magento\Usa\Model\Shipping\Carrier\AbstractCarrier',
        'Magento\Shipping\Model\Carrier\AbstractCarrierOnline'
    ],
    [
        'Magento\Usa\Model\Shipping\Carrier\AbstractCarrier\Source\Mode',
        'Magento\Shipping\Model\Config\Source\Online\Mode'
    ],
    [
        'Magento\Usa\Model\Shipping\Carrier\AbstractCarrier\Source\Requesttype',
        'Magento\Shipping\Model\Config\Source\Online\Requesttype'
    ],
    ['Magento\Catalog\Helper\Product\Url', 'Magento\Framework\Filter\Translit'],
    ['Magento\Catalog\Model\Product\Indexer\Price'],
    ['Magento\Catalog\Model\Resource\Product\Indexer\Price'],
    ['Magento\PubSub'], // unused library code which was removed
    ['Magento\Outbound'], // unused library code which was removed
    ['Magento\Indexer\Model\Processor\CacheInvalidate', 'Magento\Indexer\Model\Processor\InvalidateCache'],
    [
        'Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Reviews',
        'Magento\Review\Block\Adminhtml\Product\Edit\Tab\Reviews'
    ],
    [
        'Magento\Catalog\Controller\Adminhtml\Product\Review',
        'Magento\Review\Controller\Adminhtml\Product'
    ],
    [
        'Magento\Review\Block\Helper',
        'Magento\Review\Block\Product\ReviewRenderer'
    ],
    [
        'Magento\LauncherInterface',
        'Magento\Framework\AppInterface',
    ],
    ['Magento\Framework\Convert\ConvertException'],
    ['Magento\Framework\Convert\Container\AbstractContainer'],
    ['Magento\Framework\Convert\Mapper\Column'],
    ['Magento\Framework\Convert\Mapper\MapperInterface'],
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
    ['Magento\Core\Model\Template', 'Magento\Email\Model\AbstractTemplate'],
    ['Magento\Core\Helper\Js'],
    ['Magento\Backend\Helper\Media\Js'],
    [
        'Magento\Core\Model\Resource\Url\Rewrite\Collection',
        'Magento\UrlRewrite\Model\Resource\UrlRewriteCollection'
    ],
    [
        'Magento\Core\Model\Resource\Url\Rewrite',
        'Magento\UrlRewrite\Model\Resource\UrlRewrite'
    ],
    [
        'Magento\Core\Model\Url\Rewrite',
        'Magento\UrlRewrite\Model\UrlRewrite'
    ],
    [
        'Magento\Core\Model\Source\Urlrewrite\Options',
        'Magento\UrlRewrite\Model\UrlRewrite\OptionProvider'
    ],
    [
        'Magento\Core\Model\Source\Urlrewrite\Types',
        'Magento\UrlRewrite\Model\UrlRewrite\TypeProvider'
    ],
    [
        'Magento\Core\Helper\Url\Rewrite',
        'Magento\UrlRewrite\Helper\UrlRewrite'
    ],
    [
        'Magento\Core\App\FrontController\Plugin\UrlRewrite',
        'Magento\UrlRewrite\App\FrontController\Plugin\UrlRewrite'
    ],
    [
        'Magento\Core\App\Request\RewriteService',
        'Magento\UrlRewrite\App\Request\RewriteService'
    ],
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
    ['Magento\Core\Model\System\Store', 'Magento\Store\Model\System\Store'],
    ['Magento\Core\Model\Website', 'Magento\Store\Model\Website'],
    ['Magento\Core\Model\Website\Factory', 'Magento\Store\Model\WebsiteFactory'],
    ['Magento\Framework\App\ReinitableConfigInterface', 'Magento\Framework\App\Config\ReinitableConfigInterface'],
    ['Magento\BaseScopeInterface', 'Magento\Framework\App\ScopeInterface'],
    ['Magento\BaseScopeResolverInterface', 'Magento\Framework\App\ScopeResolverInterface'],
    ['Magento\Framework\Locale\ScopeConfigInterface'],
    ['Magento\Framework\StoreManagerInterface', 'Magento\Store\Model\StoreManagerInterface'],
    ['Magento\Core\Model\Module\Output\Config', 'Magento\Framework\Module\Output\Config'],
    ['Magento\Core\Model\Resource\Setup\Context', 'Magento\Framework\Module\Setup\Context'],
    ['Magento\Core\Model\Resource\Setup\Migration', 'Magento\Framework\Module\Setup\Migration'],
    ['Magento\Core\Model\Resource\Setup\Generic'],
    ['Magento\Newsletter\Model\Resource\Setup'],
    ['Magento\SalesRule\Model\Resource\Setup'],
    ['Magento\Core\Model\Session', 'Magento\Framework\Session\Generic'],
    ['Magento\Core\Model\Session\Config', 'Magento\Framework\Session\Config'],
    ['Magento\Core\Model\Session\SidResolver', 'Magento\Framework\Session\SidResolver'],
    ['Magento\Core\Model\Session\Validator', 'Magento\Framework\Session\Validator'],
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
    ['Magento\Sales\Block\Adminhtml\Invoice\Grid'],
    ['Magento\Sales\Block\Adminhtml\Shipment\Grid'],
    ['Magento\Sales\Block\Adminhtml\Creditmemo\Grid'],
    ['Magento\Sales\Block\Adminhtml\Transactions\Grid'],
    ['Magento\Sales\Block\Adminhtml\Transactions\Child\Grid'],
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
    ['Magento\ObjectManager', 'Magento\Framework\ObjectManagerInterface'],
    ['Magento\Translate', 'Magento\Framework\Translate'],
    ['Magento\TranslateInterface', 'Magento\Framework\TranslateInterface'],
    ['Magento\Locale', 'Magento\Framework\Locale'],
    ['Magento\LocaleFactory', 'Magento\Framework\LocaleFactory'],
    ['Magento\Integration\Model\Oauth\Token\Factory', 'Magento\Integration\Model\Oauth\TokenFactory'],
    ['Magento\LocaleInterface', 'Magento\Framework\LocaleInterface'],
    ['Magento\Logger', 'Psr\Log\LoggerInterface'],
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
    ['Magento\Customer\Service\V1\CustomerAccountService'],
    ['Magento\Customer\Service\V1\CustomerAccountServiceInterface'],
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
    ['Magento\Framework\Stdlib\Cookie', 'Magento\Framework\Stdlib\CookieManagerInterface'],
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
    ['Magento\Install\Model\Installer\AbstractInstaller'],
    ['Magento\Install\App\Action\Plugin\Install'],
    ['\Magento\Cron\App\Cron\Plugin\ApplicationInitializer', 'Magento\Framework\App\Bootstrap'],
    ['Magento\Framework\App\Error\Handler', 'Magento\Framework\App\Http'],
    ['Magento\Framework\App\State\MaintenanceMode', 'Magento\Framework\App\MaintenanceMode'],
    ['Magento\Framework\Error\Handler', 'Magento\Framework\App\ErrorHandler'],
    ['Magento\Framework\Error\HandlerInterface', 'Magento\Framework\App\ErrorHandler'],
    ['Magento\Index'],
    ['Magento\Catalog\Model\Resource\Product\Indexer\Eav'],
    ['\Magento\Framework\Api\Eav\AbstractObject', 'Magento\Framework\Api\AbstractExtensibleObject'],
    ['\Magento\Framework\Api\AbstractObject', 'Magento\Framework\Api\AbstractSimpleObject'],
    [
        '\Magento\Framework\Api\Eav\AbstractObjectBuilder',
        'Magento\Framework\Api\ExtensibleObjectBuilder'
    ],
    [
        '\Magento\Framework\Api\AbstractObjectBuilder',
        'Magento\Framework\Api\AbstractSimpleObjectBuilder'
    ],
    ['Magento\Catalog\Block\Product'],
    ['\Magento\Sales\Model\Observer'],
    ['\Magento\Install\Block\Begin'],
    ['\Magento\Checkout\Service\V1\QuoteLoader', '\Magento\Sales\Model\QuoteRepository'],
    ['Magento\PageCache\Model\Observer'],
    ['Magento\Catalog\Model\Layer\Filter\Price\Algorithm', 'Magento\Framework\Search\Dynamic\Algorithm'],
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
    ['Magento\Checkout\Controller\Onepage\Progress'],
    ['Magento\Checkout\Controller\Onepage\GetAdditional'],
    ['Magento\Framework\App\Filesystem', 'Magento\Framework\Filesystem'],
    ['Magento\TestFramework\App\Filesystem\DirectoryList'],
    ['Magento\Framework\App\Filesystem\DirectoryList\Configuration'],
    ['Magento\Framework\App\Filesystem\DirectoryList\Verification'],
    ['Magento\Framework\Filesystem\DriverFactory', 'Magento\Framework\Filesystem\DriverPool'],
    ['Magento\Framework\Filesystem\WrapperFactory'],
    ['Magento\Framework\Filesystem\WrapperInterface'],
    ['Magento\Install'],
    ['Magento\Install\Model\Resource\Resource', 'Magento\Framework\Module\Resource'],
    ['Magento\Framework\App\View\Deployment\Version\Generator\Timestamp', 'Magento\Framework\Stdlib\DateTime'],
    ['Magento\Framework\App\View\Deployment\Version\GeneratorInterface'],
    ['Magento\Framework\Authorization\RoleLocator', 'Magento\Framework\Authorization\RoleLocatorInterface'],
    ['Magento\Framework\Authorization\Policy', 'Magento\Framework\Authorization\PolicyInterface'],
    ['Magento\Framework\Stdlib\CookieManager', 'Magento\Framework\Stdlib\CookieManagerInterface'],
    ['Magento\Framework\Interception\PluginList', 'Magento\Framework\Interception\PluginListInterface'],
    ['Magento\Framework\Interception\Config', 'Magento\Framework\Interception\ConfigInterface'],
    ['Magento\Framework\Interception\Chain', 'Magento\Framework\Interception\ChainInterface'],
    ['Magento\Framework\Interception\Definition', 'Magento\Framework\Interception\DefinitionInterface'],
    ['Magento\Framework\ObjectManager\Factory', 'Magento\Framework\ObjectManager\FactoryInterface'],
    ['Magento\Framework\ObjectManager\Config', 'Magento\Framework\ObjectManager\ConfigInterface'],
    ['Magento\Framework\ObjectManager\Relations', 'Magento\Framework\ObjectManager\RelationsInterface'],
    ['Magento\Framework\ObjectManager\ConfigCache', 'Magento\Framework\ObjectManager\ConfigCacheInterface'],
    ['Magento\Framework\ObjectManager\Definition', 'Magento\Framework\ObjectManager\DefinitionInterface'],
    ['Magento\Framework\ObjectManager', 'Magento\Framework\ObjectManagerInterface'],
    ['Magento\Framework\HTTP\IClient', 'Magento\Framework\HTTP\ClientInterface'],
    ['Magento\Tax\Service\V1\TaxCalculationServiceInterface', 'Magento\Tax\Api\TaxCalculationInterface'],
    ['Magento\Tax\Service\V1\Data\TaxRule', 'Magento\Tax\Api\Data\TaxRuleInterface'],
    ['Magento\Tax\Service\V1\Data\TaxRuleSearchResults', 'Magento\Tax\Model\TaxRuleSearchResults'],
    ['Magento\Tax\Service\V1\Data\TaxRuleBuilder'],
    ['Magento\Tax\Service\V1\Data\TaxRuleSearchResultsBuilder'],
    ['Magento\Tax\Service\V1\Collection\TaxRuleCollection', 'Magento\Tax\Model\TaxRuleCollection'],
    ['Magento\Tax\Service\V1\TaxRateServiceInterface', 'Magento\Tax\Api\TaxRateRepositoryInterface'],
    ['Magento\Tax\Service\V1\Data\TaxRateTitle', 'Magento\Tax\Api\Data\TaxRateTitleInterface'],
    ['Magento\Tax\Service\V1\Data\TaxRate', 'Magento\Tax\Api\Data\TaxRateInterface'],
    ['Magento\Tax\Service\V1\Data\ZipRange'],
    ['Magento\Tax\Service\V1\Data\ZipRangeBuilder'],
    ['Magento\Tax\Service\V1\Data\TaxRateTitleBuilder', 'Magento\Tax\Api\Data\TaxRateTitleDataBuilder'],
    [
        'Magento\Tax\Service\V1\Data\TaxRateSearchResultsBuilder',
        'Magento\Tax\Api\Data\TaxRateSearchResultsDataBuilder'
    ],
    ['Magento\Tax\Service\V1\Data\TaxRateSearchResults', 'Magento\Tax\Api\Data\TaxRateSearchResultsInterface'],
    ['Magento\Tax\Service\V1\Data\TaxRateBuilder', 'Magento\Tax\Api\Data\TaxRateDataBuilder'],
    ['Magento\Tax\Service\V1\Collection\TaxRateCollection', 'Magento\Tax\Model\TaxRateCollection'],
    ['Magento\Tax\Service\V1\Data\TaxClassKey', 'Magento\Tax\Api\Data\TaxClassKeyInterface'],
    ['Magento\Tax\Service\V1\Data\TaxClassSearchResults', 'Magento\Tax\Api\Data\TaxClassSearchResultsInterface'],
    ['Magento\Tax\Service\V1\Data\TaxClass', 'Magento\Tax\Api\Data\TaxClassInterface'],
    ['Magento\Tax\Service\V1\TaxClassServiceInterface', 'Magento\Tax\Api\TaxClassRepositoryInterface'],
    ['Magento\Tax\Service\V1\Data\TaxClassSearchResultsBuilder'],
    ['Magento\Tax\Service\V1\Data\TaxClassKeyBuilder'],
    ['Magento\Tax\Service\V1\Data\TaxClassBuilder'],
    ['Magento\Tax\Service\V1\Data\OrderTaxDetails', 'Magento\Tax\Api\Data\OrderTaxDetailsInterface'],
    [
        'Magento\Tax\Service\V1\Data\OrderTaxDetails\AppliedTax',
        'Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxInterface'
    ],
    ['Magento\Tax\Service\V1\Data\OrderTaxDetails\Item', 'Magento\Tax\Api\Data\OrderTaxDetailsItemInterface'],
    ['Magento\Tax\Service\V1\Data\QuoteDetails', 'Magento\Tax\Api\Data\QuoteDetailsInterface'],
    ['Magento\Tax\Service\V1\Data\QuoteDetails\Item', 'Magento\Tax\Api\Data\QuoteDetailsItemInterface'],
    ['Magento\Tax\Service\V1\Data\TaxDetails', 'Magento\Tax\Api\Data\TaxDetailsInterface'],
    ['Magento\Tax\Service\V1\Data\TaxDetails\AppliedTax', 'Magento\Tax\Api\Data\AppliedTaxInterface'],
    ['Magento\Tax\Service\V1\Data\TaxDetails\AppliedTaxRate', 'Magento\Tax\Api\Data\AppliedTaxRateInterface'],
    ['Magento\Tax\Service\V1\Data\TaxDetails\Item', 'Magento\Tax\Api\Data\TaxDetailsItemInterface'],
    ['Magento\Tax\Service\V1\OrderTaxServiceInterface', 'Magento\Tax\Api\OrderTaxManagementInterface'],
    ['Magento\Tools\I18n\Code', 'Magento\Tools\I18n'],
    ['Magento\TestFramework\Utility\AggregateInvoker', 'Magento\Framework\Test\Utility\AggregateInvoker'],
    ['Magento\TestFramework\Utility\Classes', 'Magento\Framework\Test\Utility\Classes'],
    ['Magento\TestFramework\Utility\Files', 'Magento\Framework\Test\Utility\Files'],
    ['Magento\Framework\Module\Declaration\Reader\Filesystem', 'Magento\Framework\Module\ModuleList\Loader'],
    ['Magento\Framework\Module\Declaration\FileIterator'],
    ['Magento\Framework\Module\Declaration\FileIteratorFactory'],
    ['Magento\Framework\Module\Declaration\FileResolver', 'Magento\Framework\Module\ModuleList\Loader'],
    ['Magento\Framework\Module\Declaration\SchemaLocator'],
    ['Magento\Framework\Module\DependencyManager'],
    ['Magento\Framework\Module\DependencyManagerInterface'],
    ['Magento\Framework\App\Arguments\Loader'],
    ['Magento\Framework\App\Arguments', 'Magento\Framework\App\DeploymentConfig'],
    ['Magento\Bundle\Service\V1\Data\Product\Link', 'Magento\Bundle\Api\Data\LinkInterface'],
    ['Magento\Bundle\Service\V1\Data\Product\Option', 'Magento\Bundle\Api\Data\OptionInterface'],
    [
        'Magento\Bundle\Service\V1\Product\Link\ReadServiceInterface',
        'Magento\Bundle\Api\ProductLinkManagementInterface'
    ],
    [
        'Magento\Bundle\Service\V1\Product\Link\WriteServiceInterface',
        'Magento\Bundle\Api\ProductLinkManagementInterface'
    ],
    [
        'Magento\Bundle\Service\V1\Product\Option\ReadServiceInterface',
        'Magento\Bundle\Api\ProductOptionRepositoryInterface'
    ],
    [
        'Magento\Bundle\Service\V1\Product\Option\WriteServiceInterface',
        'Magento\Bundle\Api\ProductOptionRepositoryInterface'
    ],
    [
        'Magento\Bundle\Service\V1\Product\Option\Type\ReadServiceInterface',
        'Magento\Bundle\Api\ProductOptionTypeListInterface'
    ],
    ['Magento\Sales\Controller\Adminhtml\Order\InvoiceLoader'],
    ['Magento\Sales\Model\Resource\AbstractResource', 'Magento\Framework\Model\Resource\Db\AbstractDb'],
    ['Magento\Backend\Block\System\Config\Switcher'],
    ['Magento\VersionsCms\Block\Adminhtml\Scope\Switcher'],
    ['Magento\Backend\Block\Widget\View\Container'],
    ['Magento\VersionsCms\Block\Adminhtml\Cms\Page\Preview\Store'],
    ['Magento\Customer\Model\Address\Converter'],
    ['Magento\Customer\Model\Converter'],
    ['Magento\CatalogRule\Plugin\Indexer\Product\PriceIndexer'],
    ['Magento\CatalogRule\Plugin\Indexer\Product\PriceIndexerTest'],
    [
        'Magento\Framework\Interception\ObjectManager\Compiled\Config',
        'Magento\Framework\Interception\ObjectManager\Config\Compiled'
    ],
    [
        'Magento\Framework\Interception\ObjectManager\Config',
        'Magento\Framework\Interception\ObjectManager\Config\Developer'
    ],
    ['Magento\Framework\ObjectManager\Config\ProxyConfig'],
    ['Magento\Catalog\Block\Product\Send'],
    ['Magento\Catalog\Helper\Product\Options'],
    ['Magento\Cms\Model\Resource\Page\Service'],
    ['Magento\Directory\Helper\Url'],
    ['Magento\GiftMessage\Helper\Url'],
    ['Magento\Rss\Helper\Data'],
    ['Magento\Sales\Model\ConverterInterface'],
    ['Magento\Paypal\Block\System\Config\Fieldset\Location'],
    ['Magento\Paypal\Block\Payflow\Advanced\Review'],
    ['Magento\Paypal\Block\Payflow\Link\Review'],
    ['Magento\Paypal\Model\System\Config\Source\AuthorizationAmounts'],
    ['Magento\Rule\Model\Rule', 'Magento\Rule\Model\AbstractModel'],
    ['Magento\Framework\App\Cache\State\Options', 'Magento\Framework\App\Cache\State'],
    ['Magento\Framework\App\Cache\State\OptionsInterface', 'Magento\Framework\App\Cache\State'],
    ['Magento\Framework\Logger', 'Psr\Log\LoggerInterface'],
];
