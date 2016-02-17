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
 * @package    Zend_Service
 * @subpackage Rackspace
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

#require_once 'Zend/Service/Rackspace/Abstract.php';
#require_once 'Zend/Service/Rackspace/Servers/Server.php';
#require_once 'Zend/Service/Rackspace/Servers/ServerList.php';
#require_once 'Zend/Service/Rackspace/Servers/Image.php';
#require_once 'Zend/Service/Rackspace/Servers/ImageList.php';
#require_once 'Zend/Service/Rackspace/Servers/SharedIpGroup.php';
#require_once 'Zend/Service/Rackspace/Servers/SharedIpGroupList.php';
#require_once 'Zend/Validate/Ip.php';

class Zend_Service_Rackspace_Servers extends Zend_Service_Rackspace_Abstract
{
    const LIMIT_FILE_SIZE           = 10240;
    const LIMIT_NUM_FILE            = 5;
    const ERROR_SERVICE_UNAVAILABLE = 'The service is unavailable';
    const ERROR_UNAUTHORIZED        = 'Unauthorized';
    const ERROR_OVERLIMIT           = 'You reached the limit of requests, please wait some time before retry';
    const ERROR_PARAM_NO_ID         = 'You must specify the item\'s id';
    const ERROR_PARAM_NO_NAME       = 'You must specify the name';
    const ERROR_PARAM_NO_SERVERID   = 'You must specify the server Id';
    const ERROR_PARAM_NO_IMAGEID    = 'You must specify the server\'s image ID';
    const ERROR_PARAM_NO_FLAVORID   = 'You must specify the server\'s flavor ID';
    const ERROR_PARAM_NO_ARRAY      = 'You must specify an array of parameters';
    const ERROR_PARAM_NO_WEEKLY     = 'You must specify a weekly backup schedule';
    const ERROR_PARAM_NO_DAILY      = 'You must specify a daily backup schedule';
    const ERROR_ITEM_NOT_FOUND      = 'The item specified doesn\'t exist.';
    const ERROR_NO_FILE_EXISTS      = 'The file specified doesn\'t exist';
    const ERROR_LIMIT_FILE_SIZE     = 'You reached the size length of a file';
    const ERROR_IN_PROGRESS         = 'The item specified is still in progress';
    const ERROR_BUILD_IN_PROGRESS   = 'The build is still in progress';
    const ERROR_RESIZE_NOT_ALLOWED  = 'The resize is not allowed';
    /**
     * Get the list of the servers
     * If $details is true returns detail info
     *
     * @param  boolean $details
     * @return Zend_Service_Rackspace_Servers_ServerList|boolean
     */
    public function listServers($details=false)
    {
        $url= '/servers';
        if ($details) {
            $url.= '/detail';
        }
        $result= $this->httpCall($this->getManagementUrl().$url,'GET');
        $status= $result->getStatus();
        switch ($status) {
            case '200' :
            case '203' : // break intentionally omitted
                $servers= json_decode($result->getBody(),true);
                return new Zend_Service_Rackspace_Servers_ServerList($this,$servers['servers']);
            case '503' :
                $this->errorMsg= self::ERROR_SERVICE_UNAVAILABLE;
                break;
            case '401' :
                $this->errorMsg= self::ERROR_UNAUTHORIZED;
                break;
            case '413' :
                $this->errorMsg= self::ERROR_OVERLIMIT;
                break;
            default:
                $this->errorMsg= $result->getBody();
                break;
        }
        $this->errorCode= $status;
        return false;
    }
    /**
     * Get the specified server
     *
     * @param  string $id
     * @return Zend_Service_Rackspace_Servers_Server
     */
    public function getServer($id)
    {
        if (empty($id)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception(self::ERROR_PARAM_NO_ID);
        }
        $result= $this->httpCall($this->getManagementUrl().'/servers/'.rawurlencode($id),'GET');
        $status= $result->getStatus();
        switch ($status) {
            case '200' :
            case '203' : // break intentionally omitted
                $server = json_decode($result->getBody(),true);
                return new Zend_Service_Rackspace_Servers_Server($this,$server['server']);
            case '503' :
                $this->errorMsg= self::ERROR_SERVICE_UNAVAILABLE;
                break;
            case '401' :
                $this->errorMsg= self::ERROR_UNAUTHORIZED;
                break;
            case '404' :
                $this->errorMsg= self::ERROR_ITEM_NOT_FOUND;
                break;
            case '413' :
                $this->errorMsg= self::ERROR_OVERLIMIT;
                break;
            default:
                $this->errorMsg= $result->getBody();
                break;
        }
        $this->errorCode= $status;
        return false;
    }
    /**
     * Create a new server
     *
     * The required parameters are specified in $data (name, imageId, falvorId)
     * The $files is an associative array with 'serverPath' => 'localPath'
     *
     * @param  array $data
     * @param  array $metadata
     * @param  array $files
     * @return Zend_Service_Rackspace_Servers_Server|boolean
     */
    public function createServer(array $data, $metadata=array(),$files=array())
    {
        if (empty($data) || !is_array($data) || !is_array($metadata) || !is_array($files)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception(self::ERROR_PARAM_NO_ARRAY);
        }
        if (!isset($data['name'])) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception(self::ERROR_PARAM_NO_NAME);
        }
        if (!isset($data['flavorId'])) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception(self::ERROR_PARAM_NO_FLAVORID);
        }
        if (!isset($data['imageId'])) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception(self::ERROR_PARAM_NO_IMAGEID);
        }
        if (count($files)>self::LIMIT_NUM_FILE) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception('You can attach '.self::LIMIT_NUM_FILE.' files maximum');
        }
        if (!empty($metadata)) {
            $data['metadata']= $metadata;
        }
        $data['flavorId']= (integer) $data['flavorId'];
        $data['imageId']= (integer) $data['imageId'];
        if (!empty($files)) {
            foreach ($files as $serverPath => $filePath) {
                if (!file_exists($filePath)) {
                    #require_once 'Zend/Service/Rackspace/Exception.php';
                    throw new Zend_Service_Rackspace_Exception(
                            sprintf("The file %s doesn't exist",$filePath));
                }
                $content= file_get_contents($filePath);
                if (strlen($content) > self::LIMIT_FILE_SIZE) {
                    #require_once 'Zend/Service/Rackspace/Exception.php';
                    throw new Zend_Service_Rackspace_Exception(
                            sprintf("The size of the file %s is greater than the max size of %d bytes",
                                    $filePath,self::LIMIT_FILE_SIZE));
                }
                $data['personality'][] = array (
                    'path'     => $serverPath,
                    'contents' => base64_encode(file_get_contents($filePath))
                );
            }
        }
        $result = $this->httpCall($this->getManagementUrl().'/servers','POST',
                null,null,json_encode(array ('server' => $data)));
        $status = $result->getStatus();
        switch ($status) {
            case '200' :
            case '202' : // break intentionally omitted
                $server = json_decode($result->getBody(),true);
                return new Zend_Service_Rackspace_Servers_Server($this,$server['server']);
            case '503' :
                $this->errorMsg= self::ERROR_SERVICE_UNAVAILABLE;
                break;
            case '401' :
                $this->errorMsg= self::ERROR_UNAUTHORIZED;
                break;
            case '404' :
                $this->errorMsg= self::ERROR_ITEM_NOT_FOUND;
                break;
            case '413' :
                $this->errorMsg= self::ERROR_OVERLIMIT;
                break;
            default:
                $this->errorMsg= $result->getBody();
                break;
        }
        $this->errorCode= $status;
        return false;
    }
    /**
     * Change the name or the admin password for a server
     *
     * @param  string $id
     * @param  string $name
     * @param  string $password
     * @return boolean
     */
    protected function updateServer($id,$name=null,$password=null)
    {
        if (empty($id)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception('You must specify the ID of the server');
        }
        if (empty($name) && empty($password)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception("You must specify the new name or password of server");
        }
        $data= array();
        if (!empty($name)) {
            $data['name']= $name;
        }
        if (!empty($password)) {
            $data['adminPass']= $password;
        }
        $result = $this->httpCall($this->getManagementUrl().'/servers/'.rawurlencode($id),'PUT',
                null,null,json_encode(array('server' => $data)));
        $status = $result->getStatus();
        switch ($status) {
            case '204' : // break intentionally omitted
                return true;
            case '503' :
                $this->errorMsg= self::ERROR_SERVICE_UNAVAILABLE;
                break;
            case '401' :
                $this->errorMsg= self::ERROR_UNAUTHORIZED;
                break;
            case '404' :
                $this->errorMsg= self::ERROR_ITEM_NOT_FOUND;
                break;
            case '409' :
                $this->errorMsg= self::ERROR_IN_PROGRESS;
                break;
            case '413' :
                $this->errorMsg= self::ERROR_OVERLIMIT;
                break;
            default:
                $this->errorMsg= $result->getBody();
                break;
        }
        $this->errorCode= $status;
        return false;
    }
    /**
     * Change the server's name
     *
     * @param  string $id
     * @param  string $name
     * @return boolean
     */
    public function changeServerName($id,$name)
    {
        if (empty($id)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception('You must specify the ID of the server');
        }
        if (empty($name)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception("You must specify the new name of the server");
        }
        return $this->updateServer($id, $name);
    }
    /**
     * Change the admin password of the server
     *
     * @param  string $id
     * @param  string $password
     * @return boolean
     */
    public function changeServerPassword($id,$password)
    {
        if (empty($id)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception('You must specify the ID of the server');
        }
        if (empty($password)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception("You must specify the new password of the server");
        }
        return $this->updateServer($id, null,$password);
    }
    /**
     * Delete a server
     *
     * @param  string $id
     * @return boolean
     */
    public function deleteServer($id)
    {
        if (empty($id)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception('You must specify the ID of the server');
        }
        $result = $this->httpCall($this->getManagementUrl().'/servers/'.rawurlencode($id),'DELETE');
        $status = $result->getStatus();
        switch ($status) {
            case '202' : // break intentionally omitted
                return true;
            case '503' :
                $this->errorMsg= self::ERROR_SERVICE_UNAVAILABLE;
                break;
            case '401' :
                $this->errorMsg= self::ERROR_UNAUTHORIZED;
                break;
            case '404' :
                $this->errorMsg= self::ERROR_ITEM_NOT_FOUND;
                break;
            case '409' :
                $this->errorMsg= self::ERROR_IN_PROGRESS;
                break;
            case '413' :
                $this->errorMsg= self::ERROR_OVERLIMIT;
                break;
            default:
                $this->errorMsg= $result->getBody();
                break;
        }
        $this->errorCode= $status;
        return false;
    }
    /**
     * Get the server's IPs (public and private)
     *
     * @param  string $id
     * @return array|boolean
     */
    public function getServerIp($id)
    {
        $result= $this->getServer($id);
        if ($result===false) {
            return false;
        }
        $result= $result->toArray();
        return $result['addresses'];
    }
    /**
     * Get the Public IPs of a server
     *
     * @param  string $id
     * @return array|boolean
     */
    public function getServerPublicIp($id)
    {
        $addresses= $this->getServerIp($id);
        if ($addresses===false) {
            return false;
        }
        return $addresses['public'];
    }
    /**
     * Get the Private IPs of a server
     *
     * @param  string $id
     * @return array|boolean
     */
    public function getServerPrivateIp($id)
    {
        $addresses= $this->getServerIp($id);
        if ($addresses===false) {
            return false;
        }
        return $addresses['private'];
    }
    /**
     * Share an ip address for a server (id)
     *
     * @param  string $id server
     * @param  string $ip
     * @param  string $groupId
     * @return boolean
     */
    public function shareIpAddress($id,$ip,$groupId,$configure=true)
    {
        if (empty($id)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception('You didn\'t specified the ID of the server');
        }
        if (empty($ip)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception('You didn\'t specified the IP address to share');
        }
        $validator = new Zend_Validate_Ip();
        if (!$validator->isValid($ip)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception("The parameter $ip specified is not a valid IP address");
        }
        if (empty($groupId)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception('You didn\'t specified the group id to use');
        }
        $data= array (
            'sharedIpGroupId' => (integer) $groupId,
            'configureServer' => $configure
        );
        $result = $this->httpCall($this->getManagementUrl().'/servers/'.rawurlencode($id).'/ips/public/'.rawurlencode($ip),'PUT',
                null,null,json_encode(array('shareIp' => $data)));
        $status = $result->getStatus();
        switch ($status) {
            case '202' : // break intentionally omitted
                return true;
            case '503' :
                $this->errorMsg= self::ERROR_SERVICE_UNAVAILABLE;
                break;
            case '401' :
                $this->errorMsg= self::ERROR_UNAUTHORIZED;
                break;
            case '404' :
                $this->errorMsg= self::ERROR_ITEM_NOT_FOUND;
                break;
            case '413' :
                $this->errorMsg= self::ERROR_OVERLIMIT;
                break;
            default:
                $this->errorMsg= $result->getBody();
                break;
        }
        $this->errorCode= $status;
        return false;
    }
    /**
     * Unshare IP address for a server ($id)
     *
     * @param  string $id
     * @param  string $ip
     * @return boolean
     */
    public function unshareIpAddress($id,$ip)
    {
        if (empty($id)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception('You didn\'t specified the ID of the server');
        }
        if (empty($ip)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception('You didn\'t specified the IP address to share');
        }
        $validator = new Zend_Validate_Ip();
        if (!$validator->isValid($ip)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception("The parameter $ip specified is not a valid IP address");
        }
        $result = $this->httpCall($this->getManagementUrl().'/servers/'.rawurlencode($id).'/ips/public/'.rawurlencode($ip),
                'DELETE');
        $status = $result->getStatus();
        switch ($status) {
            case '202' : // break intentionally omitted
                return true;
            case '503' :
                $this->errorMsg= self::ERROR_SERVICE_UNAVAILABLE;
                break;
            case '401' :
                $this->errorMsg= self::ERROR_UNAUTHORIZED;
                break;
            case '404' :
                $this->errorMsg= self::ERROR_ITEM_NOT_FOUND;
                break;
            case '413' :
                $this->errorMsg= self::ERROR_OVERLIMIT;
                break;
            default:
                $this->errorMsg= $result->getBody();
                break;
        }
        $this->errorCode= $status;
        return false;
    }
    /**
     * Reboot a server
     *
     * $hard true is the equivalent of power cycling the server
     * $hard false is a graceful shutdown
     *
     * @param  string $id
     * @param  boolean $hard
     * @return boolean
     */
    public function rebootServer($id,$hard=false)
    {
        if (empty($id)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception('You didn\'t specified the ID of the server');
        }
        if (!$hard) {
            $type= 'SOFT';
        } else {
            $type= 'HARD';
        }
        $data= array (
            'reboot' => array (
                'type' => $type
            )
        );
        $result = $this->httpCall($this->getManagementUrl().'/servers/'.rawurlencode($id).'/action',
                                  'POST', null, null, json_encode($data));
        $status = $result->getStatus();
        switch ($status) {
            case '200' :
            case '202' : // break intentionally omitted
                return true;
            case '503' :
                $this->errorMsg= self::ERROR_SERVICE_UNAVAILABLE;
                break;
            case '401' :
                $this->errorMsg= self::ERROR_UNAUTHORIZED;
                break;
            case '404' :
                $this->errorMsg= self::ERROR_ITEM_NOT_FOUND;
                break;
            case '409' :
                $this->errorMsg= self::ERROR_BUILD_IN_PROGRESS;
                break;
            case '413' :
                $this->errorMsg= self::ERROR_OVERLIMIT;
                break;
            default:
                $this->errorMsg= $result->getBody();
                break;
        }
        $this->errorCode= $status;
        return false;
    }
    /**
     * Rebuild a server
     *
     * The rebuild function removes all data on the server and replaces it with the specified image,
     * serverId and IP addresses will remain the same.
     *
     * @param  string $id
     * @param  string $imageId
     * @return boolean
     */
    public function rebuildServer($id,$imageId)
    {
        if (empty($id)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception('You didn\'t specified the ID of the server');
        }
        if (empty($imageId)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception('You didn\'t specified the new imageId of the server');
        }
        $data= array (
            'rebuild' => array (
                'imageId' => (integer) $imageId
            )
        );
        $result = $this->httpCall($this->getManagementUrl().'/servers/'.rawurlencode($id).'/action',
                                  'POST', null, null, json_encode($data));
        $status = $result->getStatus();
        switch ($status) {
            case '202' : // break intentionally omitted
                return true;
            case '503' :
                $this->errorMsg= self::ERROR_SERVICE_UNAVAILABLE;
                break;
            case '401' :
                $this->errorMsg= self::ERROR_UNAUTHORIZED;
                break;
            case '404' :
                $this->errorMsg= self::ERROR_ITEM_NOT_FOUND;
                break;
            case '409' :
                $this->errorMsg= self::ERROR_BUILD_IN_PROGRESS;
                break;
            case '413' :
                $this->errorMsg= self::ERROR_OVERLIMIT;
                break;
            default:
                $this->errorMsg= $result->getBody();
                break;
        }
        $this->errorCode= $status;
        return false;
    }
    /**
     * Resize a server
     *
     * The resize function converts an existing server to a different flavor, in essence, scaling the
     * server up or down. The original server is saved for a period of time to allow rollback if there
     * is a problem. All resizes should be tested and explicitly confirmed, at which time the original
     * server is removed. All resizes are automatically confirmed after 24 hours if they are not
     * explicitly confirmed or reverted.
     *
     * @param  string $id
     * @param  string $flavorId
     * @return boolean
     */
    public function resizeServer($id,$flavorId)
    {
        if (empty($id)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception('You didn\'t specified the ID of the server');
        }
        if (empty($flavorId)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception('You didn\'t specified the new flavorId of the server');
        }
        $data= array (
            'resize' => array (
                'flavorId' => (integer) $flavorId
            )
        );
        $result = $this->httpCall($this->getManagementUrl().'/servers/'.rawurlencode($id).'/action',
                                  'POST', null, null, json_encode($data));
        $status = $result->getStatus();
        switch ($status) {
            case '202' : // break intentionally omitted
                return true;
            case '503' :
                $this->errorMsg= self::ERROR_SERVICE_UNAVAILABLE;
                break;
            case '401' :
                $this->errorMsg= self::ERROR_UNAUTHORIZED;
                break;
            case '403' :
                $this->errorMsg= self::ERROR_RESIZE_NOT_ALLOWED;
                break;
            case '404' :
                $this->errorMsg= self::ERROR_ITEM_NOT_FOUND;
                break;
            case '409' :
                $this->errorMsg= self::ERROR_BUILD_IN_PROGRESS;
                break;
            case '413' :
                $this->errorMsg= self::ERROR_OVERLIMIT;
                break;
            default:
                $this->errorMsg= $result->getBody();
                break;
        }
        $this->errorCode= $status;
        return false;
    }
    /**
     * Confirm resize of a server
     *
     * During a resize operation, the original server is saved for a period of time to allow roll
     * back if there is a problem. Once the newly resized server is tested and has been confirmed
     * to be functioning properly, use this operation to confirm the resize. After confirmation,
     * the original server is removed and cannot be rolled back to. All resizes are automatically
     * confirmed after 24 hours if they are not explicitly confirmed or reverted.
     *
     * @param  string $id
     * @return boolean
     */
    public function confirmResizeServer($id)
    {
        if (empty($id)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception('You didn\'t specified the ID of the server');
        }
        $data= array (
            'confirmResize' => null
        );
        $result = $this->httpCall($this->getManagementUrl().'/servers/'.rawurlencode($id).'/action',
                                  'POST', null, null, json_encode($data));
        $status = $result->getStatus();
        switch ($status) {
            case '204' : // break intentionally omitted
                return true;
            case '503' :
                $this->errorMsg= self::ERROR_SERVICE_UNAVAILABLE;
                break;
            case '401' :
                $this->errorMsg= self::ERROR_UNAUTHORIZED;
                break;
            case '403' :
                $this->errorMsg= self::ERROR_RESIZE_NOT_ALLOWED;
                break;
            case '404' :
                $this->errorMsg= self::ERROR_ITEM_NOT_FOUND;
                break;
            case '409' :
                $this->errorMsg= self::ERROR_BUILD_IN_PROGRESS;
                break;
            case '413' :
                $this->errorMsg= self::ERROR_OVERLIMIT;
                break;
            default:
                $this->errorMsg= $result->getBody();
                break;
        }
        $this->errorCode= $status;
        return false;
    }
    /**
     * Revert resize of a server
     *
     * During a resize operation, the original server is saved for a period of time to allow for roll
     * back if there is a problem. If you determine there is a problem with a newly resized server,
     * use this operation to revert the resize and roll back to the original server. All resizes are
     * automatically confirmed after 24 hours if they have not already been confirmed explicitly or
     * reverted.
     *
     * @param  string $id
     * @return boolean
     */
    public function revertResizeServer($id)
    {
        if (empty($id)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception('You didn\'t specified the ID of the server');
        }
        $data= array (
            'revertResize' => null
        );
        $result = $this->httpCall($this->getManagementUrl().'/servers/'.rawurlencode($id).'/action',
                                  'POST', null, null, json_encode($data));
        $status = $result->getStatus();
        switch ($status) {
            case '202' : // break intentionally omitted
                return true;
            case '503' :
                $this->errorMsg= self::ERROR_SERVICE_UNAVAILABLE;
                break;
            case '401' :
                $this->errorMsg= self::ERROR_UNAUTHORIZED;
                break;
            case '403' :
                $this->errorMsg= self::ERROR_RESIZE_NOT_ALLOWED;
                break;
            case '404' :
                $this->errorMsg= self::ERROR_ITEM_NOT_FOUND;
                break;
            case '409' :
                $this->errorMsg= self::ERROR_BUILD_IN_PROGRESS;
                break;
            case '413' :
                $this->errorMsg= self::ERROR_OVERLIMIT;
                break;
            default:
                $this->errorMsg= $result->getBody();
                break;
        }
        $this->errorCode= $status;
        return false;
    }
    /**
     * Get the list of the flavors
     *
     * If $details is true returns detail info
     *
     * @param  boolean $details
     * @return array|boolean
     */
    public function listFlavors($details=false)
    {
        $url= '/flavors';
        if ($details) {
            $url.= '/detail';
        }
        $result= $this->httpCall($this->getManagementUrl().$url,'GET');
        $status= $result->getStatus();
        switch ($status) {
            case '200' :
            case '203' : // break intentionally omitted
                $flavors= json_decode($result->getBody(),true);
                return $flavors['flavors'];
            case '503' :
                $this->errorMsg= self::ERROR_SERVICE_UNAVAILABLE;
                break;
            case '401' :
                $this->errorMsg= self::ERROR_UNAUTHORIZED;
                break;
            case '413' :
                $this->errorMsg= self::ERROR_OVERLIMIT;
                break;
            default:
                $this->errorMsg= $result->getBody();
                break;
        }
        $this->errorCode= $status;
        return false;
    }
    /**
     * Get the detail of a flavor
     *
     * @param  string $flavorId
     * @return array|boolean
     */
    public function getFlavor($flavorId)
    {
        if (empty($flavorId)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception('You didn\'t specified the new flavorId of the server');
        }
        $result= $this->httpCall($this->getManagementUrl().'/flavors/'.rawurlencode($flavorId),'GET');
        $status= $result->getStatus();
        switch ($status) {
            case '200' :
            case '203' : // break intentionally omitted
                $flavor= json_decode($result->getBody(),true);
                return $flavor['flavor'];
            case '503' :
                $this->errorMsg= self::ERROR_SERVICE_UNAVAILABLE;
                break;
            case '401' :
                $this->errorMsg= self::ERROR_UNAUTHORIZED;
                break;
            case '413' :
                $this->errorMsg= self::ERROR_OVERLIMIT;
                break;
            default:
                $this->errorMsg= $result->getBody();
                break;
        }
        $this->errorCode= $status;
        return false;
    }
    /**
     * Get the list of the images
     *
     * @param  boolean $details
     * @return Zend_Service_Rackspace_Servers_ImageList|boolean
     */
    public function listImages($details=false)
    {
        $url= '/images';
        if ($details) {
            $url.= '/detail';
        }
        $result= $this->httpCall($this->getManagementUrl().$url,'GET');
        $status= $result->getStatus();
        switch ($status) {
            case '200' :
            case '203' : // break intentionally omitted
                $images= json_decode($result->getBody(),true);
                return new Zend_Service_Rackspace_Servers_ImageList($this,$images['images']);
            case '503' :
                $this->errorMsg= self::ERROR_SERVICE_UNAVAILABLE;
                break;
            case '401' :
                $this->errorMsg= self::ERROR_UNAUTHORIZED;
                break;
            case '413' :
                $this->errorMsg= self::ERROR_OVERLIMIT;
                break;
            default:
                $this->errorMsg= $result->getBody();
                break;
        }
        $this->errorCode= $status;
        return false;
    }
    /**
     * Get detail about an image
     *
     * @param  string $id
     * @return Zend_Service_Rackspace_Servers_Image|boolean
     */
    public function getImage($id)
    {
        $result= $this->httpCall($this->getManagementUrl().'/images/'.rawurlencode($id),'GET');
        $status= $result->getStatus();
        switch ($status) {
            case '200' :
            case '203' : // break intentionally omitted
                $image= json_decode($result->getBody(),true);
                return new Zend_Service_Rackspace_Servers_Image($this,$image['image']);
            case '503' :
                $this->errorMsg= self::ERROR_SERVICE_UNAVAILABLE;
                break;
            case '401' :
                $this->errorMsg= self::ERROR_UNAUTHORIZED;
                break;
            case '404' :
                 $this->errorMsg= self::ERROR_ITEM_NOT_FOUND;
                break;
            case '413' :
                $this->errorMsg= self::ERROR_OVERLIMIT;
                break;
            default:
                $this->errorMsg= $result->getBody();
                break;
        }
        $this->errorCode= $status;
        return false;
    }
    /**
     * Create an image for a serverId
     *
     * @param  string $serverId
     * @param  string $name
     * @return Zend_Service_Rackspace_Servers_Image
     */
    public function createImage($serverId,$name)
    {
        if (empty($serverId)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception(self::ERROR_PARAM_NO_SERVERID);
        }
        if (empty($name)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception(self::ERROR_PARAM_NO_NAME);
        }
        $data = array(
            'image' => array (
                'serverId' => (integer) $serverId,
                'name'     => $name
            )
        );
        $result = $this->httpCall($this->getManagementUrl().'/images', 'POST',
                                  null, null, json_encode($data));
        $status = $result->getStatus();
        switch ($status) {
            case '202' : // break intentionally omitted
                $image= json_decode($result->getBody(),true);
                return new Zend_Service_Rackspace_Servers_Image($this,$image['image']);
            case '503' :
                $this->errorMsg= self::ERROR_SERVICE_UNAVAILABLE;
                break;
            case '401' :
                $this->errorMsg= self::ERROR_UNAUTHORIZED;
                break;
            case '403' :
                $this->errorMsg= self::ERROR_RESIZE_NOT_ALLOWED;
                break;
            case '404' :
                $this->errorMsg= self::ERROR_ITEM_NOT_FOUND;
                break;
            case '409' :
                $this->errorMsg= self::ERROR_BUILD_IN_PROGRESS;
                break;
            case '413' :
                $this->errorMsg= self::ERROR_OVERLIMIT;
                break;
            default:
                $this->errorMsg= $result->getBody();
                break;
        }
        $this->errorCode= $status;
        return false;
    }
    /**
     * Delete an image
     *
     * @param  string $id
     * @return boolean
     */
    public function deleteImage($id)
    {
        if (empty($id)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception(self::ERROR_PARAM_NO_ID);
        }
        $result = $this->httpCall($this->getManagementUrl().'/images/'.rawurlencode($id),'DELETE');
        $status = $result->getStatus();
        switch ($status) {
            case '204' : // break intentionally omitted
                return true;
            case '503' :
                $this->errorMsg= self::ERROR_SERVICE_UNAVAILABLE;
                break;
            case '401' :
                $this->errorMsg= self::ERROR_UNAUTHORIZED;
                break;
            case '404' :
                $this->errorMsg= self::ERROR_ITEM_NOT_FOUND;
                break;
            case '413' :
                $this->errorMsg= self::ERROR_OVERLIMIT;
                break;
            default:
                $this->errorMsg= $result->getBody();
                break;
        }
        $this->errorCode= $status;
        return false;
    }
    /**
     * Get the backup schedule of a server
     *
     * @param  string $id server's Id
     * @return array|boolean
     */
    public function getBackupSchedule($id)
    {
        if (empty($id)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception(self::ERROR_PARAM_NO_ID);
        }
        $result= $this->httpCall($this->getManagementUrl().'/servers/'.rawurlencode($id).'/backup_schedule',
                                 'GET');
        $status= $result->getStatus();
        switch ($status) {
            case '200' :
            case '203' : // break intentionally omitted
                $backup = json_decode($result->getBody(),true);
                return $backup['backupSchedule'];
            case '503' :
                $this->errorMsg= self::ERROR_SERVICE_UNAVAILABLE;
                break;
            case '401' :
                $this->errorMsg= self::ERROR_UNAUTHORIZED;
                break;
            case '404' :
                $this->errorMsg= self::ERROR_ITEM_NOT_FOUND;
                break;
            case '413' :
                $this->errorMsg= self::ERROR_OVERLIMIT;
                break;
            default:
                $this->errorMsg= $result->getBody();
                break;
        }
        $this->errorCode= $status;
        return false;
    }
    /**
     * Change the backup schedule of a server
     *
     * @param  string $id server's Id
     * @param  string $weekly
     * @param  string $daily
     * @return boolean
     */
    public function changeBackupSchedule($id,$weekly,$daily)
    {
        if (empty($id)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception(self::ERROR_PARAM_NO_ID);
        }
        if (empty($weekly)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception(self::ERROR_PARAM_NO_WEEKLY);
        }
        if (empty($daily)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception(self::ERROR_PARAM_NO_DAILY);
        }
        $data = array (
            'backupSchedule' => array (
                'enabled' => true,
                'weekly'  => $weekly,
                'daily'   => $daily
            )
        );
        $result= $this->httpCall($this->getManagementUrl().'/servers/'.rawurlencode($id).'/backup_schedule',
                                 'POST',null,null,json_encode($data));
        $status= $result->getStatus();
        switch ($status) {
            case '204' : // break intentionally omitted
                return true;
            case '503' :
                $this->errorMsg= self::ERROR_SERVICE_UNAVAILABLE;
                break;
            case '401' :
                $this->errorMsg= self::ERROR_UNAUTHORIZED;
                break;
            case '404' :
                $this->errorMsg= self::ERROR_ITEM_NOT_FOUND;
                break;
            case '409' :
                $this->errorMsg= self::ERROR_BUILD_IN_PROGRESS;
                break;
            case '413' :
                $this->errorMsg= self::ERROR_OVERLIMIT;
                break;
            default:
                $this->errorMsg= $result->getBody();
                break;
        }
        $this->errorCode= $status;
        return false;
    }
    /**
     * Disable the backup schedule for a server
     *
     * @param  string $id server's Id
     * @return boolean
     */
    public function disableBackupSchedule($id)
    {
        if (empty($id)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception(self::ERROR_PARAM_NO_ID);
        }
        $result = $this->httpCall($this->getManagementUrl().'/servers/'.rawurlencode($id).'/backup_schedule',
                                  'DELETE');
        $status = $result->getStatus();
        switch ($status) {
            case '204' : // break intentionally omitted
                return true;
            case '503' :
                $this->errorMsg= self::ERROR_SERVICE_UNAVAILABLE;
                break;
            case '401' :
                $this->errorMsg= self::ERROR_UNAUTHORIZED;
                break;
            case '404' :
                $this->errorMsg= self::ERROR_ITEM_NOT_FOUND;
                break;
            case '409' :
                $this->errorMsg= self::ERROR_BUILD_IN_PROGRESS;
                break;
            case '413' :
                $this->errorMsg= self::ERROR_OVERLIMIT;
                break;
            default:
                $this->errorMsg= $result->getBody();
                break;
        }
        $this->errorCode= $status;
        return false;
    }
    /**
     * Get the list of shared IP groups
     *
     * @param  boolean $details
     * @return Zend_Service_Rackspace_Servers_SharedIpGroupList|boolean
     */
    public function listSharedIpGroups($details=false)
    {
        $url= '/shared_ip_groups';
        if ($details) {
            $url.= '/detail';
        }
        $result= $this->httpCall($this->getManagementUrl().$url,'GET');
        $status= $result->getStatus();
        switch ($status) {
            case '200' :
            case '203' : // break intentionally omitted
                $groups= json_decode($result->getBody(),true);
                return new Zend_Service_Rackspace_Servers_SharedIpGroupList($this,$groups['sharedIpGroups']);
            case '503' :
                $this->errorMsg= self::ERROR_SERVICE_UNAVAILABLE;
                break;
            case '401' :
                $this->errorMsg= self::ERROR_UNAUTHORIZED;
                break;
            case '413' :
                $this->errorMsg= self::ERROR_OVERLIMIT;
                break;
            default:
                $this->errorMsg= $result->getBody();
                break;
        }
        $this->errorCode= $status;
        return false;
    }
    /**
     * Get the shared IP group
     *
     * @param  integer $id
     * @return Zend_Service_Rackspace_Servers_SharedIpGroup|boolean
     */
    public function getSharedIpGroup($id)
    {
        if (empty($id)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception(self::ERROR_PARAM_NO_ID);
        }
        $result= $this->httpCall($this->getManagementUrl().'/shared_ip_groups/'.rawurlencode($id),'GET');
        $status= $result->getStatus();
        switch ($status) {
            case '200' :
            case '203' : // break intentionally omitted
                $group= json_decode($result->getBody(),true);
                return new Zend_Service_Rackspace_Servers_SharedIpGroup($this,$group['sharedIpGroup']);
            case '503' :
                $this->errorMsg= self::ERROR_SERVICE_UNAVAILABLE;
                break;
            case '401' :
                $this->errorMsg= self::ERROR_UNAUTHORIZED;
                break;
            case '404' :
                $this->errorMsg= self::ERROR_ITEM_NOT_FOUND;
                break;
            case '413' :
                $this->errorMsg= self::ERROR_OVERLIMIT;
                break;
            default:
                $this->errorMsg= $result->getBody();
                break;
        }
        $this->errorCode= $status;
        return false;
    }
    /**
     * Create a shared Ip group
     *
     * @param  string $name
     * @param  string $serverId
     * @return array|boolean
     */
    public function createSharedIpGroup($name,$serverId)
    {
        if (empty($name)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception(self::ERROR_PARAM_NO_NAME);
        }
        if (empty($serverId)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception(self::ERROR_PARAM_NO_ID);
        }
        $data = array (
            'sharedIpGroup' => array (
                'name'   => $name,
                'server' => (integer) $serverId
            )
        );
        $result= $this->httpCall($this->getManagementUrl().'/shared_ip_groups',
                                 'POST',null,null,json_encode($data));
        $status= $result->getStatus();
        switch ($status) {
            case '201' : // break intentionally omitted
                $group = json_decode($result->getBody(),true);
                return new Zend_Service_Rackspace_Servers_SharedIpGroup($this,$group['sharedIpGroup']);
            case '503' :
                $this->errorMsg= self::ERROR_SERVICE_UNAVAILABLE;
                break;
            case '401' :
                $this->errorMsg= self::ERROR_UNAUTHORIZED;
                break;
            case '413' :
                $this->errorMsg= self::ERROR_OVERLIMIT;
                break;
            default:
                $this->errorMsg= $result->getBody();
                break;
        }
        $this->errorCode= $status;
        return false;
    }
    /**
     * Delete a Shared Ip Group
     *
     * @param  integer $id
     * @return boolean
     */
    public function deleteSharedIpGroup($id)
    {
        if (empty($id)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception(self::ERROR_PARAM_NO_ID);
        }
        $result= $this->httpCall($this->getManagementUrl().'/shared_ip_groups/'.rawurlencode($id),'DELETE');
        $status= $result->getStatus();
        switch ($status) {
            case '204' : // break intentionally omitted
                return true;
            case '503' :
                $this->errorMsg= self::ERROR_SERVICE_UNAVAILABLE;
                break;
            case '401' :
                $this->errorMsg= self::ERROR_UNAUTHORIZED;
                break;
            case '404' :
                $this->errorMsg= self::ERROR_ITEM_NOT_FOUND;
                break;
            case '413' :
                $this->errorMsg= self::ERROR_OVERLIMIT;
                break;
            default:
                $this->errorMsg= $result->getBody();
                break;
        }
        $this->errorCode= $status;
        return false;
    }
}
