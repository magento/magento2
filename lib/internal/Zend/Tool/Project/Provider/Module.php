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
 * @version    $Id: Module.php 23419 2010-11-20 21:37:46Z ramon $
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
 * @see Zend_Tool_Project_Profile_Iterator_ContextFilter
 */
#require_once 'Zend/Tool/Project/Profile/Iterator/ContextFilter.php';

/**
 * @see Zend_Tool_Project_Profile_Iterator_EnabledResourceFilter
 */
#require_once 'Zend/Tool/Project/Profile/Iterator/EnabledResourceFilter.php';

/**
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tool_Project_Provider_Module
    extends Zend_Tool_Project_Provider_Abstract
    implements Zend_Tool_Framework_Provider_Pretendable
{

    public static function createResources(Zend_Tool_Project_Profile $profile, $moduleName, Zend_Tool_Project_Profile_Resource $targetModuleResource = null)
    {

        // find the appliction directory, it will serve as our module skeleton
        if ($targetModuleResource == null) {
            $targetModuleResource = $profile->search('applicationDirectory');
            $targetModuleEnabledResources = array(
                'ControllersDirectory', 'ModelsDirectory', 'ViewsDirectory',
                'ViewScriptsDirectory', 'ViewHelpersDirectory', 'ViewFiltersDirectory'
                );
        }

        // find the actual modules directory we will use to house our module
        $modulesDirectory = $profile->search('modulesDirectory');

        // if there is a module directory already, except
        if ($modulesDirectory->search(array('moduleDirectory' => array('moduleName' => $moduleName)))) {
            throw new Zend_Tool_Project_Provider_Exception('A module named "' . $moduleName . '" already exists.');
        }

        // create the module directory
        $moduleDirectory = $modulesDirectory->createResource('moduleDirectory', array('moduleName' => $moduleName));

        // create a context filter so that we can pull out only what we need from the module skeleton
        $moduleContextFilterIterator = new Zend_Tool_Project_Profile_Iterator_ContextFilter(
            $targetModuleResource,
            array(
                'denyNames' => array('ModulesDirectory', 'ViewControllerScriptsDirectory'),
                'denyType'  => 'Zend_Tool_Project_Context_Filesystem_File'
                )
            );

        // the iterator for the module skeleton
        $targetIterator = new RecursiveIteratorIterator($moduleContextFilterIterator, RecursiveIteratorIterator::SELF_FIRST);

        // initialize some loop state information
        $currentDepth = 0;
        $parentResources = array();
        $currentResource = $moduleDirectory;

        // loop through the target module skeleton
        foreach ($targetIterator as $targetSubResource) {

            $depthDifference = $targetIterator->getDepth() - $currentDepth;
            $currentDepth = $targetIterator->getDepth();

            if ($depthDifference === 1) {
                // if we went down into a child, make note
                array_push($parentResources, $currentResource);
                // this will have always been set previously by another loop
                $currentResource = $currentChildResource;
            } elseif ($depthDifference < 0) {
                // if we went up to a parent, make note
                $i = $depthDifference;
                do {
                    // if we went out more than 1 parent, get to the correct parent
                    $currentResource = array_pop($parentResources);
                } while ($i-- > 0);
            }

            // get parameters for the newly created module resource
            $params = $targetSubResource->getAttributes();
            $currentChildResource = $currentResource->createResource($targetSubResource->getName(), $params);

            // based of the provided list (Currently up top), enable specific resources
            if (isset($targetModuleEnabledResources)) {
                $currentChildResource->setEnabled(in_array($targetSubResource->getName(), $targetModuleEnabledResources));
            } else {
                $currentChildResource->setEnabled($targetSubResource->isEnabled());
            }

        }

        return $moduleDirectory;
    }

    /**
     * create()
     *
     * @param string $name
     */
    public function create($name) //, $moduleProfile = null)
    {
        $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);

        $resources = self::createResources($this->_loadedProfile, $name);

        $response = $this->_registry->getResponse();

        if ($this->_registry->getRequest()->isPretend()) {
            $response->appendContent('I would create the following module and artifacts:');
            foreach (new RecursiveIteratorIterator($resources, RecursiveIteratorIterator::SELF_FIRST) as $resource) {
                if (is_callable(array($resource->getContext(), 'getPath'))) {
                    $response->appendContent($resource->getContext()->getPath());
                }
            }
        } else {
            $response->appendContent('Creating the following module and artifacts:');
            $enabledFilter = new Zend_Tool_Project_Profile_Iterator_EnabledResourceFilter($resources);
            foreach (new RecursiveIteratorIterator($enabledFilter, RecursiveIteratorIterator::SELF_FIRST) as $resource) {
                $response->appendContent($resource->getContext()->getPath());
                $resource->create();
            }

            $response->appendContent('Added a key for path module directory to the application.ini file');
            $appConfigFile = $this->_loadedProfile->search('ApplicationConfigFile');
            $appConfigFile->removeStringItem('resources.frontController.moduleDirectory', 'production');
            $appConfigFile->addStringItem('resources.frontController.moduleDirectory', 'APPLICATION_PATH "/modules"', 'production', false);

            if (strtolower($name) == 'default') {
                $response->appendContent('Added a key for the default module to the application.ini file');
                $appConfigFile->addStringItem('resources.frontController.params.prefixDefaultModule', '1', 'production');
            }

            $appConfigFile->create();

            // store changes to the profile
            $this->_storeProfile();
        }

    }

}

