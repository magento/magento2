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
 * @see Zend_Tool_Project_Profile
 */
#require_once 'Zend/Tool/Project/Profile.php';

/**
 * @see Zend_Tool_Framework_Provider_Abstract
 */
#require_once 'Zend/Tool/Framework/Provider/Abstract.php';

/**
 * @see Zend_Tool_Project_Context_Repository
 */
#require_once 'Zend/Tool/Project/Context/Repository.php';

/**
 * @see Zend_Tool_Project_Profile_FileParser_Xml
 */
#require_once 'Zend/Tool/Project/Profile/FileParser/Xml.php';

/**
 * @see Zend_Tool_Framework_Registry
 */
#require_once 'Zend/Tool/Framework/Registry.php';

#require_once 'Zend/Tool/Framework/Provider/Initializable.php';

/**
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Tool_Project_Provider_Abstract
    extends Zend_Tool_Framework_Provider_Abstract
    implements Zend_Tool_Framework_Provider_Initializable
{

    const NO_PROFILE_THROW_EXCEPTION = true;
    const NO_PROFILE_RETURN_FALSE    = false;

    /**
     * @var bool
     */
    protected static $_isInitialized = false;

    protected $_projectPath = null;

    /**
     * @var Zend_Tool_Project_Profile
     */
    protected $_loadedProfile = null;

    public function initialize()
    {
        // initialize the ZF Contexts (only once per php request)
        if (!self::$_isInitialized) {

            // load all base contexts ONCE
            $contextRegistry = Zend_Tool_Project_Context_Repository::getInstance();
            $contextRegistry->addContextsFromDirectory(
                dirname(dirname(__FILE__)) . '/Context/Zf/', 'Zend_Tool_Project_Context_Zf_'
            );
            $contextRegistry->addContextsFromDirectory(
                dirname(dirname(__FILE__)) . '/Context/Filesystem/', 'Zend_Tool_Project_Context_Filesystem_'
            );

            // determine if there are project specfic providers ONCE
            $profilePath = $this->_findProfileDirectory();
            if ($this->_hasProjectProviderDirectory($profilePath . DIRECTORY_SEPARATOR . '.zfproject.xml')) {
                $profile = $this->_loadProfile();
                // project providers directory resource
                $ppd = $profile->search('ProjectProvidersDirectory');
                $ppd->loadProviders($this->_registry);
            }

            self::$_isInitialized = true;
        }

        // load up the extending providers required context classes
        if ($contextClasses = $this->getContextClasses()) {
            $this->_loadContextClassesIntoRegistry($contextClasses);
        }

    }

    public function getContextClasses()
    {
        return array();
    }

    /**
     * _getProject is designed to find if there is project file in the context of where
     * the client has been called from..   The search order is as follows..
     *    - traversing downwards from (PWD) - current working directory
     *    - if an enpoint variable has been registered in teh client registry - key=workingDirectory
     *    - if an ENV variable with the key ZFPROJECT_PATH is found
     *
     * @param bool   $loadProfileFlag         Whether or not to throw an exception when no profile is found
     * @param string $projectDirectory        The project directory to use to search
     * @param bool   $searchParentDirectories Whether or not to search upper level direcotries
     * @return Zend_Tool_Project_Profile
     */
    protected function _loadProfile($loadProfileFlag = self::NO_PROFILE_THROW_EXCEPTION, $projectDirectory = null, $searchParentDirectories = true)
    {
        $foundPath = $this->_findProfileDirectory($projectDirectory, $searchParentDirectories);

        if ($foundPath == false) {
            if ($loadProfileFlag == self::NO_PROFILE_THROW_EXCEPTION) {
                throw new Zend_Tool_Project_Provider_Exception('A project profile was not found.');
            } else {
                return false;
            }
        }

        $profile = new Zend_Tool_Project_Profile();
        $profile->setAttribute('projectDirectory', $foundPath);
        $profile->loadFromFile();
        $this->_loadedProfile = $profile;
        return $profile;
    }

    protected function _findProfileDirectory($projectDirectory = null, $searchParentDirectories = true)
    {
        // use the cwd if no directory was provided
        if ($projectDirectory == null) {
            $projectDirectory = getcwd();
        } elseif (realpath($projectDirectory) == false) {
            throw new Zend_Tool_Project_Provider_Exception('The $projectDirectory supplied does not exist.');
        }

        $profile = new Zend_Tool_Project_Profile();

        $parentDirectoriesArray = explode(DIRECTORY_SEPARATOR, ltrim($projectDirectory, DIRECTORY_SEPARATOR));
        while ($parentDirectoriesArray) {
            $projectDirectoryAssembled = implode(DIRECTORY_SEPARATOR, $parentDirectoriesArray);

            if (DIRECTORY_SEPARATOR !== "\\") {
                $projectDirectoryAssembled = DIRECTORY_SEPARATOR . $projectDirectoryAssembled;
            }

            $profile->setAttribute('projectDirectory', $projectDirectoryAssembled);
            if ($profile->isLoadableFromFile()) {
                unset($profile);
                return $projectDirectoryAssembled;
            }

            // break after first run if we are not to check upper directories
            if ($searchParentDirectories == false) {
                break;
            }

            array_pop($parentDirectoriesArray);
        }

        return false;
    }

    /**
     * Load the project profile from the current working directory, if not throw exception
     *
     * @return Zend_Tool_Project_Profile
     */
    protected function _loadProfileRequired()
    {
        $profile = $this->_loadProfile();
        if ($profile === false) {
            #require_once 'Zend/Tool/Project/Provider/Exception.php';
            throw new Zend_Tool_Project_Provider_Exception('A project profile was not found in the current working directory.');
        }
        return $profile;
    }

    /**
     * Return the currently loaded profile
     *
     * @return Zend_Tool_Project_Profile
     */
    protected function _getProfile($loadProfileFlag = self::NO_PROFILE_THROW_EXCEPTION)
    {
        if (!$this->_loadedProfile) {
            if (($this->_loadProfile($loadProfileFlag) === false) && ($loadProfileFlag === self::NO_PROFILE_RETURN_FALSE)) {
                return false;
            }
        }

        return $this->_loadedProfile;
    }

    /**
     * _storeProfile()
     *
     * This method will store the profile into its proper location
     *
     */
    protected function _storeProfile()
    {
        $projectProfileFile = $this->_loadedProfile->search('ProjectProfileFile');

        $name = $projectProfileFile->getContext()->getPath();

        $this->_registry->getResponse()->appendContent('Updating project profile \'' . $name . '\'');

        $projectProfileFile->getContext()->save();
    }

    protected function _getContentForContext(Zend_Tool_Project_Context_Interface $context, $methodName, $parameters)
    {
        $storage = $this->_registry->getStorage();
        if (!$storage->isEnabled()) {
            return false;
        }

        if (!class_exists('Zend_Tool_Project_Context_Content_Engine')) {
            #require_once 'Zend/Tool/Project/Context/Content/Engine.php';
        }

        $engine = new Zend_Tool_Project_Context_Content_Engine($storage);
        return $engine->getContent($context, $methodName, $parameters);
    }

    protected function _hasProjectProviderDirectory($pathToProfileFile)
    {
        // do some static analysis of the file so that we can determin whether or not to incure
        // the cost of loading the profile before the system is fully bootstrapped
        if (!file_exists($pathToProfileFile)) {
            return false;
        }

        $contents = file_get_contents($pathToProfileFile);
        if (strstr($contents, '<projectProvidersDirectory') === false) {
            return false;
        }

        if (strstr($contents, '<projectProvidersDirectory enabled="false"')) {
            return false;
        }

        return true;
    }

    /**
     * _loadContextClassesIntoRegistry() - This is called by the constructor
     * so that child providers can provide a list of contexts to load into the
     * context repository
     *
     * @param array $contextClasses
     */
    private function _loadContextClassesIntoRegistry($contextClasses)
    {
        $registry = Zend_Tool_Project_Context_Repository::getInstance();

        foreach ($contextClasses as $contextClass) {
            $registry->addContextClass($contextClass);
        }
    }
}
