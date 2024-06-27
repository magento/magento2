<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Locate all payment methods in the system and verify declaration of their blocks
 */
namespace Magento\Test\Integrity\Magento\Payment;

use Magento\Framework\App\State;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestStatus\TestStatus;
/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MethodsTest extends \PHPUnit\Framework\TestCase
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
        if (in_array($code, ['free', 'substitution', 'vault', 'payflowpro_cc_vault', 'fake_vault'])) {
            return;
        }
        Bootstrap::getObjectManager()->configure($this->getTestConfiguration());
        /** @var $blockFactory \Magento\Framework\View\Element\BlockFactory */
        $blockFactory = Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\Element\BlockFactory::class
        );
        $storeId = Bootstrap::getObjectManager()->get(
            \Magento\Store\Model\StoreManagerInterface::class
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
                \Magento\Payment\Model\Info::class
            )->disableOriginalConstructor()->onlyMethods(
                []
            )->getMock();
            $paymentInfo->expects(
                $this->any()
            )->method(
                'getAdditionalInformation'
            )->willReturn(
                'Additional data mock'
            );
            $model->setInfoInstance($paymentInfo);
        }
        Bootstrap::getObjectManager()->get(\Magento\Framework\App\State::class)
            ->setMode(State::MODE_DEVELOPER);
        $this->assertNotEmpty($model->getTitle());
        foreach ([$model->getFormBlockType(), $model->getInfoBlockType()] as $blockClass) {
            if (is_array($blockClass)) {
                $blockClass=$blockClass['instance'] ?? '';
            }
            $message = "Block class: {$blockClass}";
            /** @var $block \Magento\Framework\View\Element\Template */
            $block = $blockFactory->createBlock($blockClass);
            $block->setArea('frontend');
            if ($model->canUseInternal()) {
                try {
                    Bootstrap::getObjectManager()->get(
                        \Magento\Store\Model\StoreManagerInterface::class
                    )->getStore()->setId(
                        \Magento\Store\Model\Store::DEFAULT_STORE_ID
                    );
                    $block->setArea('adminhtml');
                    $this->assertFileExists((string)$block->getTemplateFile(), $message);
                    Bootstrap::getObjectManager()->get(
                        \Magento\Store\Model\StoreManagerInterface::class
                    )->getStore()->setId(
                        $storeId
                    );
                } catch (\Exception $e) {
                    Bootstrap::getObjectManager()->get(
                        \Magento\Store\Model\StoreManagerInterface::class
                    )->getStore()->setId(
                        $storeId
                    );
                    throw $e;
                }
            }
        }
        Bootstrap::getObjectManager()->get(\Magento\Framework\App\State::class)
            ->setMode(State::MODE_DEFAULT);
    }

    /**
     * @return array
     */
    public static function paymentMethodDataProvider()
    {
        /** @var $helper \Magento\Payment\Helper\Data */
        $om = Bootstrap::getObjectManager();
        $helper = $om->get(\Magento\Payment\Helper\Data::class);
        $result = [];
        foreach ($helper->getPaymentMethods() as $code => $method) {
            if (!isset($method['model'])) {
                TestStatus::warning(
                    'The `model` node must be provided for payment method configuration with code: ' . $code
                );
                continue;
            }
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
