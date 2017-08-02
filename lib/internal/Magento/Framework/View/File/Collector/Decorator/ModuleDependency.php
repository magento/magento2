<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\File\Collector\Decorator;

use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\File;
use Magento\Framework\View\File\CollectorInterface;

/**
 * Decorator that sorts view files according to dependencies between modules they belong to
 * @since 2.0.0
 */
class ModuleDependency implements CollectorInterface
{
    /**
     * Subject
     *
     * @var CollectorInterface
     * @since 2.0.0
     */
    private $subject;

    /**
     * Module list
     *
     * @var ModuleListInterface
     * @since 2.0.0
     */
    private $moduleList;

    /**
     * Fully-qualified names of modules, ordered by their priority in the system
     *
     * @var array|null
     * @since 2.0.0
     */
    private $orderedModules;

    /**
     * Constructor
     *
     * @param CollectorInterface $subject
     * @param ModuleListInterface $listInterface
     * @since 2.0.0
     */
    public function __construct(
        CollectorInterface $subject,
        ModuleListInterface $listInterface
    ) {
        $this->subject = $subject;
        $this->moduleList = $listInterface;
    }

    /**
     * Retrieve view files, sorted by the priority of modules they belong to
     *
     * @param ThemeInterface $theme
     * @param string $filePath
     * @return \Magento\Framework\View\File[]
     * @since 2.0.0
     */
    public function getFiles(ThemeInterface $theme, $filePath)
    {
        $result = $this->subject->getFiles($theme, $filePath);
        usort($result, [$this, 'compareFiles']);
        return $result;
    }

    /**
     * Compare view files according to the priority of modules they belong to. To be used as a callback for sorting.
     *
     * @param File $fileOne
     * @param File $fileTwo
     * @return int
     * @since 2.0.0
     */
    public function compareFiles(File $fileOne, File $fileTwo)
    {
        if ($fileOne->getModule() == $fileTwo->getModule()) {
            return strcmp($fileOne->getName(), $fileTwo->getName());
        }
        $moduleOnePriority = $this->getModulePriority($fileOne->getModule());
        $moduleTwoPriority = $this->getModulePriority($fileTwo->getModule());
        if ($moduleOnePriority == $moduleTwoPriority) {
            return strcmp($fileOne->getModule(), $fileTwo->getModule());
        }
        return ($moduleOnePriority < $moduleTwoPriority ? -1 : 1);
    }

    /**
     * Retrieve priority of a module relatively to other modules in the system
     *
     * @param string $moduleName
     * @return int
     * @since 2.0.0
     */
    protected function getModulePriority($moduleName)
    {
        if ($this->orderedModules === null) {
            $this->orderedModules = $this->moduleList->getNames();
        }
        $result = array_search($moduleName, $this->orderedModules);
        // Assume unknown modules have the same priority, distinctive from known modules
        if ($result === false) {
            return -1;
        }
        return $result;
    }
}
