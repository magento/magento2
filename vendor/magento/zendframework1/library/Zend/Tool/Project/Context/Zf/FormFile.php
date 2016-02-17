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
 * @see Zend_Tool_Project_Context_Zf_AbstractClassFile
 */
#require_once 'Zend/Tool/Project/Context/Zf/AbstractClassFile.php';

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
class Zend_Tool_Project_Context_Zf_FormFile extends Zend_Tool_Project_Context_Zf_AbstractClassFile
{

    /**
     * @var string
     */
    protected $_formName = 'Base';

    /**
     * @var string
     */
    protected $_filesystemName = 'formName';

    /**
     * init()
     *
     */
    public function init()
    {
        $this->_formName = $this->_resource->getAttribute('formName');
        $this->_filesystemName = ucfirst($this->_formName) . '.php';
        parent::init();
    }

    /**
     * getPersistentAttributes
     *
     * @return array
     */
    public function getPersistentAttributes()
    {
        return array(
            'formName' => $this->getFormName()
            );
    }

    /**
     * getName()
     *
     * @return string
     */
    public function getName()
    {
        return 'FormFile';
    }

    public function getFormName()
    {
        return $this->_formName;
    }

    public function getContents()
    {

        $className = $this->getFullClassName($this->_formName, 'Form');

        $codeGenFile = new Zend_CodeGenerator_Php_File(array(
            'fileName' => $this->getPath(),
            'classes' => array(
                new Zend_CodeGenerator_Php_Class(array(
                    'name' => $className,
                    'extendedClass' => 'Zend_Form',
                    'methods' => array(
                        new Zend_CodeGenerator_Php_Method(array(
                            'name' => 'init',
                            'body' => '/* Form Elements & Other Definitions Here ... */',
                            ))
                        )

                    ))
                )
            ));
        return $codeGenFile->generate();
    }
}
