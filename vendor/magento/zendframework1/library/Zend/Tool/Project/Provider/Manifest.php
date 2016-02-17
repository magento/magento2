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
 * @see Zend_Tool_Framework_Manifest_ProviderManifestable
 */
#require_once 'Zend/Tool/Framework/Manifest/ProviderManifestable.php';

/**
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tool_Project_Provider_Manifest implements
    Zend_Tool_Framework_Manifest_ProviderManifestable
{

    /**
     * getProviders()
     *
     * @return array Array of Providers
     */
    public function getProviders()
    {
        // the order here will represent what the output will look like when iterating a manifest

        return array(
            // top level project & profile providers
            'Zend_Tool_Project_Provider_Profile',
            'Zend_Tool_Project_Provider_Project',

            // app layer provider
            'Zend_Tool_Project_Provider_Application',

            // MVC layer providers
            'Zend_Tool_Project_Provider_Model',
            'Zend_Tool_Project_Provider_View',
            'Zend_Tool_Project_Provider_Controller',
            'Zend_Tool_Project_Provider_Action',

            // hMVC provider
            'Zend_Tool_Project_Provider_Module',

            // application problem providers
            'Zend_Tool_Project_Provider_Form',
            'Zend_Tool_Project_Provider_Layout',
            'Zend_Tool_Project_Provider_DbAdapter',
            'Zend_Tool_Project_Provider_DbTable',

            // provider within project provider
            'Zend_Tool_Project_Provider_ProjectProvider',

        );
    }
}
