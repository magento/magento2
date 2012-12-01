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
 * @package     Mage_Tag
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Tag_Block_Adminhtml_Catalog_Product_Edit_Tab_TagTest
    extends Mage_Tag_Block_Adminhtml_Catalog_Product_Edit_Tab_TagTestCaseAbstract
{
    /**
     * @var string
     */
    protected $_modelName = 'Mage_Tag_Block_Adminhtml_Catalog_Product_Edit_Tab_Tag';

    /**
     * @var string
     */
    protected $_title = 'Product Tags';

    /**
     * @covers Mage_Tag_Block_Adminhtml_Catalog_Product_Edit_Tab_Tag::getTabLabel
     * @covers Mage_Tag_Block_Adminhtml_Catalog_Product_Edit_Tab_Tag::getTabTitle
     * @covers Mage_Tag_Block_Adminhtml_Catalog_Product_Edit_Tab_Tag::canShowTab
     * @covers Mage_Tag_Block_Adminhtml_Catalog_Product_Edit_Tab_Tag::isHidden
     * @covers Mage_Tag_Block_Adminhtml_Catalog_Product_Edit_Tab_Tag::getTabClass
     * @covers Mage_Tag_Block_Adminhtml_Catalog_Product_Edit_Tab_Tag::getAfter
     *
     * @dataProvider methodListDataProvider
     * @param string $method
     */
    public function testDefinedPublicMethods($method)
    {
        $this->$method();
    }
}
