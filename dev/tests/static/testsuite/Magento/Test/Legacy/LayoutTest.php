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

/**
 * Coverage of obsolete nodes in layout
 */
namespace Magento\Test\Legacy;

class LayoutTest extends \PHPUnit_Framework_TestCase
{
    /**
     * List of obsolete nodes
     *
     * @var array
     */
    protected $_obsoleteNodes = array(
        'PRODUCT_TYPE_simple',
        'PRODUCT_TYPE_configurable',
        'PRODUCT_TYPE_grouped',
        'PRODUCT_TYPE_bundle',
        'PRODUCT_TYPE_virtual',
        'PRODUCT_TYPE_downloadable',
        'PRODUCT_TYPE_giftcard',
        'catalog_category_default',
        'catalog_category_layered',
        'catalog_category_layered_nochildren',
        'customer_logged_in',
        'customer_logged_out',
        'customer_logged_in_psc_handle',
        'customer_logged_out_psc_handle',
        'cms_page',
        'sku_failed_products_handle',
        'catalog_product_send',
        'reference'
    );

    /**
     * List of obsolete references per handle
     *
     * @var array
     */
    protected $_obsoleteReferences = array(
        'adminhtml_user_edit' => array(
            'adminhtml.permissions.user.edit.tabs',
            'adminhtml.permission.user.edit.tabs',
            'adminhtml.permissions.user.edit',
            'adminhtml.permission.user.edit',
            'adminhtml.permissions.user.roles.grid.js',
            'adminhtml.permission.user.roles.grid.js',
            'adminhtml.permissions.user.edit.tab.roles',
            'adminhtml.permissions.user.edit.tab.roles.js'
        ),
        'adminhtml_user_role_index' => array(
            'adminhtml.permission.role.index',
            'adminhtml.permissions.role.index',
            'adminhtml.permissions.role.grid'
        ),
        'adminhtml_user_role_rolegrid' => array('adminhtml.permission.role.grid', 'adminhtml.permissions.role.grid'),
        'adminhtml_user_role_editrole' => array(
            'adminhtml.permissions.editroles',
            'adminhtml.permissions.tab.rolesedit',
            'adminhtml.permission.roles.users.grid.js',
            'adminhtml.permissions.roles.users.grid.js',
            'adminhtml.permission.role.buttons',
            'adminhtml.permissions.role.buttons',
            'adminhtml.permission.role.edit.gws'
        ),
        'adminhtml_user_role_editrolegrid' => array(
            'adminhtml.permission.role.grid.user',
            'adminhtml.permissions.role.grid.user'
        ),
        'adminhtml_user_index' => array('adminhtml.permission.user.index', 'adminhtml.permissions.user.index'),
        'adminhtml_user_rolegrid' => array(
            'adminhtml.permissions.user.rolegrid',
            'adminhtml.permission.user.rolegrid'
        ),
        'adminhtml_user_rolesgrid' => array(
            'adminhtml.permissions.user.rolesgrid',
            'adminhtml.permission.user.rolesgrid'
        )
    );

    public function testLayoutFile()
    {
        $invoker = new \Magento\TestFramework\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * @param string $layoutFile
             */
            function ($layoutFile) {
                $layoutXml = simplexml_load_file($layoutFile);

                $this->_testObsoleteReferences($layoutXml);
                $this->_testObsoleteAttributes($layoutXml);

                $selectorHeadBlock = '(name()="block" or name()="referenceBlock") and ' .
                    '(@name="head" or @name="convert_root_head" or @name="vde_head")';
                $this->assertSame(
                    array(),
                    $layoutXml->xpath(
                        '//block[@class="Magento\Theme\Block\Html\Head\Css" ' .
                        'or @class="Magento\Theme\Block\Html\Head\Link" ' .
                        'or @class="Magento\Theme\Block\Html\Head\Script"]' .
                        '/parent::*[not(' .
                        $selectorHeadBlock .
                        ')]'
                    ),
                    'Blocks \Magento\Theme\Block\Html\Head\{Css,Link,Script} '.
                    'are allowed within the "head" block only. ' .
                    'Verify integrity of the nodes nesting.'
                );
                $this->assertSame(
                    array(),
                    $layoutXml->xpath('/layout//*[@output="toHtml"]'),
                    'output="toHtml" is obsolete. Use output="1"'
                );
                foreach ($layoutXml as $handle) {
                    $this->assertNotContains(
                        (string)$handle['id'],
                        $this->_obsoleteNodes,
                        'This layout handle is obsolete.'
                    );
                }
                foreach ($layoutXml->xpath('@helper') as $action) {
                    $this->assertNotContains('/', $action->getAttribute('helper'));
                    $this->assertContains('::', $action->getAttribute('helper'));
                }

                if (false !== strpos(
                    $layoutFile,
                    'app/code/Magento/Sales/view/adminhtml/layout/sales_order'
                ) || false !== strpos(
                    $layoutFile,
                    'app/code/Magento/Shipping/view/adminhtml/layout/adminhtml_order'
                )
                ) {
                    $this->markTestIncomplete(
                        "The file {$layoutFile} has to use \\Magento\\Core\\Block\\Text\\List, \n" .
                        'there is no solution to get rid of it right now.'
                    );
                }
                $this->assertSame(
                    array(),
                    $layoutXml->xpath('/layout//block[@class="Magento\Framework\View\Element\Text\ListText"]'),
                    'The class \Magento\Framework\View\Element\Text\ListTest' .
                    ' is not supposed to be used in layout anymore.'
                );
            },
            \Magento\TestFramework\Utility\Files::init()->getLayoutFiles()
        );
    }

    /**
     * @param SimpleXMLElement $layoutXml
     */
    protected function _testObsoleteReferences($layoutXml)
    {
        foreach ($layoutXml as $handle) {
            if (isset($this->_obsoleteReferences[$handle->getName()])) {
                foreach ($handle->xpath('reference') as $reference) {
                    $this->assertNotContains(
                        (string)$reference['name'],
                        $this->_obsoleteReferences[$handle->getName()],
                        'The block being referenced is removed.'
                    );
                }
            }
        }
    }

    /**
     * Tests the attributes of the top-level Layout Node.
     * Verifies there are no longer attributes of "parent" or "owner"
     *
     * @param SimpleXMLElement $layoutXml
     */
    protected function _testObsoleteAttributes($layoutXml)
    {
        $issues = array();
        $type = $layoutXml['type'];
        $parent = $layoutXml['parent'];
        $owner = $layoutXml['owner'];

        if ((string)$type === 'page') {
            if ($parent) {
                $issues[] = 'Attribute "parent" is not valid';
            }
        }
        if ((string)$type === 'fragment') {
            if ($owner) {
                $issues[] = 'Attribute "owner" is not valid';
            }
        }
        if ($issues) {
            $this->fail("Issues found in handle declaration:\n" . implode("\n", $issues) . "\n");
        }
    }

    public function testActionNodeMethods()
    {
        $invoker = new \Magento\TestFramework\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * @param string $layoutFile
             */
            function ($layoutFile) {
                $layoutXml = simplexml_load_file($layoutFile);
                $methodFilter = '@method!="' . implode('" and @method!="', $this->getAllowedActionNodeMethods()) . '"';
                foreach ($layoutXml->xpath('//action[' . $methodFilter . ']') as $node) {
                    $attributes = $node->attributes();
                    $this->fail(
                        sprintf(
                            'Call of method "%s" via layout instruction <action> is not allowed.',
                            $attributes['method']
                        )
                    );
                }
            },
            \Magento\TestFramework\Utility\Files::init()->getLayoutFiles()
        );
    }

    /**
     * List of currently allowed (i.e. not refactored yet) methods for use in <action method="someMethod"/> layout
     *  instruction.
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * Temporary method existing until <action> instruction in layout is not eliminated, no need to split it.
     *
     * @return string[]
     */
    public function getAllowedActionNodeMethods()
    {
        return array(
            'addBodyClass',
            'addButtons',
            'addColumnCountLayoutDepend',
            'addCrumb',
            'addDatabaseBlock',
            'addInputTypeTemplate',
            'addNotice',
            'addReportTypeOption',
            'addTab',
            'addTabAfter',
            'addText',
            'append',
            'removeTab',
            'setActive',
            'setAddressType',
            'setAfterCondition',
            'setAfterTotal',
            'setAtCall',
            'setAtCode',
            'setAtLabel',
            'setAuthenticationStartMode',
            'setBeforeCondition',
            'setBlockId',
            'setBugreportUrl',
            'setCanLoadExtJs',
            'setCanLoadRulesJs',
            'setCanLoadTinyMce',
            'setClassName',
            'setColClass',
            'setColumnCount',
            'setColumnsLimit',
            'setCssClass',
            'setDefaultFilter',
            'setDefaultStoreName',
            'setDestElementId',
            'setDisplayArea',
            'setDontDisplayContainer',
            'setEmptyGridMessage',
            'setEntityModelClass',
            'setFieldOption',
            'setFieldVisibility',
            'setFormCode',
            'setFormId',
            'setFormPrefix',
            'setGiftRegistryTemplate',
            'setGiftRegistryUrl',
            'setGridHtmlClass',
            'setGridHtmlCss',
            'setGridHtmlId',
            'setHeaderTitle',
            'setHideBalance',
            'setHideLink',
            'setHideRequiredNotice',
            'setHtmlClass',
            'setId',
            'setImageType',
            'setImgAlt',
            'setImgHeight',
            'setImgSrc',
            'setImgWidth',
            'setInList',
            'setInfoTemplate',
            'setIsCollapsed',
            'setIsDisabled',
            'setIsEnabled',
            'setIsGuestNote',
            'setIsHandle',
            'setIsLinkMode',
            'setIsPlaneMode',
            'setIsTitleHidden',
            'setIsViewCurrent',
            'setItemLimit',
            'setLabel',
            'setLabelProperties',
            'setLayoutCode',
            'setLinkUrl',
            'setListCollection',
            'setListModes',
            'setListOrders',
            'setMAPTemplate',
            'setMethodFormTemplate',
            'setMethodInfo',
            'setMyClass',
            'setPageLayout',
            'setPageTitle',
            'setParentType',
            'setControllerPath',
            'setPosition',
            'setPositioned',
            'setRewardMessage',
            'setRewardQtyLimitationMessage',
            'setShouldPrepareInfoTabs',
            'setShowPart',
            'setSignupLabel',
            'setSourceField',
            'setStoreVarName',
            'setStrong',
            'setTemplate',
            'setText',
            'setThemeName',
            'setTierPriceTemplate',
            'setTitle',
            'setTitleClass',
            'setTitleId',
            'setToolbarBlockName',
            'setType',
            'setUseConfirm',
            'setValueProperties',
            'setViewAction',
            'setViewColumn',
            'setViewLabel',
            'setViewMode',
            'setWrapperClass',
            'unsetChild',
            'unsetChildren',
            'updateButton'
        );
    }
}
