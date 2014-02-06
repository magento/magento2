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
 * @package     Magento_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Test\Integrity\Modular;

/**
 * @magentoAppIsolation
 */
class TemplateFilesTest extends \Magento\TestFramework\TestCase\AbstractIntegrity
{
    public function testAllTemplates()
    {
        $invoker = new \Magento\TestFramework\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * @param string $module
             * @param string $template
             * @param string $class
             * @param string $area
             */
            function ($module, $template, $class, $area) {
                \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\View\DesignInterface')
                    ->setDefaultDesignTheme();
                // intentionally to make sure the module files will be requested
                $params = array(
                    'area'       => $area,
                    'themeModel' => \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
                        ->create('Magento\View\Design\ThemeInterface'),
                    'module'     => $module
                );
                $file = \Magento\TestFramework\Helper\Bootstrap::getObjectmanager()
                    ->get('Magento\View\FileSystem')
                    ->getFilename($template, $params);
                $this->assertFileExists($file, "Block class: {$class}");
            },
            $this->allTemplatesDataProvider()
        );
    }

    /**
     * @return array
     */
    public function allTemplatesDataProvider()
    {
        $blockClass = '';
        try {
            /** @var $website \Magento\Core\Model\Website */
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Model\StoreManagerInterface')
                ->getStore()->setWebsiteId(0);

            $templates = array();
            $skippedBlocks = $this->_getBlocksToSkip();
            foreach (\Magento\TestFramework\Utility\Classes::collectModuleClasses('Block') as $blockClass => $module) {
                if (!in_array($module, $this->_getEnabledModules()) || in_array($blockClass, $skippedBlocks)) {
                    continue;
                }
                $class = new \ReflectionClass($blockClass);
                if ($class->isAbstract() || !$class->isSubclassOf('Magento\View\Element\Template')) {
                    continue;
                }

                $area = 'frontend';
                if ($module == 'Magento_Install') {
                    $area = 'install';
                } elseif ($module == 'Magento_Adminhtml' || strpos($blockClass, '\\Adminhtml\\')
                    || strpos($blockClass, '\\Backend\\')
                    || $class->isSubclassOf('Magento\Backend\Block\Template')
                ) {
                    $area = 'adminhtml';
                }

                \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Model\App')->loadAreaPart(
                    $area,
                    \Magento\Core\Model\App\Area::PART_CONFIG
                );
                \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
                    ->get('Magento\Config\ScopeInterface')
                    ->setCurrentScope($area);
                \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\App\State')
                    ->setAreaCode($area);

                $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create($blockClass);
                $template = $block->getTemplate();
                if ($template) {
                    $templates[$module . ', ' . $template . ', ' . $blockClass . ', ' . $area] =
                        array($module, $template, $blockClass, $area);
                }
            }
            return $templates;
        } catch (\Exception $e) {
            trigger_error("Corrupted data provider. Last known block instantiation attempt: '{$blockClass}'."
                . " Exception: {$e}", E_USER_ERROR);
        }
    }

    /**
     * @return array
     */
    protected function _getBlocksToSkip()
    {
        $result = array();
        foreach (glob(__DIR__ . '/_files/skip_template_blocks*.php') as $file) {
            $blocks = include $file;
            $result = array_merge($result, $blocks);
        }
        return array_combine($result, $result);
    }
}
