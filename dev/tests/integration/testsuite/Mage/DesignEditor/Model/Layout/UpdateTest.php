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
 * @package     Mage_DesignEditor
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_DesignEditor_Model_Layout_UpdateTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Mage_DesignEditor_Model_Layout_Update::__beforeSave
     * @magentoDbIsolation enabled
     */
    public function testBeforeSave()
    {
        /** @var $model Mage_DesignEditor_Model_Layout_Update */
        $model = Mage::getObjectManager()->get('Mage_DesignEditor_Model_Layout_Update');
        $model->setData(array(
            'handle' => 'layout_update_test'
        ));
        $model->save();
        $this->assertTrue($model->getIsVde());
        $model->delete();
    }
}
