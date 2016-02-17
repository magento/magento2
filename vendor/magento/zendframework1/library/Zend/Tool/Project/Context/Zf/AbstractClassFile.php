<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Tool
 * @subpackage Framework
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * Zend_Tool_Project_Context_Filesystem_File
 */
#require_once 'Zend/Tool/Project/Context/Filesystem/File.php';

/**
 * This class is the front most class for utilizing Zend_Tool_Project
 *
 * A profile is a hierarchical set of resources that keep track of
 * items within a specific project.
 *
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Tool_Project_Context_Zf_AbstractClassFile extends Zend_Tool_Project_Context_Filesystem_File
{

    /**
     * getFullClassName()
     *
     * @param string $localClassName
     * @param string $classContextName
     */
    public function getFullClassName($localClassName, $classContextName = null)
    {

        // find the ApplicationDirectory OR ModuleDirectory
        $currentResource = $this->_resource;
        do {
            $resourceName = $currentResource->getName();
            if ($resourceName == 'ApplicationDirectory' || $resourceName == 'ModuleDirectory') {
                $containingResource = $currentResource;
                break;
            }
        } while ($currentResource instanceof Zend_Tool_Project_Profile_Resource
            && $currentResource = $currentResource->getParentResource());

        $fullClassName = '';

        // go find the proper prefix
        if (isset($containingResource)) {
            if ($containingResource->getName() == 'ApplicationDirectory') {
                $prefix = $containingResource->getAttribute('classNamePrefix');
                $fullClassName = $prefix;
            } elseif ($containingResource->getName() == 'ModuleDirectory') {
                $filter = new Zend_Filter_Word_DashToCamelCase();
                $prefix = $filter->filter(ucfirst($containingResource->getAttribute('moduleName'))) . '_';
                $fullClassName = $prefix;
            }
        }

        if ($classContextName) {
            $fullClassName .= rtrim($classContextName, '_') . '_';
        }
        $fullClassName .= $localClassName;

        return $fullClassName;
    }

}
