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
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tool_Project_Provider_Model extends Zend_Tool_Project_Provider_Abstract
{

    public static function createResource(Zend_Tool_Project_Profile $profile, $modelName, $moduleName = null)
    {
        if (!is_string($modelName)) {
            throw new Zend_Tool_Project_Provider_Exception('Zend_Tool_Project_Provider_Model::createResource() expects \"modelName\" is the name of a model resource to create.');
        }

        if (!($modelsDirectory = self::_getModelsDirectoryResource($profile, $moduleName))) {
            if ($moduleName) {
                $exceptionMessage = 'A model directory for module "' . $moduleName . '" was not found.';
            } else {
                $exceptionMessage = 'A model directory was not found.';
            }
            throw new Zend_Tool_Project_Provider_Exception($exceptionMessage);
        }

        $newModel = $modelsDirectory->createResource(
            'modelFile',
            array('modelName' => $modelName, 'moduleName' => $moduleName)
            );

        return $newModel;
    }

    /**
     * hasResource()
     *
     * @param Zend_Tool_Project_Profile $profile
     * @param string $modelName
     * @param string $moduleName
     * @return Zend_Tool_Project_Profile_Resource
     */
    public static function hasResource(Zend_Tool_Project_Profile $profile, $modelName, $moduleName = null)
    {
        if (!is_string($modelName)) {
            throw new Zend_Tool_Project_Provider_Exception('Zend_Tool_Project_Provider_Model::createResource() expects \"modelName\" is the name of a model resource to check for existence.');
        }

        $modelsDirectory = self::_getModelsDirectoryResource($profile, $moduleName);
        
        if (!$modelsDirectory instanceof Zend_Tool_Project_Profile_Resource) {
            return false;
        }
        
        return (($modelsDirectory->search(array('modelFile' => array('modelName' => $modelName)))) instanceof Zend_Tool_Project_Profile_Resource);
    }

    /**
     * _getModelsDirectoryResource()
     *
     * @param Zend_Tool_Project_Profile $profile
     * @param string $moduleName
     * @return Zend_Tool_Project_Profile_Resource
     */
    protected static function _getModelsDirectoryResource(Zend_Tool_Project_Profile $profile, $moduleName = null)
    {
        $profileSearchParams = array();

        if ($moduleName != null && is_string($moduleName)) {
            $profileSearchParams = array('modulesDirectory', 'moduleDirectory' => array('moduleName' => $moduleName));
        }

        $profileSearchParams[] = 'modelsDirectory';

        return $profile->search($profileSearchParams);
    }

    /**
     * Create a new model
     *
     * @param string $name
     * @param string $module
     */
    public function create($name, $module = null)
    {
        $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);

        $originalName = $name;

        $name = ucwords($name);

        // determine if testing is enabled in the project
        $testingEnabled = false; //Zend_Tool_Project_Provider_Test::isTestingEnabled($this->_loadedProfile);
        $testModelResource = null;

        // Check that there is not a dash or underscore, return if doesnt match regex
        if (preg_match('#[_-]#', $name)) {
            throw new Zend_Tool_Project_Provider_Exception('Model names should be camel cased.');
        }

        if (self::hasResource($this->_loadedProfile, $name, $module)) {
            throw new Zend_Tool_Project_Provider_Exception('This project already has a model named ' . $name);
        }

        // get request/response object
        $request = $this->_registry->getRequest();
        $response = $this->_registry->getResponse();

        // alert the user about inline converted names
        $tense = (($request->isPretend()) ? 'would be' : 'is');

        if ($name !== $originalName) {
            $response->appendContent(
                'Note: The canonical model name that ' . $tense
                    . ' used with other providers is "' . $name . '";'
                    . ' not "' . $originalName . '" as supplied',
                array('color' => array('yellow'))
                );
        }

        try {
            $modelResource = self::createResource($this->_loadedProfile, $name, $module);

            if ($testingEnabled) {
                // $testModelResource = Zend_Tool_Project_Provider_Test::createApplicationResource($this->_loadedProfile, $name, 'index', $module);
            }

        } catch (Exception $e) {
            $response->setException($e);
            return;
        }

        // do the creation
        if ($request->isPretend()) {

            $response->appendContent('Would create a model at '  . $modelResource->getContext()->getPath());

            if ($testModelResource) {
                $response->appendContent('Would create a model test file at ' . $testModelResource->getContext()->getPath());
            }

        } else {

            $response->appendContent('Creating a model at ' . $modelResource->getContext()->getPath());
            $modelResource->create();

            if ($testModelResource) {
                $response->appendContent('Creating a model test file at ' . $testModelResource->getContext()->getPath());
                $testModelResource->create();
            }

            $this->_storeProfile();
        }

    }


}
