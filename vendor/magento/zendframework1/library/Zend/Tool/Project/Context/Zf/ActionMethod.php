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
 * @see Zend_Tool_Project_Context_Interface
 */
#require_once 'Zend/Tool/Project/Context/Interface.php';

/**
 * @see Zend_Reflection_File
 */
#require_once 'Zend/Reflection/File.php';

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
class Zend_Tool_Project_Context_Zf_ActionMethod implements Zend_Tool_Project_Context_Interface
{

    /**
     * @var Zend_Tool_Project_Profile_Resource
     */
    protected $_resource = null;

    /**
     * @var Zend_Tool_Project_Profile_Resource
     */
    protected $_controllerResource = null;

    /**
     * @var string
     */
    protected $_controllerPath = '';

    /**
     * @var string
     */
    protected $_actionName = null;

    /**
     * init()
     *
     * @return Zend_Tool_Project_Context_Zf_ActionMethod
     */
    public function init()
    {
        $this->_actionName = $this->_resource->getAttribute('actionName');

        $this->_resource->setAppendable(false);
        $this->_controllerResource = $this->_resource->getParentResource();
        if (!$this->_controllerResource->getContext() instanceof Zend_Tool_Project_Context_Zf_ControllerFile) {
            #require_once 'Zend/Tool/Project/Context/Exception.php';
            throw new Zend_Tool_Project_Context_Exception('ActionMethod must be a sub resource of a ControllerFile');
        }
        // make the ControllerFile node appendable so we can tack on the actionMethod.
        $this->_resource->getParentResource()->setAppendable(true);

        $this->_controllerPath = $this->_controllerResource->getContext()->getPath();

        /*
         * This code block is now commented, its doing to much for init()
         *
        if ($this->_controllerPath != '' && self::hasActionMethod($this->_controllerPath, $this->_actionName)) {
            #require_once 'Zend/Tool/Project/Context/Exception.php';
            throw new Zend_Tool_Project_Context_Exception('An action named ' . $this->_actionName . 'Action already exists in this controller');
        }
        */

        return $this;
    }

    /**
     * getPersistentAttributes
     *
     * @return array
     */
    public function getPersistentAttributes()
    {
        return array(
            'actionName' => $this->getActionName()
            );
    }

    /**
     * getName()
     *
     * @return string
     */
    public function getName()
    {
        return 'ActionMethod';
    }

    /**
     * setResource()
     *
     * @param Zend_Tool_Project_Profile_Resource $resource
     * @return Zend_Tool_Project_Context_Zf_ActionMethod
     */
    public function setResource(Zend_Tool_Project_Profile_Resource $resource)
    {
        $this->_resource = $resource;
        return $this;
    }

    /**
     * setActionName()
     *
     * @param string $actionName
     * @return Zend_Tool_Project_Context_Zf_ActionMethod
     */
    public function setActionName($actionName)
    {
        $this->_actionName = $actionName;
        return $this;
    }

    /**
     * getActionName()
     *
     * @return string
     */
    public function getActionName()
    {
        return $this->_actionName;
    }

    /**
     * create()
     *
     * @return Zend_Tool_Project_Context_Zf_ActionMethod
     */
    public function create()
    {
        if (self::createActionMethod($this->_controllerPath, $this->_actionName) === false) {
            #require_once 'Zend/Tool/Project/Context/Exception.php';
            throw new Zend_Tool_Project_Context_Exception(
                'Could not create action within controller ' . $this->_controllerPath
                . ' with action name ' . $this->_actionName
                );
        }
        return $this;
    }

    /**
     * delete()
     *
     * @return Zend_Tool_Project_Context_Zf_ActionMethod
     */
    public function delete()
    {
        // @todo do this
        return $this;
    }

    /**
     * createAcionMethod()
     *
     * @param string $controllerPath
     * @param string $actionName
     * @param string $body
     * @return true
     */
    public static function createActionMethod($controllerPath, $actionName, $body = '        // action body')
    {
        if (!file_exists($controllerPath)) {
            return false;
        }

        $controllerCodeGenFile = Zend_CodeGenerator_Php_File::fromReflectedFileName($controllerPath, true, true);
        $controllerCodeGenFile->getClass()->setMethod(array(
            'name' => $actionName . 'Action',
            'body' => $body
            ));

        file_put_contents($controllerPath, $controllerCodeGenFile->generate());
        return true;
    }

    /**
     * hasActionMethod()
     *
     * @param string $controllerPath
     * @param string $actionName
     * @return bool
     */
    public static function hasActionMethod($controllerPath, $actionName)
    {
        if (!file_exists($controllerPath)) {
            return false;
        }

        $controllerCodeGenFile = Zend_CodeGenerator_Php_File::fromReflectedFileName($controllerPath, true, true);
        return $controllerCodeGenFile->getClass()->hasMethod($actionName . 'Action');
    }

}
