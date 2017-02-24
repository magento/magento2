<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Theme;

class TemplateFilesTest extends \Magento\TestFramework\TestCase\AbstractIntegrity
{
    /**
     * Note that data provider is not used in conventional way in order to not overwhelm test statistics
     */
    public function testTemplates()
    {
        $invalidTemplates = [];
        foreach ($this->templatesDataProvider() as $template) {
            list($area, $themeId, $module, $file, $xml) = $template;
            $params = ['area' => $area, 'themeId' => $themeId, 'module' => $module];
            try {
                $templateFilename = \Magento\TestFramework\Helper\Bootstrap::getObjectmanager()
                    ->get(\Magento\Framework\View\FileSystem::class)
                    ->getTemplateFileName($file, $params);
                $this->assertFileExists($templateFilename);
            } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
                $invalidTemplates[] = "File \"{$templateFilename}\" does not exist." .
                    PHP_EOL .
                    "Parameters: {$area}/{$themeId} {$module}::{$file}" .
                    PHP_EOL .
                    'Layout update: ' .
                    $xml;
            }
        }

        $this->assertEmpty(
            $invalidTemplates,
            "Invalid templates found:\n\n" . implode("\n-----\n", $invalidTemplates)
        );
    }

    public function templatesDataProvider()
    {
        $templates = [];

        $themes = $this->_getDesignThemes();
        foreach ($themes as $theme) {
            /** @var \Magento\Framework\View\Layout\ProcessorInterface $layoutUpdate */
            $layoutUpdate = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                \Magento\Framework\View\Layout\ProcessorInterface::class,
                ['theme' => $theme]
            );
            $layoutTemplates = $this->_getLayoutTemplates($layoutUpdate->getFileLayoutUpdatesXml());
            foreach ($layoutTemplates as $templateData) {
                $templates[] = array_merge([$theme->getArea(), $theme->getId()], $templateData);
            }
        }

        return $templates;
    }

    /**
     * Get templates list that are defined in layout
     *
     * @param  \SimpleXMLElement $layoutXml
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _getLayoutTemplates($layoutXml)
    {
        $templates = [];

        $blocks = $layoutXml->xpath('//block');
        foreach ($blocks as $block) {
            $attributes = $block->attributes();
            if (isset($attributes['template'])) {
                $module = $this->_getBlockModule($block);
                if (!$this->_isTemplateForDisabledModule($module, (string)$attributes['template'])) {
                    $templates[] = [$module, (string)$attributes['template'], $block->asXML()];
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
                        "//block[@name='{$referenceName}'] | //referenceBlock[@name='{$referenceName}']"
                    );
                    $module = $this->_getBlockModule($block[0]);
                    if (!$template->attributes() && !$this->_isTemplateForDisabledModule($module, (string)$template)) {
                        $templates[] = [$module, (string)$template, $parent[0]->asXml()];
                    }
                    break;
                case 'addInformationRenderer':
                case 'addMergeSettingsBlockType':
                    $blockType = $action[0]->xpath('block');
                    $module = $this->_getBlockModule($blockType[0]);
                    if (!$this->_isTemplateForDisabledModule($module, (string)$template)) {
                        $templates[] = [$module, (string)$template, $action[0]->asXml()];
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
     * @param  \SimpleXMLElement $xmlNode
     * @return string
     */
    protected function _getBlockModule($xmlNode)
    {
        $attributes = $xmlNode->attributes();
        if (isset($attributes['type'])) {
            $class = (string)$attributes['type'];
        } else {
            $class = (string)$xmlNode;
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
