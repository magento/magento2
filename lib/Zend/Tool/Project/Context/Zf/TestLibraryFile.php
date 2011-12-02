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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: TestLibraryFile.php 20096 2010-01-06 02:05:09Z bkarwin $
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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tool_Project_Context_Zf_TestLibraryFile extends Zend_Tool_Project_Context_Filesystem_File
{

    /**
     * @var string
     */
    protected $_forClassName = '';

    /**
     * getName()
     *
     * @return string
     */
    public function getName()
    {
        return 'TestLibraryFile';
    }

    /**
     * init()
     *
     * @return Zend_Tool_Project_Context_Zf_TestLibraryFile
     */
    public function init()
    {
        $this->_forClassName = $this->_resource->getAttribute('forClassName');
        $this->_filesystemName = ucfirst(ltrim(strrchr($this->_forClassName, '_'), '_')) . 'Test.php';
        parent::init();
        return $this;
    }

    /**
     * getContents()
     *
     * @return string
     */
    public function getContents()
    {

        $filter = new Zend_Filter_Word_DashToCamelCase();

        $className = $filter->filter($this->_forClassName) . 'Test';

        $codeGenFile = new Zend_CodeGenerator_Php_File(array(
            'requiredFiles' => array(
                'PHPUnit/Framework/TestCase.php'
                ),
            'classes' => array(
                new Zend_CodeGenerator_Php_Class(array(
                    'name' => $className,
                    'extendedClass' => 'PHPUnit_Framework_TestCase',
                    'methods' => array(
                        new Zend_CodeGenerator_Php_Method(array(
                            'name' => 'setUp',
                            'body' => '        /* Setup Routine */'
                            )),
                        new Zend_CodeGenerator_Php_Method(array(
                            'name' => 'tearDown',
                            'body' => '        /* Tear Down Routine */'
                            ))
                        )
                    ))
                )
            ));

        return $codeGenFile->generate();
    }

}