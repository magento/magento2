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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Autoload;

use \Magento\Framework\App\Filesystem\DirectoryList;
use \Magento\Framework\Autoload\AutoloaderInterface;
use \Magento\Framework\Filesystem\FileResolver;

/**
 * Utility class for populating an autoloader with application-specific information for PSR-0 and PSR-4 mappings
 * and include-path contents
 */
class Populator
{

    /**
     * @param AutoloaderInterface $registry
     * @param DirectoryList $dirList
     * @return void
     */
    public static function populateMappings(AutoloaderInterface $autoloader, DirectoryList $dirList)
    {
        $modulesDir = $dirList->getPath(DirectoryList::MODULES);
        $generationDir = $dirList->getPath(DirectoryList::GENERATION);
        $frameworkDir = $dirList->getPath(DirectoryList::LIB_INTERNAL);

        $autoloader->addPsr4('Magento\\', [$modulesDir . '/Magento/', $generationDir . '/Magento/'], true);
        $autoloader->addPsr4('Zend\\Soap\\', $modulesDir . '/Zend/Soap/', true);
        $autoloader->addPsr4('Zend\\', $frameworkDir . '/Zend/', true);

        $autoloader->addPsr0('Apache_', $frameworkDir, true);
        $autoloader->addPsr0('Cm_', $frameworkDir, true);
        $autoloader->addPsr0('Credis_', $frameworkDir, true);
        $autoloader->addPsr0('Less_', $frameworkDir, true);
        $autoloader->addPsr0('Symfony\\', $frameworkDir, true);
        $autoloader->addPsr0('Zend_Date', $modulesDir, true);
        $autoloader->addPsr0('Zend_Mime', $modulesDir, true);
        $autoloader->addPsr0('Zend_', $frameworkDir, true);
        $autoloader->addPsr0('Zend\\', $frameworkDir, true);

        /** Required for Zend functionality */
        FileResolver::addIncludePath($frameworkDir);

        /** Required for code generation to occur */
        FileResolver::addIncludePath($generationDir);
    }
}
