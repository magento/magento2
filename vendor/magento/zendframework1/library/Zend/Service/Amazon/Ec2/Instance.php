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
 * @package    Zend_Service_Amazon
 * @subpackage Ec2
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Service_Amazon_Ec2_Abstract
 */
#require_once 'Zend/Service/Amazon/Ec2/Abstract.php';

/**
 * An Amazon EC2 interface that allows yout to run, terminate, reboot and describe Amazon
 * Ec2 Instances.
 *
 * @category   Zend
 * @package    Zend_Service_Amazon
 * @subpackage Ec2
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Amazon_Ec2_Instance extends Zend_Service_Amazon_Ec2_Abstract
{
    /**
     * Constant for Micro Instance Type
     */
    const MICRO = 't1.micro';
    /**
     * Constant for Small Instance TYpe
     */
    const SMALL = 'm1.small';

    /**
     * Constant for Large Instance TYpe
     */
    const LARGE = 'm1.large';

    /**
     * Constant for X-Large Instance TYpe
     */
    const XLARGE = 'm1.xlarge';

    /**
     * Constant for High CPU Medium Instance TYpe
     */
    const HCPU_MEDIUM = 'c1.medium';

    /**
     * Constant for High CPU X-Large Instance TYpe
     */
    const HCPU_XLARGE = 'c1.xlarge';


    /**
     * Launches a specified number of Instances.
     *
     * If Amazon EC2 cannot launch the minimum number AMIs you request, no
     * instances launch. If there is insufficient capacity to launch the
     * maximum number of AMIs you request, Amazon EC2 launches as many
     * as possible to satisfy the requested maximum values.
     *
     * Every instance is launched in a security group. If you do not specify
     * a security group at launch, the instances start in your default security group.
     * For more information on creating security groups, see CreateSecurityGroup.
     *
     * An optional instance type can be specified. For information
     * about instance types, see Instance Types.
     *
     * You can provide an optional key pair ID for each image in the launch request
     * (for more information, see CreateKeyPair). All instances that are created
     * from images that use this key pair will have access to the associated public
     * key at boot. You can use this key to provide secure access to an instance of an
     * image on a per-instance basis. Amazon EC2 public images use this feature to
     * provide secure access without passwords.
     *
     * Launching public images without a key pair ID will leave them inaccessible.
     *
     * @param array $options                        An array that contins the options to start an instance.
     *                                              Required Values:
     *                                                imageId string        ID of the AMI with which to launch instances.
     *                                              Optional Values:
     *                                                minCount integer      Minimum number of instances to launch.
     *                                                maxCount integer      Maximum number of instances to launch.
     *                                                keyName string        Name of the key pair with which to launch instances.
     *                                                securityGruop string|array Names of the security groups with which to associate the instances.
     *                                                userData string       The user data available to the launched instances. This should not be Base64 encoded.
     *                                                instanceType constant Specifies the instance type.
     *                                                placement string      Specifies the availability zone in which to launch the instance(s). By default, Amazon EC2 selects an availability zone for you.
     *                                                kernelId string       The ID of the kernel with which to launch the instance.
     *                                                ramdiskId string      The ID of the RAM disk with which to launch the instance.
     *                                                blockDeviceVirtualName string     Specifies the virtual name to map to the corresponding device name. For example: instancestore0
     *                                                blockDeviceName string            Specifies the device to which you are mapping a virtual name. For example: sdb
     *                                                monitor boolean               Turn on CloudWatch Monitoring for an instance.
     * @return array
     */
    public function run(array $options)
    {
        $_defaultOptions = array(
            'minCount'  => 1,
            'maxCount'  => 1,
            'instanceType' => Zend_Service_Amazon_Ec2_Instance::SMALL
        );

        // set / override the defualt optoins if they are not passed into the array;
        $options = array_merge($_defaultOptions, $options);

        if(!isset($options['imageId'])) {
            #require_once 'Zend/Service/Amazon/Ec2/Exception.php';
            throw new Zend_Service_Amazon_Ec2_Exception('No Image Id Provided');
        }


        $params = array();
        $params['Action'] = 'RunInstances';
        $params['ImageId'] = $options['imageId'];
        $params['MinCount'] = $options['minCount'];
        $params['MaxCount'] = $options['maxCount'];

        if(isset($options['keyName'])) {
            $params['KeyName'] = $options['keyName'];
        }

        if(is_array($options['securityGroup']) && !empty($options['securityGroup'])) {
            foreach($options['securityGroup'] as $k=>$name) {
                $params['SecurityGroup.' . ($k+1)] = $name;
            }
        } elseif(isset($options['securityGroup'])) {
            $params['SecurityGroup.1'] = $options['securityGroup'];
        }

        if(isset($options['userData'])) {
            $params['UserData'] = base64_encode($options['userData']);
        }

        if(isset($options['instanceType'])) {
            $params['InstanceType'] = $options['instanceType'];
        }

        if(isset($options['placement'])) {
            $params['Placement.AvailabilityZone'] = $options['placement'];
        }

        if(isset($options['kernelId'])) {
            $params['KernelId'] = $options['kernelId'];
        }

        if(isset($options['ramdiskId'])) {
            $params['RamdiskId'] = $options['ramdiskId'];
        }

        if(isset($options['blockDeviceVirtualName']) && isset($options['blockDeviceName'])) {
            $params['BlockDeviceMapping.n.VirtualName'] = $options['blockDeviceVirtualName'];
            $params['BlockDeviceMapping.n.DeviceName'] = $options['blockDeviceName'];
        }

        if(isset($options['monitor']) && $options['monitor'] === true) {
            $params['Monitoring.Enabled'] = true;
        }
        $response = $this->sendRequest($params);
        $xpath = $response->getXPath();

        $return = array();

        $return['reservationId'] = $xpath->evaluate('string(//ec2:reservationId/text())');
        $return['ownerId'] = $xpath->evaluate('string(//ec2:ownerId/text())');

        $gs = $xpath->query('//ec2:groupSet/ec2:item');
        foreach($gs as $gs_node) {
            $return['groupSet'][] = $xpath->evaluate('string(ec2:groupId/text())', $gs_node);
            unset($gs_node);
        }
        unset($gs);

        $is = $xpath->query('//ec2:instancesSet/ec2:item');
        foreach($is as $is_node) {
            $item = array();

            $item['instanceId'] = $xpath->evaluate('string(ec2:instanceId/text())', $is_node);
            $item['imageId'] = $xpath->evaluate('string(ec2:imageId/text())', $is_node);
            $item['instanceState']['code'] = $xpath->evaluate('string(ec2:instanceState/ec2:code/text())', $is_node);
            $item['instanceState']['name'] = $xpath->evaluate('string(ec2:instanceState/ec2:name/text())', $is_node);
            $item['privateDnsName'] = $xpath->evaluate('string(ec2:privateDnsName/text())', $is_node);
            $item['dnsName'] = $xpath->evaluate('string(ec2:dnsName/text())', $is_node);
            $item['keyName'] = $xpath->evaluate('string(ec2:keyName/text())', $is_node);
            $item['instanceType'] = $xpath->evaluate('string(ec2:instanceType/text())', $is_node);
            $item['amiLaunchIndex'] = $xpath->evaluate('string(ec2:amiLaunchIndex/text())', $is_node);
            $item['launchTime'] = $xpath->evaluate('string(ec2:launchTime/text())', $is_node);
            $item['availabilityZone'] = $xpath->evaluate('string(ec2:placement/ec2:availabilityZone/text())', $is_node);

            $return['instances'][] = $item;
            unset($item);
            unset($is_node);
        }
        unset($is);

        return $return;

    }

    /**
     * Returns information about instances that you own.
     *
     * If you specify one or more instance IDs, Amazon EC2 returns information
     * for those instances. If you do not specify instance IDs, Amazon EC2
     * returns information for all relevant instances. If you specify an invalid
     * instance ID, a fault is returned. If you specify an instance that you do
     * not own, it will not be included in the returned results.
     *
     * Recently terminated instances might appear in the returned results.
     * This interval is usually less than one hour.
     *
     * @param string|array $instaceId       Set of instances IDs of which to get the status.
     * @param boolean                       Ture to ignore Terminated Instances.
     * @return array
     */
    public function describe($instanceId = null, $ignoreTerminated = false)
    {
        $params = array();
        $params['Action'] = 'DescribeInstances';

        if(is_array($instanceId) && !empty($instanceId)) {
            foreach($instanceId as $k=>$name) {
                $params['InstanceId.' . ($k+1)] = $name;
            }
        } elseif($instanceId) {
            $params['InstanceId.1'] = $instanceId;
        }

        $response = $this->sendRequest($params);

        $xpath = $response->getXPath();

        $nodes = $xpath->query('//ec2:reservationSet/ec2:item');

        $return = array();
        $return['instances'] = array();

        foreach($nodes as $node) {
            if($xpath->evaluate('string(ec2:instancesSet/ec2:item/ec2:instanceState/ec2:code/text())', $node) == 48 && $ignoreTerminated) continue;
            $item = array();

            $item['reservationId'] = $xpath->evaluate('string(ec2:reservationId/text())', $node);
            $item['ownerId'] = $xpath->evaluate('string(ec2:ownerId/text())', $node);

            $gs = $xpath->query('ec2:groupSet/ec2:item', $node);
            foreach($gs as $gs_node) {
                $item['groupSet'][] = $xpath->evaluate('string(ec2:groupId/text())', $gs_node);
                unset($gs_node);
            }
            unset($gs);

            $is = $xpath->query('ec2:instancesSet/ec2:item', $node);

            foreach($is as $is_node) {

                $item['instanceId'] = $xpath->evaluate('string(ec2:instanceId/text())', $is_node);
                $item['imageId'] = $xpath->evaluate('string(ec2:imageId/text())', $is_node);
                $item['instanceState']['code'] = $xpath->evaluate('string(ec2:instanceState/ec2:code/text())', $is_node);
                $item['instanceState']['name'] = $xpath->evaluate('string(ec2:instanceState/ec2:name/text())', $is_node);
                $item['privateDnsName'] = $xpath->evaluate('string(ec2:privateDnsName/text())', $is_node);
                $item['dnsName'] = $xpath->evaluate('string(ec2:dnsName/text())', $is_node);
                $item['keyName'] = $xpath->evaluate('string(ec2:keyName/text())', $is_node);
                $item['productCode'] = $xpath->evaluate('string(ec2:productCodesSet/ec2:item/ec2:productCode/text())', $is_node);
                $item['instanceType'] = $xpath->evaluate('string(ec2:instanceType/text())', $is_node);
                $item['launchTime'] = $xpath->evaluate('string(ec2:launchTime/text())', $is_node);
                $item['availabilityZone'] = $xpath->evaluate('string(ec2:placement/ec2:availabilityZone/text())', $is_node);
                $item['kernelId'] = $xpath->evaluate('string(ec2:kernelId/text())', $is_node);
                $item['ramediskId'] = $xpath->evaluate('string(ec2:ramediskId/text())', $is_node);
                $item['amiLaunchIndex'] = $xpath->evaluate('string(ec2:amiLaunchIndex/text())', $is_node);
                $item['monitoringState'] = $xpath->evaluate('string(ec2:monitoring/ec2:state/text())', $is_node);

                $return['instances'][] = $item;
                unset($is_node);
            }
            unset($item);
            unset($is);
        }

        return $return;
    }

    /**
     * Returns information about instances that you own that were started from
     * a specific imageId
     *
     * Recently terminated instances might appear in the returned results.
     * This interval is usually less than one hour.
     *
     * @param string $imageId               The imageId used to start the Instance.
     * @param boolean                       Ture to ignore Terminated Instances.
     * @return array
     */
    public function describeByImageId($imageId, $ignoreTerminated = false)
    {
        $arrInstances = $this->describe(null, $ignoreTerminated);

        $return = array();

        foreach($arrInstances['instances'] as $instance) {
            if($instance['imageId'] !== $imageId) continue;
            $return[] = $instance;
        }

        return $return;
    }

    /**
     * Shuts down one or more instances. This operation is idempotent; if you terminate
     * an instance more than once, each call will succeed.
     *
     * Terminated instances will remain visible after termination (approximately one hour).
     *
     * @param string|array $instanceId      One or more instance IDs returned.
     * @return array
     */
    public function terminate($instanceId)
    {
        $params = array();
        $params['Action'] = 'TerminateInstances';

        if(is_array($instanceId) && !empty($instanceId)) {
            foreach($instanceId as $k=>$name) {
                $params['InstanceId.' . ($k+1)] = $name;
            }
        } elseif($instanceId) {
            $params['InstanceId.1'] = $instanceId;
        }

        $response = $this->sendRequest($params);
        $xpath = $response->getXPath();

        $nodes = $xpath->query('//ec2:instancesSet/ec2:item');

        $return = array();
        foreach($nodes as $node) {
            $item = array();

            $item['instanceId'] = $xpath->evaluate('string(ec2:instanceId/text())', $node);
            $item['shutdownState']['code'] = $xpath->evaluate('string(ec2:shutdownState/ec2:code/text())', $node);
            $item['shutdownState']['name'] = $xpath->evaluate('string(ec2:shutdownState/ec2:name/text())', $node);
            $item['previousState']['code'] = $xpath->evaluate('string(ec2:previousState/ec2:code/text())', $node);
            $item['previousState']['name'] = $xpath->evaluate('string(ec2:previousState/ec2:name/text())', $node);

            $return[] = $item;
            unset($item);
        }

        return $return;
    }

    /**
     * Requests a reboot of one or more instances.
     *
     * This operation is asynchronous; it only queues a request to reboot the specified instance(s). The operation
     * will succeed if the instances are valid and belong to the user. Requests to reboot terminated instances are ignored.
     *
     * @param string|array $instanceId  One or more instance IDs.
     * @return boolean
     */
    public function reboot($instanceId)
    {
        $params = array();
        $params['Action'] = 'RebootInstances';

        if(is_array($instanceId) && !empty($instanceId)) {
            foreach($instanceId as $k=>$name) {
                $params['InstanceId.' . ($k+1)] = $name;
            }
        } elseif($instanceId) {
            $params['InstanceId.1'] = $instanceId;
        }

        $response = $this->sendRequest($params);
        $xpath = $response->getXPath();

        $return = $xpath->evaluate('string(//ec2:return/text())');

        return ($return === "true");
    }

    /**
     * Retrieves console output for the specified instance.
     *
     * Instance console output is buffered and posted shortly after instance boot, reboot, and termination.
     * Amazon EC2 preserves the most recent 64 KB output which will be available for at least one hour after the most recent post.
     *
     * @param string $instanceId       An instance ID
     * @return array
     */
    public function consoleOutput($instanceId)
    {
        $params = array();
        $params['Action'] = 'GetConsoleOutput';
        $params['InstanceId'] = $instanceId;

        $response = $this->sendRequest($params);
        $xpath = $response->getXPath();

        $return = array();

        $return['instanceId'] = $xpath->evaluate('string(//ec2:instanceId/text())');
        $return['timestamp'] = $xpath->evaluate('string(//ec2:timestamp/text())');
        $return['output'] = base64_decode($xpath->evaluate('string(//ec2:output/text())'));

        return $return;
    }

    /**
     * Returns true if the specified product code is attached to the specified instance.
     * The operation returns false if the product code is not attached to the instance.
     *
     * The confirmProduct operation can only be executed by the owner of the AMI.
     * This feature is useful when an AMI owner is providing support and wants to
     * verify whether a user's instance is eligible.
     *
     * @param string $productCode           The product code to confirm.
     * @param string $instanceId            The instance for which to confirm the product code.
     * @return array|boolean                An array if the product code is attached to the instance, false if it is not.
     */
    public function confirmProduct($productCode, $instanceId)
    {
        $params = array();
        $params['Action'] = 'ConfirmProductInstance';
        $params['ProductCode'] = $productCode;
        $params['InstanceId'] = $instanceId;

        $response = $this->sendRequest($params);
        $xpath = $response->getXPath();

        $result = $xpath->evaluate('string(//ec2:result/text())');

        if($result === "true") {
            $return['result'] = true;
            $return['ownerId'] = $xpath->evaluate('string(//ec2:ownerId/text())');

            return $return;
        }

        return false;
    }

    /**
    * Turn on Amazon CloudWatch Monitoring for an instance or a list of instances
    *
    * @param array|string $instanceId           The instance or list of instances you want to enable monitoring for
    * @return array
    */
    public function monitor($instanceId)
    {
        $params = array();
        $params['Action'] = 'MonitorInstances';

        if(is_array($instanceId) && !empty($instanceId)) {
            foreach($instanceId as $k=>$name) {
                $params['InstanceId.' . ($k+1)] = $name;
            }
        } elseif($instanceId) {
            $params['InstanceId.1'] = $instanceId;
        }

        $response = $this->sendRequest($params);
        $xpath = $response->getXPath();


        $items = $xpath->query('//ec2:instancesSet/ec2:item');

        $arrReturn = array();
        foreach($items as $item) {
            $i = array();
            $i['instanceid'] = $xpath->evaluate('string(//ec2:instanceId/text())', $item);
            $i['monitorstate'] = $xpath->evaluate('string(//ec2:monitoring/ec2:state/text())');
            $arrReturn[] = $i;
            unset($i);
        }

        return $arrReturn;
    }
    /**
    * Turn off Amazon CloudWatch Monitoring for an instance or a list of instances
    *
    * @param array|string $instanceId           The instance or list of instances you want to disable monitoring for
    * @return array
    */
    public function unmonitor($instanceId)
    {
        $params = array();
        $params['Action'] = 'UnmonitorInstances';

        if(is_array($instanceId) && !empty($instanceId)) {
            foreach($instanceId as $k=>$name) {
                $params['InstanceId.' . ($k+1)] = $name;
            }
        } elseif($instanceId) {
            $params['InstanceId.1'] = $instanceId;
        }

        $response = $this->sendRequest($params);
        $xpath = $response->getXPath();


        $items = $xpath->query('//ec2:instancesSet/ec2:item');

        $arrReturn = array();
        foreach($items as $item) {
            $i = array();
            $i['instanceid'] = $xpath->evaluate('string(//ec2:instanceId/text())', $item);
            $i['monitorstate'] = $xpath->evaluate('string(//ec2:monitoring/ec2:state/text())');
            $arrReturn[] = $i;
            unset($i);
        }

        return $arrReturn;
    }

}

