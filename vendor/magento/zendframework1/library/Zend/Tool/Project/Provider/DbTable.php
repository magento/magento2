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
class Zend_Tool_Project_Provider_DbTable
    extends Zend_Tool_Project_Provider_Abstract
    implements Zend_Tool_Framework_Provider_Pretendable
{

    protected $_specialties = array('FromDatabase');

    /**
     * @var Zend_Filter
     */
    protected $_nameFilter = null;

    public static function createResource(Zend_Tool_Project_Profile $profile, $dbTableName, $actualTableName, $moduleName = null)
    {
        $profileSearchParams = array();

        if ($moduleName != null && is_string($moduleName)) {
            $profileSearchParams = array('modulesDirectory', 'moduleDirectory' => array('moduleName' => $moduleName));
        }

        $profileSearchParams[] = 'modelsDirectory';

        $modelsDirectory = $profile->search($profileSearchParams);

        if (!($modelsDirectory instanceof Zend_Tool_Project_Profile_Resource)) {
            throw new Zend_Tool_Project_Provider_Exception(
                'A models directory was not found' .
                (($moduleName) ? ' for module ' . $moduleName . '.' : '.')
                );
        }

        if (!($dbTableDirectory = $modelsDirectory->search('DbTableDirectory'))) {
            $dbTableDirectory = $modelsDirectory->createResource('DbTableDirectory');
        }

        $dbTableFile = $dbTableDirectory->createResource('DbTableFile', array('dbTableName' => $dbTableName, 'actualTableName' => $actualTableName));

        return $dbTableFile;
    }

    public static function hasResource(Zend_Tool_Project_Profile $profile, $dbTableName, $moduleName = null)
    {
        $profileSearchParams = array();

        if ($moduleName != null && is_string($moduleName)) {
            $profileSearchParams = array('modulesDirectory', 'moduleDirectory' => array('moduleName' => $moduleName));
        }

        $profileSearchParams[] = 'modelsDirectory';

        $modelsDirectory = $profile->search($profileSearchParams);

        if (!($modelsDirectory instanceof Zend_Tool_Project_Profile_Resource)
            || !($dbTableDirectory = $modelsDirectory->search('DbTableDirectory'))) {
            return false;
        }

        $dbTableFile = $dbTableDirectory->search(array('DbTableFile' => array('dbTableName' => $dbTableName)));

        return ($dbTableFile instanceof Zend_Tool_Project_Profile_Resource) ? true : false;
    }


    public function create($name, $actualTableName, $module = null, $forceOverwrite = false)
    {
        $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);

        // Check that there is not a dash or underscore, return if doesnt match regex
        if (preg_match('#[_-]#', $name)) {
            throw new Zend_Tool_Project_Provider_Exception('DbTable names should be camel cased.');
        }

        $originalName = $name;
        $name = ucfirst($name);

        if ($actualTableName == '') {
            throw new Zend_Tool_Project_Provider_Exception('You must provide both the DbTable name as well as the actual db table\'s name.');
        }

        if (self::hasResource($this->_loadedProfile, $name, $module)) {
            throw new Zend_Tool_Project_Provider_Exception('This project already has a DbTable named ' . $name);
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
            $tableResource = self::createResource($this->_loadedProfile, $name, $actualTableName, $module);
        } catch (Exception $e) {
            $response = $this->_registry->getResponse();
            $response->setException($e);
            return;
        }

        // do the creation
        if ($request->isPretend()) {
            $response->appendContent('Would create a DbTable at '  . $tableResource->getContext()->getPath());
        } else {
            $response->appendContent('Creating a DbTable at ' . $tableResource->getContext()->getPath());
            $tableResource->create();
            $this->_storeProfile();
        }
    }

    /**
     * @param string $module        Module name action should be applied to.
     * @param bool $forceOverwrite  Whether should force overwriting previous classes generated
     * @return void 
     */
    public function createFromDatabase($module = null, $forceOverwrite = false)
    {
        $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);

        $bootstrapResource = $this->_loadedProfile->search('BootstrapFile');

        /* @var $zendApp Zend_Application */
        $zendApp = $bootstrapResource->getApplicationInstance();

        try {
            $zendApp->bootstrap('db');
        } catch (Zend_Application_Exception $e) {
            throw new Zend_Tool_Project_Provider_Exception('Db resource not available, you might need to configure a DbAdapter.');
            return;
        }

        /* @var $db Zend_Db_Adapter_Abstract */
        $db = $zendApp->getBootstrap()->getResource('db');

        $tableResources = array();
        foreach ($db->listTables() as $actualTableName) {

            $dbTableName = $this->_convertTableNameToClassName($actualTableName);

            if (!$forceOverwrite && self::hasResource($this->_loadedProfile, $dbTableName, $module)) {
                throw new Zend_Tool_Project_Provider_Exception(
                    'This DbTable resource already exists, if you wish to overwrite it, '
                    . 'pass the "forceOverwrite" flag to this provider.'
                    );
            }

            $tableResources[] = self::createResource(
                $this->_loadedProfile,
                $dbTableName,
                $actualTableName,
                $module
                );
        }

        if (count($tableResources) == 0) {
            $this->_registry->getResponse()->appendContent('There are no tables in the selected database to write.');
        }

        // do the creation
        if ($this->_registry->getRequest()->isPretend()) {

            foreach ($tableResources as $tableResource) {
                $this->_registry->getResponse()->appendContent('Would create a DbTable at '  . $tableResource->getContext()->getPath());
            }

        } else {

            foreach ($tableResources as $tableResource) {
                $this->_registry->getResponse()->appendContent('Creating a DbTable at ' . $tableResource->getContext()->getPath());
                $tableResource->create();
            }

            $this->_storeProfile();
        }


    }

    protected function _convertTableNameToClassName($tableName)
    {
        if ($this->_nameFilter == null) {
            $this->_nameFilter = new Zend_Filter();
            $this->_nameFilter
                ->addFilter(new Zend_Filter_Word_UnderscoreToCamelCase());
        }

        return $this->_nameFilter->filter($tableName);
    }

}
