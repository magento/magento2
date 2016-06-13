<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Locate all payment methods in the system and verify declaration of their blocks
 */
namespace Magento\Test\Integrity\Magento\Payment;

use Magento\Framework\App\State;
use Magento\TestFramework\Helper\Bootstrap;

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
        if ($code == 'vault') {
            return;
        }
        Bootstrap::getObjectManager()->configure($this->getTestConfiguration());
        /** @var $blockFactory \Magento\Framework\View\Element\BlockFactory */
        $blockFactory = Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\Element\BlockFactory'
        );
        $storeId = Bootstrap::getObjectManager()->get(
            'Magento\Store\Model\StoreManagerInterface'
        )->getStore()->getId();
        /** @var $model \Magento\Payment\Model\MethodInterface */
        if (empty($methodClass)) {
            /**
             * Note that $code is not whatever the payment method getCode() returns
             */
            $this->fail("Model of '{$code}' payment method is not found.");
        }
        $model = Bootstrap::getObjectManager()->create($methodClass);
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
        Bootstrap::getObjectManager()->get('Magento\Framework\App\State')
            ->setMode(State::MODE_DEVELOPER);
        $this->assertNotEmpty($model->getTitle());
        foreach ([$model->getFormBlockType(), $model->getInfoBlockType()] as $blockClass) {
            $message = "Block class: {$blockClass}";
            /** @var $block \Magento\Framework\View\Element\Template */
            $block = $blockFactory->createBlock($blockClass);
            $block->setArea('frontend');
            $this->assertFileExists((string)$block->getTemplateFile(), $message);
            if ($model->canUseInternal()) {
                try {
                    Bootstrap::getObjectManager()->get(
                        'Magento\Store\Model\StoreManagerInterface'
                    )->getStore()->setId(
                        \Magento\Store\Model\Store::DEFAULT_STORE_ID
                    );
                    $block->setArea('adminhtml');
                    $this->assertFileExists((string)$block->getTemplateFile(), $message);
                    Bootstrap::getObjectManager()->get(
                        'Magento\Store\Model\StoreManagerInterface'
                    )->getStore()->setId(
                        $storeId
                    );
                } catch (\Exception $e) {
                    Bootstrap::getObjectManager()->get(
                        'Magento\Store\Model\StoreManagerInterface'
                    )->getStore()->setId(
                        $storeId
                    );
                    throw $e;
                }
            }
        }
        Bootstrap::getObjectManager()->get('Magento\Framework\App\State')
            ->setMode(State::MODE_DEFAULT);
    }

    /**
     * @return array
     */
    public function paymentMethodDataProvider()
    {
        /** @var $helper \Magento\Payment\Helper\Data */
        $helper = Bootstrap::getObjectManager()->get('Magento\Payment\Helper\Data');
        $result = [];
        foreach ($helper->getPaymentMethods() as $code => $method) {
            $result[] = [$code, $method['model']];
        }
        return $result;
    }

    /**
     * @param string $path
     * @return \RegexIterator
     */
    private function collectFiles($path)
    {
        $ds = preg_quote(DIRECTORY_SEPARATOR);
        $flags = \FilesystemIterator::CURRENT_AS_FILEINFO
            | \FilesystemIterator::SKIP_DOTS;
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, $flags));

        return new \RegexIterator(
            $iterator,
            '#' . $ds . 'etc' . $ds . 'di\.php$#',
            \RegexIterator::MATCH,
            \RegexIterator::USE_KEY
        );
    }

    /**
     * @return array
     */
    private function getTestConfiguration()
    {
        $result = [];
        $ds = DIRECTORY_SEPARATOR;
        $path = __DIR__ . $ds . str_repeat('..' . $ds, 5) . 'Magento';

        foreach ($this->collectFiles($path) as $file) {
            $config = include $file->getPathname();
            $result = array_replace_recursive($result, $config);
        }

        return $result;
    }
}
