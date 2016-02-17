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
 * @property string $Name              The affinity group name.
 * @property string $Label             A label for the affinity group.
 * @property string $Description       A description for the affinity group.
 * @property string $Location          The location of the affinity group.
 * @property array  $HostedServices    A list of hosted services in this affinity gtoup.
 * @property array  $StorageServices   A list of storage services in this affinity gtoup.
 */
class Zend_Service_WindowsAzure_Management_AffinityGroupInstance
	extends Zend_Service_WindowsAzure_Management_ServiceEntityAbstract
{
    /**
     * Constructor
     *
     * @property string $name              The affinity group name.
     * @property string $label             A label for the affinity group.
     * @property string $description       A description for the affinity group.
     * @property string $location          The location of the affinity group.
     * @property array  $hostedServices    A list of hosted services in this affinity gtoup.
     * @property array  $storageServices   A list of storage services in this affinity gtoup.
	 */
    public function __construct($name, $label, $description, $location, $hostedServices = array(), $storageServices = array())
    {
        $this->_data = array(
            'name'              => $name,
            'label'             => base64_decode($label),
            'description'       => $description,
            'location'          => $location,
            'hostedservices'    => $hostedServices,
            'storageservices'   => $storageServices
        );
    }
}
