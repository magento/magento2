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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Model_Layout_MergeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Layout_Merge
     */
    private $_model;

    protected function setUp()
    {
        $layoutUtility = new Mage_Core_Utility_Layout($this);
        $this->_model = $layoutUtility->getLayoutUpdateFromFixture(__DIR__ . '/_files/_handles.xml');
    }

    /**
     * Note: test was not relocated to unit tests because of invocation of the static methods
     */
    public function testGetContainers()
    {
        $this->_model->addPageHandles(array('catalog_product_view_type_configurable'));
        $this->_model->load();
        $expected = array(
            'content'                         => 'Main Content Area',
            'product.info.extrahint'          => 'Product View Extra Hint',
            'product.info.configurable.extra' => 'Configurable Product Extra Info',
        );
        $this->assertSame($expected, $this->_model->getContainers());
    }
}
