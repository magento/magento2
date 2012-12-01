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
 * @category    Magento
 * @package     Mage_Adminhtml
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_Adminhtml_Block_Poll_Grid
 */
class Mage_Adminhtml_Block_Poll_GridTest extends PHPUnit_Framework_TestCase
{
    public function testPrepareColumns()
    {
        $layout = Mage::getObjectManager()->create('Mage_Core_Model_Layout');
        $block = $layout->addBlock('Mage_Adminhtml_Block_Poll_Grid');

        $prepareColumnsMethod = new ReflectionMethod(
            'Mage_Adminhtml_Block_Poll_Grid', '_prepareColumns');
        $prepareColumnsMethod->setAccessible(true);
        $prepareColumnsMethod->invoke($block);

        foreach (array('date_posted', 'date_closed') as $id) {
            $column = $block->getColumn($id);
            $this->assertNotNull($column);
            $this->assertNotEmpty($column->getDateFormat());
        }
    }
}
