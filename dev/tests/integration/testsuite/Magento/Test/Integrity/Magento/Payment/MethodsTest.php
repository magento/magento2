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
 * Locate all payment methods in the system and verify declaration of their blocks
 */
namespace Magento\Test\Integrity\Magento\Payment;

class MethodsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $methodClass
     * @param string $code
     * @dataProvider paymentMethodDataProvider
     * @magentoAppArea frontend
     * @throws \Exception on various assertion failures
     */
    public function testPaymentMethod($code, $methodClass)
    {
        /** @var $blockFactory \Magento\Framework\View\Element\BlockFactory */
        $blockFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\Element\BlockFactory'
        );
        $storeId = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\StoreManagerInterface'
        )->getStore()->getId();
        /** @var $model \Magento\Payment\Model\MethodInterface */
        if (empty($methodClass)) {
            /**
             * Note that $code is not whatever the payment method getCode() returns
             */
            $this->fail("Model of '{$code}' payment method is not found.");
        }
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create($methodClass);
        if ($code == \Magento\Payment\Model\Method\Substitution::CODE) {
            $paymentInfo = $this->getMockBuilder(
                'Magento\Payment\Model\Info'
            )->disableOriginalConstructor()->setMethods(
                []
            )->getMock();
            $paymentInfo->expects(
                $this->any()
            )->method(
                'getAdditionalInformation'
            )->will(
                $this->returnValue('Additional data mock')
            );
            $model->setInfoInstance($paymentInfo);
        }
        $this->assertNotEmpty($model->getTitle());
        foreach (array($model->getFormBlockType(), $model->getInfoBlockType()) as $blockClass) {
            $message = "Block class: {$blockClass}";
            /** @var $block \Magento\Framework\View\Element\Template */
            $block = $blockFactory->createBlock($blockClass);
            $block->setArea('frontend');
            $this->assertFileExists($block->getTemplateFile(), $message);
            if ($model->canUseInternal()) {
                try {
                    \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                        'Magento\Framework\StoreManagerInterface'
                    )->getStore()->setId(
                        \Magento\Store\Model\Store::DEFAULT_STORE_ID
                    );
                    $block->setArea('adminhtml');
                    $this->assertFileExists($block->getTemplateFile(), $message);
                    \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                        'Magento\Framework\StoreManagerInterface'
                    )->getStore()->setId(
                        $storeId
                    );
                } catch (\Exception $e) {
                    \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                        'Magento\Framework\StoreManagerInterface'
                    )->getStore()->setId(
                        $storeId
                    );
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
        /** @var $helper \Magento\Payment\Helper\Data */
        $helper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Payment\Helper\Data');
        $result = array();
        foreach ($helper->getPaymentMethods() as $code => $method) {
            $result[] = array($code, $method['model']);
        }
        return $result;
    }
}
