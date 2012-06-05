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
 * @package     Mage_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @group integrity
 */
class Integrity_Theme_TemplateFilesTest extends Magento_Test_TestCase_IntegrityAbstract
{
    /**
     * Note that data provider is not used in conventional way in order to not overwhelm test statistics
     */
    public function testTemplates()
    {
        $invalidTemplates = array();
        foreach ($this->templatesDataProvider() as $template) {
            list($area, $package, $theme, $module, $file, $xml) = $template;
            $params = array(
                '_area'     => $area,
                '_package'  => $package,
                '_theme'    => $theme,
                '_module'   => $module
            );
            try {
                $templateFilename = Mage::getDesign()->getFilename($file, $params);
                $this->assertFileExists($templateFilename);
            } catch (PHPUnit_Framework_ExpectationFailedException $e) {
                $invalidTemplates[] = "{$templateFilename}\n"
                    . "Parameters: {$area}/{$package}/{$theme} {$module}::{$file}\nLayout update: {$xml}";
            }
        }

        $this->assertEmpty($invalidTemplates, "Invalid templates found:\n\n" . implode("\n-----\n", $invalidTemplates));
    }

    public function templatesDataProvider()
    {
        $templates = array();

        $themes = $this->_getDesignThemes();
        foreach ($themes as $view) {
            list($area, $package, $theme) = explode('/', $view);
            $layoutUpdate = new Mage_Core_Model_Layout_Update(
                array('area' => $area, 'package' => $package, 'theme' => $theme)
            );
            $layoutTemplates = $this->_getLayoutTemplates($layoutUpdate->getFileLayoutUpdatesXml());
            foreach ($layoutTemplates as $templateData) {
                $templates[] = array_merge(array($area, $package, $theme), $templateData);
            }
        }

        return $templates;
    }

    /**
     * Get templates list that are defined in layout
     *
     * @param  SimpleXMLElement $layoutXml
     * @return array
     */
    protected function _getLayoutTemplates($layoutXml)
    {
        $templates = array();

        $blocks = $layoutXml->xpath('//block');
        foreach ($blocks as $block) {
            $attributes = $block->attributes();
            if (isset($attributes['template'])) {
                $module = $this->_getBlockModule($block);
                if (!$this->_isTemplateForDisabledModule($module, (string)$attributes['template'])) {
                    $templates[] = array($module, (string)$attributes['template'], $block->asXML());
                }
            }
        }

        $layoutTemplates = $layoutXml->xpath('//template');
        foreach ($layoutTemplates as $template) {
            $action = $template->xpath("parent::*");
            $attributes = $action[0]->attributes();
            switch ($attributes['method']) {
                case 'setTemplate':
                    $parent = $action[0]->xpath("parent::*");
                    $attributes = $parent[0]->attributes();
                    $referenceName = (string)$attributes['name'];
                    $block = $layoutXml->xpath(
                        "//block[@name='{$referenceName}'] | //reference[@name='{$referenceName}']");
                    $module = $this->_getBlockModule($block[0]);
                    if (!$template->attributes() && !$this->_isTemplateForDisabledModule($module, (string)$template)) {
                        $templates[] = array($module, (string)$template, $parent[0]->asXml());
                    }
                    break;
                case 'addPriceBlockType':
                case 'addRowItemRender':
                case 'addItemRender':
                case 'addOptionRenderer':
                case 'addInformationRenderer':
                case 'addMergeSettingsBlockType':
                    $blockType = $action[0]->xpath('block');
                    $module = $this->_getBlockModule($blockType[0]);
                    if (!$this->_isTemplateForDisabledModule($module, (string)$template)) {
                        $templates[] = array($module, (string)$template, $action[0]->asXml());
                    }
                    break;
                default:
                    break;
            }
        }
        return $templates;
    }

    /**
     * Get module name based on block definition in xml layout
     *
     * @param  SimpleXMLElement $xmlNode
     * @return string
     */
    protected function _getBlockModule($xmlNode)
    {
        $attributes = $xmlNode->attributes();
        if (isset($attributes['type'])) {
            $class = Mage::getConfig()->getBlockClassName((string) $attributes['type']);
        } else {
            $class = Mage::getConfig()->getBlockClassName((string) $xmlNode);
        }
        $blockModule = substr($class, 0, strpos($class, '_Block'));
        return $blockModule;
    }

    /**
     * Returns whether template belongs to a disabled module
     *
     * @param string $blockModule Module of a block that will render this template
     * @param string $template
     * @return bool
     */
    protected function _isTemplateForDisabledModule($blockModule, $template)
    {
        $enabledModules = $this->_getEnabledModules();

        if (!isset($enabledModules[$blockModule])) {
            return true;
        }
        return $this->_isFileForDisabledModule($template);
    }
}
