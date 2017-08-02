<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\File\Collector\Decorator;

use Magento\Framework\Module\Manager;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\File;
use Magento\Framework\View\File\CollectorInterface;

/**
 * Decorator that filters out view files that belong to modules, output of which is prohibited
 * @since 2.0.0
 */
class ModuleOutput implements CollectorInterface
{
    /**
     * Subject
     *
     * @var CollectorInterface
     * @since 2.0.0
     */
    private $subject;

    /**
     * Module manager
     *
     * @var \Magento\Framework\Module\Manager
     * @since 2.0.0
     */
    private $moduleManager;

    /**
     * Constructor
     *
     * @param CollectorInterface $subject
     * @param Manager $moduleManager
     * @since 2.0.0
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
     * @since 2.0.0
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
