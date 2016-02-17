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
class Zend_Tool_Project_Context_Zf_TestApplicationControllerFile extends Zend_Tool_Project_Context_Filesystem_File
{

    /**
     * @var string
     */
    protected $_forControllerName = '';

    /**
     * getName()
     *
     * @return string
     */
    public function getName()
    {
        return 'TestApplicationControllerFile';
    }

    /**
     * init()
     *
     * @return Zend_Tool_Project_Context_Zf_TestApplicationControllerFile
     */
    public function init()
    {
        $this->_forControllerName = $this->_resource->getAttribute('forControllerName');
        $this->_filesystemName = ucfirst($this->_forControllerName) . 'ControllerTest.php';
        parent::init();
        return $this;
    }

    /**
     * getPersistentAttributes()
     *
     * @return unknown
     */
    public function getPersistentAttributes()
    {
        $attributes = array();

        if ($this->_forControllerName) {
            $attributes['forControllerName'] = $this->getForControllerName();
        }

        return $attributes;
    }

    public function getForControllerName()
    {
        return $this->_forControllerName;
    }

    /**
     * getContents()
     *
     * @return string
     */
    public function getContents()
    {

        $filter = new Zend_Filter_Word_DashToCamelCase();

        $className = $filter->filter($this->_forControllerName) . 'ControllerTest';

        /* @var $controllerDirectoryResource Zend_Tool_Project_Profile_Resource */
        $controllerDirectoryResource = $this->_resource->getParentResource();
        if ($controllerDirectoryResource->getParentResource()->getName() == 'TestApplicationModuleDirectory') {
            $className = $filter->filter(ucfirst($controllerDirectoryResource->getParentResource()->getForModuleName()))
                . '_' . $className;
        }

        $codeGenFile = new Zend_CodeGenerator_Php_File(array(
            'classes' => array(
                new Zend_CodeGenerator_Php_Class(array(
                    'name' => $className,
                    'extendedClass' => 'Zend_Test_PHPUnit_ControllerTestCase',
                    'methods' => array(
                        new Zend_CodeGenerator_Php_Method(array(
                            'name' => 'setUp',
                            'body' => <<<EOS
\$this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
parent::setUp();
EOS
                            ))
                        )
                    ))
                )
            ));

        return $codeGenFile->generate();
    }

}
