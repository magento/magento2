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
 * @version    $Id: Action.php 20851 2010-02-02 21:45:51Z ralph $
 */

/**
 * @see Zend_Tool_Project_Provider_Abstract
 */
#require_once 'Zend/Tool/Project/Provider/Abstract.php';

/**
 * @see Zend_Tool_Framework_Provider_Pretendable
 */
#require_once 'Zend/Tool/Framework/Provider/Pretendable.php';

/**
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tool_Project_Provider_Action
    extends Zend_Tool_Project_Provider_Abstract
    implements Zend_Tool_Framework_Provider_Pretendable
{

    /**
     * createResource()
     *
     * @param Zend_Tool_Project_Profile $profile
     * @param string $actionName
     * @param string $controllerName
     * @param string $moduleName
     * @return Zend_Tool_Project_Profile_Resource
     */
    public static function createResource(Zend_Tool_Project_Profile $profile, $actionName, $controllerName, $moduleName = null)
    {

        if (!is_string($actionName)) {
            throw new Zend_Tool_Project_Provider_Exception('Zend_Tool_Project_Provider_Action::createResource() expects \"actionName\" is the name of a action resource to create.');
        }

        if (!is_string($controllerName)) {
            throw new Zend_Tool_Project_Provider_Exception('Zend_Tool_Project_Provider_Action::createResource() expects \"controllerName\" is the name of a controller resource to create.');
        }

        $controllerFile = self::_getControllerFileResource($profile, $controllerName, $moduleName);

        $actionMethod = $controllerFile->createResource('ActionMethod', array('actionName' => $actionName));

        return $actionMethod;
    }

    /**
     * hasResource()
     *
     * @param Zend_Tool_Project_Profile $profile
     * @param string $actionName
     * @param string $controllerName
     * @param string $moduleName
     * @return Zend_Tool_Project_Profile_Resource
     */
    public static function hasResource(Zend_Tool_Project_Profile $profile, $actionName, $controllerName, $moduleName = null)
    {
        if (!is_string($actionName)) {
            throw new Zend_Tool_Project_Provider_Exception('Zend_Tool_Project_Provider_Action::createResource() expects \"actionName\" is the name of a action resource to create.');
        }

        if (!is_string($controllerName)) {
            throw new Zend_Tool_Project_Provider_Exception('Zend_Tool_Project_Provider_Action::createResource() expects \"controllerName\" is the name of a controller resource to create.');
        }

        $controllerFile = self::_getControllerFileResource($profile, $controllerName, $moduleName);

        if ($controllerFile == null) {
            throw new Zend_Tool_Project_Provider_Exception('Controller ' . $controllerName . ' was not found.');
        }
       
        return (($controllerFile->search(array('actionMethod' => array('actionName' => $actionName)))) instanceof Zend_Tool_Project_Profile_Resource);
    }

    /**
     * _getControllerFileResource()
     *
     * @param Zend_Tool_Project_Profile $profile
     * @param string $controllerName
     * @param string $moduleName
     * @return Zend_Tool_Project_Profile_Resource
     */
    protected static function _getControllerFileResource(Zend_Tool_Project_Profile $profile, $controllerName, $moduleName = null)
    {
        $profileSearchParams = array();

        if ($moduleName != null && is_string($moduleName)) {
            $profileSearchParams = array('modulesDirectory', 'moduleDirectory' => array('moduleName' => $moduleName));
        }

        $profileSearchParams[] = 'controllersDirectory';
        $profileSearchParams['controllerFile'] = array('controllerName' => $controllerName);

        return $profile->search($profileSearchParams);
    }

    /**
     * create()
     *
     * @param string $name           Action name for controller, in camelCase format.
     * @param string $controllerName Controller name action should be applied to.
     * @param bool $viewIncluded     Whether the view should the view be included.
     * @param string $module         Module name action should be applied to.
     */
    public function create($name, $controllerName = 'Index', $viewIncluded = true, $module = null)
    {

        $this->_loadProfile();

        // Check that there is not a dash or underscore, return if doesnt match regex
        if (preg_match('#[_-]#', $name)) {
            throw new Zend_Tool_Project_Provider_Exception('Action names should be camel cased.');
        }
        
        $originalName = $name;
        $originalControllerName = $controllerName;
        
        // ensure it is camelCase (lower first letter)
        $name = strtolower(substr($name, 0, 1)) . substr($name, 1);
        
        // ensure controller is MixedCase
        $controllerName = ucfirst($controllerName);
        
        if (self::hasResource($this->_loadedProfile, $name, $controllerName, $module)) {
            throw new Zend_Tool_Project_Provider_Exception('This controller (' . $controllerName . ') already has an action named (' . $name . ')');
        }
        
        $actionMethod = self::createResource($this->_loadedProfile, $name, $controllerName, $module);

        // get request/response object
        $request = $this->_registry->getRequest();
        $response = $this->_registry->getResponse();
        
        // alert the user about inline converted names
        $tense = (($request->isPretend()) ? 'would be' : 'is');
        
        if ($name !== $originalName) {
            $response->appendContent(
                'Note: The canonical action name that ' . $tense
                    . ' used with other providers is "' . $name . '";'
                    . ' not "' . $originalName . '" as supplied',
                array('color' => array('yellow'))
                );
        }
        
        if ($controllerName !== $originalControllerName) {
            $response->appendContent(
                'Note: The canonical controller name that ' . $tense
                    . ' used with other providers is "' . $controllerName . '";'
                    . ' not "' . $originalControllerName . '" as supplied',
                array('color' => array('yellow'))
                );
        }
        
        unset($tense);
        
        if ($request->isPretend()) {
            $response->appendContent(
                'Would create an action named ' . $name .
                ' inside controller at ' . $actionMethod->getParentResource()->getContext()->getPath()
                );
        } else {
            $response->appendContent(
                'Creating an action named ' . $name .
                ' inside controller at ' . $actionMethod->getParentResource()->getContext()->getPath()
                );
            $actionMethod->create();
            $this->_storeProfile();
        }

        if ($viewIncluded) {
            $viewResource = Zend_Tool_Project_Provider_View::createResource($this->_loadedProfile, $name, $controllerName, $module);

            if ($this->_registry->getRequest()->isPretend()) {
                $response->appendContent(
                    'Would create a view script for the ' . $name . ' action method at ' . $viewResource->getContext()->getPath()
                    );
            } else {
                $response->appendContent(
                    'Creating a view script for the ' . $name . ' action method at ' . $viewResource->getContext()->getPath()
                    );
                $viewResource->create();
                $this->_storeProfile();
            }

        }

    }

}
