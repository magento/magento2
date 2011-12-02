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
 * @version    $Id: Controller.php 20851 2010-02-02 21:45:51Z ralph $
 */

/**
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tool_Project_Provider_Controller
    extends Zend_Tool_Project_Provider_Abstract
    implements Zend_Tool_Framework_Provider_Pretendable
{

    /**
     * createResource will create the controllerFile resource at the appropriate location in the
     * profile.  NOTE: it is your job to execute the create() method on the resource, as well as
     * store the profile when done.
     *
     * @param Zend_Tool_Project_Profile $profile
     * @param string $controllerName
     * @param string $moduleName
     * @return Zend_Tool_Project_Profile_Resource
     */
    public static function createResource(Zend_Tool_Project_Profile $profile, $controllerName, $moduleName = null)
    {
        if (!is_string($controllerName)) {
            throw new Zend_Tool_Project_Provider_Exception('Zend_Tool_Project_Provider_Controller::createResource() expects \"controllerName\" is the name of a controller resource to create.');
        }

        if (!($controllersDirectory = self::_getControllersDirectoryResource($profile, $moduleName))) {
            if ($moduleName) {
                $exceptionMessage = 'A controller directory for module "' . $moduleName . '" was not found.';
            } else {
                $exceptionMessage = 'A controller directory was not found.';
            }
            throw new Zend_Tool_Project_Provider_Exception($exceptionMessage);
        }

        $newController = $controllersDirectory->createResource(
            'controllerFile', 
            array('controllerName' => $controllerName, 'moduleName' => $moduleName)
            );

        return $newController;
    }

    /**
     * hasResource()
     *
     * @param Zend_Tool_Project_Profile $profile
     * @param string $controllerName
     * @param string $moduleName
     * @return Zend_Tool_Project_Profile_Resource
     */
    public static function hasResource(Zend_Tool_Project_Profile $profile, $controllerName, $moduleName = null)
    {
        if (!is_string($controllerName)) {
            throw new Zend_Tool_Project_Provider_Exception('Zend_Tool_Project_Provider_Controller::createResource() expects \"controllerName\" is the name of a controller resource to create.');
        }

        $controllersDirectory = self::_getControllersDirectoryResource($profile, $moduleName);
        return (($controllersDirectory->search(array('controllerFile' => array('controllerName' => $controllerName)))) instanceof Zend_Tool_Project_Profile_Resource);
    }

    /**
     * _getControllersDirectoryResource()
     *
     * @param Zend_Tool_Project_Profile $profile
     * @param string $moduleName
     * @return Zend_Tool_Project_Profile_Resource
     */
    protected static function _getControllersDirectoryResource(Zend_Tool_Project_Profile $profile, $moduleName = null)
    {
        $profileSearchParams = array();

        if ($moduleName != null && is_string($moduleName)) {
            $profileSearchParams = array('modulesDirectory', 'moduleDirectory' => array('moduleName' => $moduleName));
        }

        $profileSearchParams[] = 'controllersDirectory';

        return $profile->search($profileSearchParams);
    }

    /**
     * Create a new controller
     *
     * @param string $name The name of the controller to create, in camelCase.
     * @param bool $indexActionIncluded Whether or not to create the index action.
     */
    public function create($name, $indexActionIncluded = true, $module = null)
    {
        $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);

        // determine if testing is enabled in the project
        #require_once 'Zend/Tool/Project/Provider/Test.php';
        $testingEnabled = Zend_Tool_Project_Provider_Test::isTestingEnabled($this->_loadedProfile);

        if (self::hasResource($this->_loadedProfile, $name, $module)) {
            throw new Zend_Tool_Project_Provider_Exception('This project already has a controller named ' . $name);
        }

        // Check that there is not a dash or underscore, return if doesnt match regex
        if (preg_match('#[_-]#', $name)) {
            throw new Zend_Tool_Project_Provider_Exception('Controller names should be camel cased.');
        }
        
        $originalName = $name;
        $name = ucfirst($name);
        
        // get request & response
        $request = $this->_registry->getRequest();
        $response = $this->_registry->getResponse();
        
        try {
            $controllerResource = self::createResource($this->_loadedProfile, $name, $module);
            if ($indexActionIncluded) {
                $indexActionResource = Zend_Tool_Project_Provider_Action::createResource($this->_loadedProfile, 'index', $name, $module);
                $indexActionViewResource = Zend_Tool_Project_Provider_View::createResource($this->_loadedProfile, 'index', $name, $module);
            }
            if ($testingEnabled) {
                $testControllerResource = Zend_Tool_Project_Provider_Test::createApplicationResource($this->_loadedProfile, $name, 'index', $module);
            }

        } catch (Exception $e) {
            $response->setException($e);
            return;
        }

        // determime if we need to note to the user about the name
        if (($name !== $originalName)) {
            $tense = (($request->isPretend()) ? 'would be' : 'is');
            $response->appendContent(
                'Note: The canonical controller name that ' . $tense
                    . ' used with other providers is "' . $name . '";'
                    . ' not "' . $originalName . '" as supplied',
                array('color' => array('yellow'))
                );
            unset($tense);
        }
        
        // do the creation
        if ($request->isPretend()) {
            
            $response->appendContent('Would create a controller at '  . $controllerResource->getContext()->getPath());

            if (isset($indexActionResource)) {
                $response->appendContent('Would create an index action method in controller ' . $name);
                $response->appendContent('Would create a view script for the index action method at ' . $indexActionViewResource->getContext()->getPath());
            }
            
            if ($testControllerResource) {
                $response->appendContent('Would create a controller test file at ' . $testControllerResource->getContext()->getPath());
            }

        } else {

            $response->appendContent('Creating a controller at ' . $controllerResource->getContext()->getPath());
            $controllerResource->create();

            if (isset($indexActionResource)) {
                $response->appendContent('Creating an index action method in controller ' . $name);
                $indexActionResource->create();
                $response->appendContent('Creating a view script for the index action method at ' . $indexActionViewResource->getContext()->getPath());
                $indexActionViewResource->create();
            }

            if ($testControllerResource) {
                $response->appendContent('Creating a controller test file at ' . $testControllerResource->getContext()->getPath());
                $testControllerResource->create();
            }

            $this->_storeProfile();
        }

    }



}
