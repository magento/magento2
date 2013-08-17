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
 * @package     Mage_Payment
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Locate all payment methods in the system and verify declaration of their blocks
 */
class Integrity_Mage_Payment_MethodsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param string $methodClass
     * @param string $code
     * @dataProvider paymentMethodDataProvider
     * @throws Exception on various assertion failures
     */
    public function testPaymentMethod($code, $methodClass)
    {
        /** @var $blockFactory Mage_Core_Model_BlockFactory */
        $blockFactory = Mage::getObjectManager()->get('Mage_Core_Model_BlockFactory');
        $storeId = Mage::app()->getStore()->getId();
        /** @var $model Mage_Payment_Model_Method_Abstract */
        if (empty($methodClass)) {
            /**
             * Note that $code is not whatever the payment method getCode() returns
             */
            $this->fail("Model of '{$code}' payment method is not found."); // prevent fatal error
        }
        $model = Mage::getObjectManager()->create($methodClass);
        $this->assertNotEmpty($model->getTitle());
        foreach (array($model->getFormBlockType(), $model->getInfoBlockType()) as $blockClass) {
            $message = "Block class: {$blockClass}";
            /** @var $block Mage_Core_Block_Template */
            $block = $blockFactory->createBlock($blockClass);
            $block->setArea('frontend');
            $this->assertFileExists($block->getTemplateFile(), $message);
            if ($model->canUseInternal()) {
                try {
                    Mage::app()->getStore()->setId(Mage_Core_Model_AppInterface::ADMIN_STORE_ID);
                    $block->setArea('adminhtml');
                    $this->assertFileExists($block->getTemplateFile(), $message);
                    Mage::app()->getStore()->setId($storeId);
                } catch (Exception $e) {
                    Mage::app()->getStore()->setId($storeId);
                    throw $e;
                }
            }
        }
    }

    /**
     * @return array
     */
    public function paymentMethodDataProvider()
    {
        /** @var $helper Mage_Payment_Helper_Data */
        $helper = Mage::helper('Mage_Payment_Helper_Data');
        $result = array();
        foreach ($helper->getPaymentMethods() as $code => $method) {
            $result[] = array($code, $method['model']);
        }
        return $result;
    }
}
