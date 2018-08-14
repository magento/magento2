<?php
/**
 * High-level interface for email templates data that hides format from the client code
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Model\Template;

use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\View\Design\Theme\ThemePackageList;

class Config implements \Magento\Framework\Mail\Template\ConfigInterface
{
    /**
     * @var \Magento\Email\Model\Template\Config\Data
     */
    protected $_dataStorage;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $_moduleReader;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $fileSystem;

    /**
     * @var \Magento\Framework\View\FileSystem
     */
    protected $viewFileSystem;

    /**
     * @var ReadFactory
     */
    private $readDirFactory;

    /**
     * @var ThemePackageList
     */
    private $themePackages;

    /**
     * @param \Magento\Email\Model\Template\Config\Data $dataStorage
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     * @param \Magento\Framework\View\FileSystem $viewFileSystem
     * @param ThemePackageList $themePackages
     * @param ReadFactory $readDirFactory
     */
    public function __construct(
        \Magento\Email\Model\Template\Config\Data $dataStorage,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Magento\Framework\View\FileSystem $viewFileSystem,
        ThemePackageList $themePackages,
        ReadFactory $readDirFactory
    ) {
        $this->_dataStorage = $dataStorage;
        $this->_moduleReader = $moduleReader;
        $this->viewFileSystem = $viewFileSystem;
        $this->themePackages = $themePackages;
        $this->readDirFactory = $readDirFactory;
    }

    /**
     * Return list of all email templates, both default module and theme-specific templates
     *
     * @return array[]
     */
    public function getAvailableTemplates()
    {
        $templates = [];
        foreach (array_keys($this->_dataStorage->get()) as $templateId) {
            $templates[] = [
                'value' => $templateId,
                'label' => $this->getTemplateLabel($templateId),
                'group' => $this->getTemplateModule($templateId),
            ];
            $themeTemplates = $this->getThemeTemplates($templateId);
            $templates = array_merge($templates, $themeTemplates);
        }
        return $templates;
    }

    /**
     * Find all theme-based email templates for a given template ID
     *
     * @param string $templateId
     * @return array[]
     */
    public function getThemeTemplates($templateId)
    {
        $templates = [];

        $area = $this->getTemplateArea($templateId);
        $module = $this->getTemplateModule($templateId);
        $filename = $this->_getInfo($templateId, 'file');

        foreach ($this->themePackages->getThemes() as $theme) {
            if ($theme->getArea() == $area) {
                $themeDir = $this->readDirFactory->create($theme->getPath());
                $file = "$module/email/$filename";
                if ($themeDir->isExist($file)) {
                    $templates[] = [
                        'value' => sprintf(
                            '%s/%s/%s',
                            $templateId,
                            $theme->getVendor(),
                            $theme->getName()
                        ),
                        'label' => sprintf(
                            '%s (%s/%s)',
                            $this->getTemplateLabel($templateId),
                            $theme->getVendor(),
                            $theme->getName()
                        ),
                        'group' => $this->getTemplateModule($templateId),
                    ];
                }
            }
        }

        return $templates;
    }

    /**
     * Parses a template ID and returns an array of templateId and theme
     *
     * @param string $templateId
     * @return array an array of array('templateId' => '...', 'theme' => '...')
     */
    public function parseTemplateIdParts($templateId)
    {
        $parts = [
            'templateId' => $templateId,
            'theme' => null
        ];
        $pattern = "#^(?<templateId>[^/]+)/(?<themeVendor>[^/]+)/(?<themeName>[^/]+)#i";
        if (preg_match($pattern, $templateId, $matches)) {
            $parts['templateId'] = $matches['templateId'];
            $parts['theme'] = $matches['themeVendor'] . '/' . $matches['themeName'];
        }
        return $parts;
    }

    /**
     * Retrieve translated label of an email template
     *
     * @param string $templateId
     * @return \Magento\Framework\Phrase
     */
    public function getTemplateLabel($templateId)
    {
        return __($this->_getInfo($templateId, 'label'));
    }

    /**
     * Retrieve type of an email template
     *
     * @param string $templateId
     * @return string
     */
    public function getTemplateType($templateId)
    {
        return $this->_getInfo($templateId, 'type');
    }

    /**
     * Retrieve fully-qualified name of a module an email template belongs to
     *
     * @param string $templateId
     * @return string
     */
    public function getTemplateModule($templateId)
    {
        return $this->_getInfo($templateId, 'module');
    }

    /**
     * Retrieve the area an email template belongs to
     *
     * @param string $templateId
     * @return string
     */
    public function getTemplateArea($templateId)
    {
        return $this->_getInfo($templateId, 'area');
    }

    /**
     * Retrieve full path to an email template file
     *
     * @param string $templateId
     * @param array|null $designParams
     * @return string
     */
    public function getTemplateFilename($templateId, $designParams = [])
    {
        // If design params aren't passed, then use area/module defined in email_templates.xml
        if (!isset($designParams['area'])) {
            $designParams['area'] = $this->getTemplateArea($templateId);
        }
        $module = $this->getTemplateModule($templateId);
        $designParams['module'] = $module;

        $file = $this->_getInfo($templateId, 'file');
        $filename = $this->getFilename($file, $designParams, $module);

        return $filename;
    }

    /**
     * Retrieve value of a field of an email template
     *
     * @param string $templateId Name of an email template
     * @param string $fieldName Name of a field value of which to return
     * @return string
     * @throws \UnexpectedValueException
     */
    protected function _getInfo($templateId, $fieldName)
    {
        $data = $this->_dataStorage->get();
        if (!isset($data[$templateId])) {
            throw new \UnexpectedValueException("Email template '{$templateId}' is not defined.");
        }
        if (!isset($data[$templateId][$fieldName])) {
            throw new \UnexpectedValueException(
                "Field '{$fieldName}' is not defined for email template '{$templateId}'."
            );
        }
        return $data[$templateId][$fieldName];
    }

    /**
     * @param string $file
     * @param array $designParams
     * @param string $module
     *
     * @return string
     *
     * @throws \UnexpectedValueException
     */
    private function getFilename($file, array $designParams, $module)
    {
        $filename = $this->viewFileSystem->getEmailTemplateFileName($file, $designParams, $module);

        if ($filename === false) {
            throw new \UnexpectedValueException("Template file '{$file}' is not found.");
        }

        return $filename;
    }
}
