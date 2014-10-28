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
 * @version    $Id: Form.php 23209 2010-10-21 16:09:34Z ralph $
 */

/**
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tool_Project_Provider_Form extends Zend_Tool_Project_Provider_Abstract
{

    public static function createResource(Zend_Tool_Project_Profile $profile, $formName, $moduleName = null)
    {
        if (!is_string($formName)) {
            throw new Zend_Tool_Project_Provider_Exception('Zend_Tool_Project_Provider_Form::createResource() expects \"formName\" is the name of a form resource to create.');
        }

        if (!($formsDirectory = self::_getFormsDirectoryResource($profile, $moduleName))) {
            if ($moduleName) {
                $exceptionMessage = 'A form directory for module "' . $moduleName . '" was not found.';
            } else {
                $exceptionMessage = 'A form directory was not found.';
            }
            throw new Zend_Tool_Project_Provider_Exception($exceptionMessage);
        }

        $newForm = $formsDirectory->createResource(
            'formFile', 
            array('formName' => $formName, 'moduleName' => $moduleName)
            );

        return $newForm;
    }

    /**
     * hasResource()
     *
     * @param Zend_Tool_Project_Profile $profile
     * @param string $formName
     * @param string $moduleName
     * @return Zend_Tool_Project_Profile_Resource
     */
    public static function hasResource(Zend_Tool_Project_Profile $profile, $formName, $moduleName = null)
    {
        if (!is_string($formName)) {
            throw new Zend_Tool_Project_Provider_Exception('Zend_Tool_Project_Provider_Form::createResource() expects \"formName\" is the name of a form resource to check for existence.');
        }

        $formsDirectory = self::_getFormsDirectoryResource($profile, $moduleName);
        return (($formsDirectory->search(array('formFile' => array('formName' => $formName)))) instanceof Zend_Tool_Project_Profile_Resource);
    }
    
    /**
     * _getFormsDirectoryResource()
     *
     * @param Zend_Tool_Project_Profile $profile
     * @param string $moduleName
     * @return Zend_Tool_Project_Profile_Resource
     */
    protected static function _getFormsDirectoryResource(Zend_Tool_Project_Profile $profile, $moduleName = null)
    {
        $profileSearchParams = array();

        if ($moduleName != null && is_string($moduleName)) {
            $profileSearchParams = array('modulesDirectory', 'moduleDirectory' => array('moduleName' => $moduleName));
        }

        $profileSearchParams[] = 'formsDirectory';

        return $profile->search($profileSearchParams);
    }
    
    public function enable($module = null)
    {
        $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);

        // determine if testing is enabled in the project
        $testingEnabled = Zend_Tool_Project_Provider_Test::isTestingEnabled($this->_loadedProfile);

        $formDirectoryResource = self::_getFormsDirectoryResource($this->_loadedProfile, $module);
        
        if ($formDirectoryResource->isEnabled()) {
            throw new Zend_Tool_Project_Provider_Exception('This project already has forms enabled.');
        } else {
            if ($this->_registry->getRequest()->isPretend()) {
                $this->_registry->getResponse()->appendContent('Would enable forms directory at ' . $formDirectoryResource->getContext()->getPath());
            } else {
                $this->_registry->getResponse()->appendContent('Enabling forms directory at ' . $formDirectoryResource->getContext()->getPath());
                $formDirectoryResource->setEnabled(true);
                $formDirectoryResource->create();
                $this->_storeProfile();                
            }

        }
    }
    
    /**
     * Create a new form
     *
     * @param string $name
     * @param string $module
     */
    public function create($name, $module = null)
    {
        $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);

        // determine if testing is enabled in the project
        $testingEnabled = Zend_Tool_Project_Provider_Test::isTestingEnabled($this->_loadedProfile);

        if (self::hasResource($this->_loadedProfile, $name, $module)) {
            throw new Zend_Tool_Project_Provider_Exception('This project already has a form named ' . $name);
        }

        // Check that there is not a dash or underscore, return if doesnt match regex
        if (preg_match('#[_-]#', $name)) {
            throw new Zend_Tool_Project_Provider_Exception('Form names should be camel cased.');
        }
        
        $name = ucwords($name);
        
        try {
            $formResource = self::createResource($this->_loadedProfile, $name, $module);

            if ($testingEnabled) {
                $testFormResource = null;
                // $testFormResource = Zend_Tool_Project_Provider_Test::createApplicationResource($this->_loadedProfile, $name, 'index', $module);
            }

        } catch (Exception $e) {
            $response = $this->_registry->getResponse();
            $response->setException($e);
            return;
        }

        // do the creation
        if ($this->_registry->getRequest()->isPretend()) {

            $this->_registry->getResponse()->appendContent('Would create a form at '  . $formResource->getContext()->getPath());

            if ($testFormResource) {
                $this->_registry->getResponse()->appendContent('Would create a form test file at ' . $testFormResource->getContext()->getPath());
            }

        } else {

            $this->_registry->getResponse()->appendContent('Creating a form at ' . $formResource->getContext()->getPath());
            $formResource->create();

            if ($testFormResource) {
                $this->_registry->getResponse()->appendContent('Creating a form test file at ' . $testFormResource->getContext()->getPath());
                $testFormResource->create();
            }

            $this->_storeProfile();
        }

    }


}