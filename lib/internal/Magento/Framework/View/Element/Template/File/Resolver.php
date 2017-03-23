<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\Template\File;

/**
 * Class Resolver
 * @package Magento\Framework\View\Element\Template\File
 */
class Resolver
{
    /**
     * Template files map
     *
     * @var []
     */
    protected $_templateFilesMap = [];

    /**
     * View filesystem
     *
     * @var \Magento\Framework\View\FileSystem
     */
    protected $_viewFileSystem;

    /**
     * Class constructor
     *
     * @param \Magento\Framework\View\FileSystem $viewFileSystem
     */
    public function __construct(\Magento\Framework\View\FileSystem $viewFileSystem)
    {
        $this->_viewFileSystem = $viewFileSystem;
    }

    /**
     * Get template filename
     *
     * @param string $template
     * @param [] $params
     * @return string|bool
     */
    public function getTemplateFileName($template, $params = [])
    {
        $key = $template . '_' . serialize($params);
        if (!isset($this->_templateFilesMap[$key])) {
            $this->_templateFilesMap[$key] = $this->_viewFileSystem->getTemplateFileName($template, $params);
        }
        return $this->_templateFilesMap[$key];
    }
}
