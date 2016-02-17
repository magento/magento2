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
 * @see Zend_Tool_Project_Context_Filesystem_File
 */
#require_once 'Zend/Tool/Project/Context/Filesystem/File.php';

/**
 * @see Zend_CodeGenerator_Php_File
 */
#require_once 'Zend/CodeGenerator/Php/File.php';

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
class Zend_Tool_Project_Context_Zf_ProjectProviderFile extends Zend_Tool_Project_Context_Filesystem_File
{

    /**
     * @var string
     */
    protected $_projectProviderName = null;

    /**
     * @var array
     */
    protected $_actionNames = array();

    /**
     * init()
     *
     * @return Zend_Tool_Project_Context_Zf_ProjectProviderFile
     */
    public function init()
    {

        $this->_projectProviderName = $this->_resource->getAttribute('projectProviderName');
        $this->_actionNames = $this->_resource->getAttribute('actionNames');
        $this->_filesystemName = ucfirst($this->_projectProviderName) . 'Provider.php';

        if (strpos($this->_actionNames, ',')) {
            $this->_actionNames = explode(',', $this->_actionNames);
        } else {
            $this->_actionNames = ($this->_actionNames) ? array($this->_actionNames) : array();
        }

        parent::init();
        return $this;
    }

    /**
     * getPersistentAttributes()
     *
     * @return array
     */
    public function getPersistentAttributes()
    {
        return array(
            'projectProviderName' => $this->getProjectProviderName(),
            'actionNames' => implode(',', $this->_actionNames)
            );
    }

    /**
     * getName()
     *
     * @return string
     */
    public function getName()
    {
        return 'ProjectProviderFile';
    }

    /**
     * getProjectProviderName()
     *
     * @return string
     */
    public function getProjectProviderName()
    {
        return $this->_projectProviderName;
    }

    /**
     * getContents()
     *
     * @return string
     */
    public function getContents()
    {

        $filter = new Zend_Filter_Word_DashToCamelCase();

        $className = $filter->filter($this->_projectProviderName) . 'Provider';

        $class = new Zend_CodeGenerator_Php_Class(array(
            'name' => $className,
            'extendedClass' => 'Zend_Tool_Project_Provider_Abstract'
            ));

        $methods = array();
        foreach ($this->_actionNames as $actionName) {
            $methods[] = new Zend_CodeGenerator_Php_Method(array(
                'name' => $actionName,
                'body' => '        /** @todo Implementation */'
                ));
        }

        if ($methods) {
            $class->setMethods($methods);
        }

        $codeGenFile = new Zend_CodeGenerator_Php_File(array(
            'requiredFiles' => array(
                'Zend/Tool/Project/Provider/Abstract.php',
                'Zend/Tool/Project/Provider/Exception.php'
                ),
            'classes' => array($class)
            ));

        return $codeGenFile->generate();
    }

}
