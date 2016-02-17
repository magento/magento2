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

#require_once 'Zend/Tool/Framework/Manifest/ProviderManifestable.php';
#require_once 'Zend/Tool/Framework/Manifest/ActionManifestable.php';
#require_once 'Zend/Tool/Framework/System/Provider/Version.php';
#require_once 'Zend/Tool/Framework/System/Provider/Config.php';
#require_once 'Zend/Tool/Framework/System/Provider/Phpinfo.php';
#require_once 'Zend/Tool/Framework/System/Provider/Manifest.php';
#require_once 'Zend/Tool/Framework/System/Action/Create.php';
#require_once 'Zend/Tool/Framework/System/Action/Delete.php';

/**
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tool_Framework_System_Manifest
    implements Zend_Tool_Framework_Manifest_ProviderManifestable, Zend_Tool_Framework_Manifest_ActionManifestable
{

    public function getProviders()
    {
        $providers = array(
            new Zend_Tool_Framework_System_Provider_Version(),
            new Zend_Tool_Framework_System_Provider_Config(),
            new Zend_Tool_Framework_System_Provider_Phpinfo(),
            new Zend_Tool_Framework_System_Provider_Manifest()
            );

        return $providers;
    }

    public function getActions()
    {
        $actions = array(
            new Zend_Tool_Framework_System_Action_Create(),
            new Zend_Tool_Framework_System_Action_Delete()
            );

        return $actions;
    }
}
