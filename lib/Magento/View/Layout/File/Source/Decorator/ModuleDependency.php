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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\View\Layout\File\Source\Decorator;

use Magento\View\Layout\File\SourceInterface;
use Magento\View\Layout\File;
use Magento\Module\ModuleListInterface;
use Magento\View\Design\ThemeInterface;

/**
 * Decorator that sorts layout files according to dependencies between modules they belong to
 */
class ModuleDependency implements SourceInterface
{
    /**
     * Subject
     *
     * @var SourceInterface
     */
    private $subject;

    /**
     * Module list
     *
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * Fully-qualified names of modules, ordered by their priority in the system
     *
     * @var array|null
     */
    private $orderedModules;

    /**
     * Constructor
     *
     * @param SourceInterface $subject
     * @param ModuleListInterface $listInterface
     */
    public function __construct(
        SourceInterface $subject,
        ModuleListInterface $listInterface
    ) {
        $this->subject = $subject;
        $this->moduleList = $listInterface;
    }

    /**
     * Retrieve layout files, sorted by the priority of modules they belong to
     *
     * @param ThemeInterface $theme
     * @param string $filePath
     * @return \Magento\View\Layout\File[]
     */
    public function getFiles(ThemeInterface $theme, $filePath = '*')
    {
        $result = $this->subject->getFiles($theme, $filePath);
        usort($result, array($this, 'compareFiles'));
        return $result;
    }

    /**
     * Compare layout files according to the priority of modules they belong to. To be used as a callback for sorting.
     *
     * @param File $fileOne
     * @param File $fileTwo
     * @return int
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
     */
    protected function getModulePriority($moduleName)
    {
        if ($this->orderedModules === null) {
            $this->orderedModules = array();
            foreach ($this->moduleList->getModules() as $module) {
                $this->orderedModules[] = $module['name'];
            }
        }
        $result = array_search($moduleName, $this->orderedModules);
        // Assume unknown modules have the same priority, distinctive from known modules
        if ($result === false) {
            return -1;
        }
        return $result;
    }
}
