<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\File\Collector\Decorator;

use Magento\Framework\Module\Manager;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\File;
use Magento\Framework\View\File\CollectorInterface;

/**
 * Decorator that filters out view files that belong to modules, output of which is prohibited
 */
class ModuleOutput implements CollectorInterface
{
    /**
     * Subject
     *
     * @var CollectorInterface
     */
    private $subject;

    /**
     * Module manager
     *
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * Constructor
     *
     * @param CollectorInterface $subject
     * @param Manager $moduleManager
     */
    public function __construct(
        CollectorInterface $subject,
        Manager $moduleManager
    ) {
        $this->subject = $subject;
        $this->moduleManager = $moduleManager;
    }

    /**
     * Retrieve files
     *
     * Filter out theme files that belong to inactive modules or ones explicitly configured to not produce any output
     *
     * @param ThemeInterface $theme
     * @param string $filePath
     * @return \Magento\Framework\View\File[]
     */
    public function getFiles(ThemeInterface $theme, $filePath)
    {
        $result = [];
        foreach ($this->subject->getFiles($theme, $filePath) as $file) {
            if ($this->moduleManager->isOutputEnabled($file->getModule())) {
                $result[] = $file;
            }
        }
        return $result;
    }
}
