<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Modular;

use Magento\Customer\Model\Context;

/**
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TemplateFilesTest extends \Magento\TestFramework\TestCase\AbstractIntegrity
{
    public function testAllTemplates()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            function ($module, $template, $class, $area) {
                \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                    \Magento\Framework\View\DesignInterface::class
                )->setDefaultDesignTheme();
                // intentionally to make sure the module files will be requested
                $params = [
                    'area' => $area,
                    'themeModel' => \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                        \Magento\Framework\View\Design\ThemeInterface::class
                    ),
                    'module' => $module,
                ];
                $file = \Magento\TestFramework\Helper\Bootstrap::getObjectmanager()->get(
                    \Magento\Framework\View\FileSystem::class
                )->getTemplateFileName(
                    $template,
                    $params
                );
                $this->assertInternalType('string', $file, "Block class: {$class} {$template}");
                $this->assertFileExists($file, "Block class: {$class}");
            },
            $this->allTemplatesDataProvider()
        );
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function allTemplatesDataProvider()
    {
        $blockClass = '';
        try {
            /** @var $website \Magento\Store\Model\Website */
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                \Magento\Store\Model\StoreManagerInterface::class
            )->getStore()->setWebsiteId(
                0
            );

            $templates = [];
            $skippedBlocks = $this->_getBlocksToSkip();
            foreach (\Magento\Framework\App\Utility\Classes::collectModuleClasses('Block') as $blockClass => $module) {
                if (!in_array($module, $this->_getEnabledModules()) || in_array($blockClass, $skippedBlocks)) {
                    continue;
                }
                $class = new \ReflectionClass($blockClass);
                if ($class->isAbstract() || !$class->isSubclassOf(\Magento\Framework\View\Element\Template::class)) {
                    continue;
                }

                $area = 'frontend';
                if ($module == 'Magento_Backend' || strpos(
                    $blockClass,
                    '\\Adminhtml\\'
                ) || strpos(
                    $blockClass,
                    '\\Backend\\'
                ) || $class->isSubclassOf(
                    \Magento\Backend\Block\Template::class
                )
                ) {
                    $area = 'adminhtml';
                }

                \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                    \Magento\Framework\App\AreaList::class
                )->getArea(
                    $area
                )->load(
                    \Magento\Framework\App\Area::PART_CONFIG
                );
                \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                    \Magento\Framework\Config\ScopeInterface::class
                )->setCurrentScope(
                    $area
                );
                \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                    \Magento\Framework\App\State::class
                )->setAreaCode(
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
                $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create($blockClass);
                $template = $block->getTemplate();
                if ($template) {
                    $templates[$module . ', ' . $template . ', ' . $blockClass . ', ' . $area] = [
                        $module,
                        $template,
                        $blockClass,
                        $area,
                    ];
                }
            }
            return $templates;
        } catch (\Exception $e) {
            trigger_error(
                "Corrupted data provider. Last known block instantiation attempt: '{$blockClass}'." .
                " Exception: {$e}",
                E_USER_ERROR
            );
        }
    }

    /**
     * @return array
     */
    protected function _getBlocksToSkip()
    {
        $result = [];
        foreach (glob(__DIR__ . '/_files/skip_template_blocks*.php') as $file) {
            $blocks = include $file;
            $result = array_merge($result, $blocks);
        }
        return array_combine($result, $result);
    }
}
