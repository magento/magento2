<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\File\Collector\Decorator;

use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\File;
use Magento\Framework\View\File\CollectorInterface;

class ModuleEnabled implements CollectorInterface
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
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * Constructor
     *
     * @param CollectorInterface $subject
     * @param ModuleManager $moduleManager
     */
    public function __construct(
        CollectorInterface $subject,
        ModuleManager $moduleManager
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
     * @return File[]
     */
    public function getFiles(ThemeInterface $theme, $filePath): array
    {
        $result = [];
        foreach ($this->subject->getFiles($theme, $filePath) as $file) {
            if ($this->moduleManager->isEnabled($file->getModule())) {
                $result[] = $file;
            }
        }
        return $result;
    }
}
