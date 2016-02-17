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
 * @version    $Id: ActionMethod.php 20096 2010-01-06 02:05:09Z bkarwin $
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
class Zend_Tool_Project_Context_Zf_TestApplicationActionMethod implements Zend_Tool_Project_Context_Interface
{

    /**
     * @var Zend_Tool_Project_Profile_Resource
     */
    protected $_resource = null;

    /**
     * @var Zend_Tool_Project_Profile_Resource
     */
    protected $_testApplicationControllerResource = null;

    /**
     * @var string
     */
    protected $_testApplicationControllerPath = '';

    /**
     * @var string
     */
    protected $_forActionName = null;

    /**
     * init()
     *
     * @return Zend_Tool_Project_Context_Zf_ActionMethod
     */
    public function init()
    {
        $this->_forActionName = $this->_resource->getAttribute('forActionName');

        $this->_resource->setAppendable(false);
        $this->_testApplicationControllerResource = $this->_resource->getParentResource();
        if (!$this->_testApplicationControllerResource->getContext() instanceof Zend_Tool_Project_Context_Zf_TestApplicationControllerFile) {
            #require_once 'Zend/Tool/Project/Context/Exception.php';
            throw new Zend_Tool_Project_Context_Exception('ActionMethod must be a sub resource of a TestApplicationControllerFile');
        }
        // make the ControllerFile node appendable so we can tack on the actionMethod.
        $this->_resource->getParentResource()->setAppendable(true);

        $this->_testApplicationControllerPath = $this->_testApplicationControllerResource->getContext()->getPath();

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
            'forActionName' => $this->getForActionName()
            );
    }

    /**
     * getName()
     *
     * @return string
     */
    public function getName()
    {
        return 'TestApplicationActionMethod';
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
     * getActionName()
     *
     * @return string
     */
    public function getForActionName()
    {
        return $this->_forActionName;
    }

    /**
     * create()
     *
     * @return Zend_Tool_Project_Context_Zf_ActionMethod
     */
    public function create()
    {
        $file = $this->_testApplicationControllerPath;

        if (!file_exists($file)) {
            #require_once 'Zend/Tool/Project/Context/Exception.php';
            throw new Zend_Tool_Project_Context_Exception(
                'Could not create action within test controller ' . $file
                . ' with action name ' . $this->_forActionName
                );
        }

        $actionParam = $this->getForActionName();
        $controllerParam = $this->_resource->getParentResource()->getForControllerName();
        //$moduleParam = null;//

        /* @var $controllerDirectoryResource Zend_Tool_Project_Profile_Resource */
        $controllerDirectoryResource = $this->_resource->getParentResource()->getParentResource();
        if ($controllerDirectoryResource->getParentResource()->getName() == 'TestApplicationModuleDirectory') {
            $moduleParam = $controllerDirectoryResource->getParentResource()->getForModuleName();
        } else {
            $moduleParam = 'default';
        }



        if ($actionParam == 'index' && $controllerParam == 'Index' && $moduleParam == 'default') {
            $assert = '$this->assertQueryContentContains("div#welcome h3", "This is your project\'s main page");';
        } else {
            $assert = <<<EOS
\$this->assertQueryContentContains(
    'div#view-content p',
    'View script for controller <b>' . \$params['controller'] . '</b> and script/action name <b>' . \$params['action'] . '</b>'
    );
EOS;
        }

        $codeGenFile = Zend_CodeGenerator_Php_File::fromReflectedFileName($file, true, true);
        $codeGenFile->getClass()->setMethod(array(
            'name' => 'test' . ucfirst($actionParam) . 'Action',
            'body' => <<<EOS
\$params = array('action' => '$actionParam', 'controller' => '$controllerParam', 'module' => '$moduleParam');
\$urlParams = \$this->urlizeOptions(\$params);
\$url = \$this->url(\$urlParams);
\$this->dispatch(\$url);

// assertions
\$this->assertModule(\$urlParams['module']);
\$this->assertController(\$urlParams['controller']);
\$this->assertAction(\$urlParams['action']);
$assert

EOS
            ));

        file_put_contents($file, $codeGenFile->generate());

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
     * hasActionMethod()
     *
     * @param string $controllerPath
     * @param string $actionName
     * @return bool
     */
    /*
    public static function hasActionMethod($controllerPath, $actionName)
    {
        if (!file_exists($controllerPath)) {
            return false;
        }

        $controllerCodeGenFile = Zend_CodeGenerator_Php_File::fromReflectedFileName($controllerPath, true, true);
        return $controllerCodeGenFile->getClass()->hasMethod('test' . $actionName . 'Action');
    }
    */

}
