<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Modular;

use Magento\Customer\Model\Context;

/**
 * This test ensures that all blocks have the appropriate constructor arguments that allow
 * them to be instantiated via the objectManager.
 *
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BlockInstantiationTest extends \Magento\TestFramework\TestCase\AbstractIntegrity
{
    public function testBlockInstantiation()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            function ($module, $class, $area) {
                $this->assertNotEmpty($module);
                $this->assertTrue(class_exists($class), "Block class: {$class}");
                \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                    \Magento\Framework\Config\ScopeInterface::class
                )->setCurrentScope(
                    $area
                );
                $context = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                    \Magento\Framework\App\Http\Context::class
                );
                $context->setValue(Context::CONTEXT_AUTH, false, false);
                $context->setValue(
                    Context::CONTEXT_GROUP,
                    \Magento\Customer\Model\GroupManagement::NOT_LOGGED_IN_ID,
                    \Magento\Customer\Model\GroupManagement::NOT_LOGGED_IN_ID
                );
                \Magento\TestFramework\Helper\Bootstrap::getInstance()->loadArea($area);

                try {
                    \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create($class);
                } catch (\Exception $e) {
                    throw new \Exception("Unable to instantiate '{$class}'", 0, $e);
                }
            },
            $this->allBlocksDataProvider()
        );
    }

    /**
     * @return array
     */
    public function allBlocksDataProvider()
    {
        $blockClass = '';
        try {
            /** @var $website \Magento\Store\Model\Website */
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                \Magento\Store\Model\StoreManagerInterface::class
            )->getStore()->setWebsiteId(
                0
            );

            $enabledModules = $this->_getEnabledModules();
            $skipBlocks = $this->_getBlocksToSkip();
            $templateBlocks = [];
            $blockMods = \Magento\Framework\App\Utility\Classes::collectModuleClasses('Block');
            foreach ($blockMods as $blockClass => $module) {
                if (!isset($enabledModules[$module]) || isset($skipBlocks[$blockClass])) {
                    continue;
                }
                $class = new \ReflectionClass($blockClass);
                if ($class->isAbstract() || !$class->isSubclassOf(\Magento\Framework\View\Element\Template::class)) {
                    continue;
                }
                $templateBlocks = $this->_addBlock($module, $blockClass, $class, $templateBlocks);
            }
            asort($templateBlocks);
            return $templateBlocks;
        } catch (\Exception $e) {
            trigger_error(
                "Corrupted data provider. Last known block instantiation attempt: '{$blockClass}'." .
                " Exception: {$e}",
                E_USER_ERROR
            );
        }
    }

    /**
     * Loads block classes, that should not be instantiated during the instantiation test
     *
     * @return array
     */
    protected function _getBlocksToSkip()
    {
        $result = [];
        foreach (glob(__DIR__ . '/_files/skip_blocks*.php') as $file) {
            $blocks = include $file;
            $result = array_merge($result, $blocks);
        }
        return array_combine($result, $result);
    }

    /**
     * @param $module
     * @param $blockClass
     * @param $class
     * @param $templateBlocks
     * @return mixed
     */
    private function _addBlock($module, $blockClass, $class, $templateBlocks)
    {
        $area = 'frontend';
        if ($module == 'Magento_Backend' || strpos(
            $blockClass,
            '\\Adminhtml\\'
        ) || strpos(
            $blockClass,
            '_Backend_'
        ) || $class->isSubclassOf(
            \Magento\Backend\Block\Template::class
        )
        ) {
            $area = 'adminhtml';
        }
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\App\AreaList::class
        )->getArea(
            \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE
        )->load(
            \Magento\Framework\App\Area::PART_CONFIG
        );
        $templateBlocks[$module . ', ' . $blockClass . ', ' . $area] = [$module, $blockClass, $area];
        return $templateBlocks;
    }
}
