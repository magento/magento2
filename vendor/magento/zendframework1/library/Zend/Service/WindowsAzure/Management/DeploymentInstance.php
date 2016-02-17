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
 * @package    Zend_Service_WindowsAzure
 * @subpackage Management
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Service_WindowsAzure_Management_ServiceEntityAbstract
 */
#require_once 'Zend/Service/WindowsAzure/Management/ServiceEntityAbstract.php';

/**
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @subpackage Management
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 *
 * @property string $Name            The name for the deployment. This name must be unique among other deployments for the specified hosted service.
 * @property string $DeploymentSlot  The environment to which the hosted service is deployed, either staging or production.
 * @property string $PrivateID       The unique identifier for this deployment.
 * @property string $Label           The label for the deployment.
 * @property string $Url             The URL for the deployment.
 * @property string $Configuration   The configuration file (XML, represented as string).
 * @property string $Status          The status of the deployment. Running, Suspended, RunningTransitioning, SuspendedTransitioning, Starting, Suspending, Deploying, Deleting.
 * @property string $UpgradeStatus   Parent node for elements describing an upgrade that is currently underway.
 * @property string $UpgradeType     The upgrade type designated for this deployment. Possible values are Auto and Manual.
 * @property string $CurrentUpgradeDomainState  The state of the current upgrade domain. Possible values are Before and During.
 * @property string $CurrentUpgradeDomain       An integer value that identifies the current upgrade domain. Upgrade domains are identified with a zero-based index: the first upgrade domain has an ID of 0, the second has an ID of 1, and so on.
 * @property string $UpgradeDomainCount         An integer value that indicates the number of upgrade domains in the deployment.
 * @property array  $RoleInstanceList           The list of role instances.
 * @property array  $RoleList                   The list of roles.
 */
class Zend_Service_WindowsAzure_Management_DeploymentInstance
	extends Zend_Service_WindowsAzure_Management_ServiceEntityAbstract
{
    /**
     * Constructor
     *
     * @param string $name            The name for the deployment. This name must be unique among other deployments for the specified hosted service.
     * @param string $deploymentSlot  The environment to which the hosted service is deployed, either staging or production.
     * @param string $privateID       The unique identifier for this deployment.
     * @param string $label           The label for the deployment.
     * @param string $url             The URL for the deployment.
     * @param string $configuration   The configuration file (XML, represented as string).
     * @param string $status          The status of the deployment. Running, Suspended, RunningTransitioning, SuspendedTransitioning, Starting, Suspending, Deploying, Deleting.
     * @param string $upgradeStatus   Parent node for elements describing an upgrade that is currently underway.
     * @param string $upgradeType     The upgrade type designated for this deployment. Possible values are Auto and Manual.
     * @param string $currentUpgradeDomainState  The state of the current upgrade domain. Possible values are Before and During.
     * @param string $currentUpgradeDomain       An integer value that identifies the current upgrade domain. Upgrade domains are identified with a zero-based index: the first upgrade domain has an ID of 0, the second has an ID of 1, and so on.
     * @param string $upgradeDomainCount         An integer value that indicates the number of upgrade domains in the deployment.
     * @param array  $roleInstanceList           The list of role instances.
     * @param array  $roleList                   The list of roles.
	 */
    public function __construct($name, $deploymentSlot, $privateID, $label, $url, $configuration, $status, $upgradeStatus, $upgradeType, $currentUpgradeDomainState, $currentUpgradeDomain, $upgradeDomainCount, $roleInstanceList = array(), $roleList = array())
    {
        $this->_data = array(
            'name'                        => $name,
            'deploymentslot'              => $deploymentSlot,
            'privateid'                   => $privateID,
            'label'                       => base64_decode($label),
            'url'                         => $url,
            'configuration'               => base64_decode($configuration),
            'status'                      => $status,
            'upgradestatus'               => $upgradeStatus,
            'upgradetype'                 => $upgradeType,
            'currentupgradedomainstate'   => $currentUpgradeDomainState,
            'currentupgradedomain'        => $currentUpgradeDomain,
            'upgradedomaincount'          => $upgradeDomainCount,
            'roleinstancelist'            => $roleInstanceList,
            'rolelist'                    => $roleList
        );
    }
}
