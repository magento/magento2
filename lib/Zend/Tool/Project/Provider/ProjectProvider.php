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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: ProjectProvider.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/** @see Zend_Tool_Project_Provider_Abstract */
#require_once 'Zend/Tool/Project/Provider/Abstract.php';

/**
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tool_Project_Provider_ProjectProvider extends Zend_Tool_Project_Provider_Abstract
{

    /**
     * createResource()
     *
     * @param Zend_Tool_Project_Profile $profile
     * @param string $projectProviderName
     * @param string $actionNames
     * @return Zend_Tool_Project_Profile_Resource
     */
    public static function createResource(Zend_Tool_Project_Profile $profile, $projectProviderName, $actionNames = null)
    {

        if (!is_string($projectProviderName)) {
            /**
             * @see Zend_Tool_Project_Provider_Exception
             */
            #require_once 'Zend/Tool/Project/Provider/Exception.php';
            throw new Zend_Tool_Project_Provider_Exception('Zend_Tool_Project_Provider_Controller::createResource() expects \"projectProviderName\" is the name of a project provider resource to create.');
        }

        $profileSearchParams = array();
        $profileSearchParams[] = 'projectProvidersDirectory';

        $projectProvider = $profile->createResourceAt($profileSearchParams, 'projectProviderFile', array('projectProviderName' => $projectProviderName, 'actionNames' => $actionNames));

        return $projectProvider;
    }

    /**
     * getName()
     *
     * @return string
     */
    public function getName()
    {
        return 'ProjectProvider';
    }

    /**
     * Create stub for Zend_Tool Project Provider
     *
     * @var string       $name            class name for new Zend_Tool Project Provider
     * @var array|string $actions         list of provider methods
     * @throws Zend_Tool_Project_Provider_Exception
     */
    public function create($name, $actions = null)
    {
        $profile = $this->_loadProfileRequired();

        $projectProvider = self::createResource($profile, $name, $actions);

        if ($this->_registry->getRequest()->isPretend()) {
            $this->_registry->getResponse()->appendContent('Would create a project provider named ' . $name
                . ' in location ' . $projectProvider->getPath()
                );
        } else {
            $this->_registry->getResponse()->appendContent('Creating a project provider named ' . $name
                . ' in location ' . $projectProvider->getPath()
                );
            $projectProvider->create();
            $this->_storeProfile();
        }

    }
}
