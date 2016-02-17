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
 * An Amazon EC2 interface to create, delete, describe, grand and revoke sercurity permissions.
 *
 * @category   Zend
 * @package    Zend_Service_Amazon
 * @subpackage Ec2
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Amazon_Ec2_Securitygroups extends Zend_Service_Amazon_Ec2_Abstract
{
    /**
     * Creates a new security group.
     *
     * Every instance is launched in a security group. If no security group is specified
     * during launch, the instances are launched in the default security group. Instances
     * within the same security group have unrestricted network access to each other.
     * Instances will reject network access attempts from other instances in a different
     * security group. As the owner of instances you can grant or revoke specific permissions
     * using the {@link authorizeIp}, {@link authorizeGroup}, {@link revokeGroup} and
     * {$link revokeIp} operations.
     *
     * @param string $name          Name of the new security group.
     * @param string $description   Description of the new security group.
     * @return boolean
     */
    public function create($name, $description)
    {
        $params = array();
        $params['Action'] = 'CreateSecurityGroup';
        $params['GroupName'] = $name;
        $params['GroupDescription'] = $description;

        $response = $this->sendRequest($params);
        $xpath = $response->getXPath();
        $success  = $xpath->evaluate('string(//ec2:return/text())');

        return ($success === "true");
    }

    /**
     * Returns information about security groups that you own.
     *
     * If you specify security group names, information about those security group is returned.
     * Otherwise, information for all security group is returned. If you specify a group
     * that does not exist, a fault is returned.
     *
     * @param string|array $name    List of security groups to describe
     * @return array
     */
    public function describe($name = null)
    {
        $params = array();
        $params['Action'] = 'DescribeSecurityGroups';
        if(is_array($name) && !empty($name)) {
            foreach($name as $k=>$name) {
                $params['GroupName.' . ($k+1)] = $name;
            }
        } elseif($name) {
            $params['GroupName.1'] = $name;
        }

        $response = $this->sendRequest($params);
        $xpath = $response->getXPath();

        $return = array();

        $nodes = $xpath->query('//ec2:securityGroupInfo/ec2:item');

        foreach($nodes as $node) {
            $item = array();

            $item['ownerId'] = $xpath->evaluate('string(ec2:ownerId/text())', $node);
            $item['groupName'] = $xpath->evaluate('string(ec2:groupName/text())', $node);
            $item['groupDescription'] = $xpath->evaluate('string(ec2:groupDescription/text())', $node);

            $ip_nodes = $xpath->query('ec2:ipPermissions/ec2:item', $node);

            foreach($ip_nodes as $ip_node) {
                $sItem = array();

                $sItem['ipProtocol'] = $xpath->evaluate('string(ec2:ipProtocol/text())', $ip_node);
                $sItem['fromPort'] = $xpath->evaluate('string(ec2:fromPort/text())', $ip_node);
                $sItem['toPort'] = $xpath->evaluate('string(ec2:toPort/text())', $ip_node);

                $ips = $xpath->query('ec2:ipRanges/ec2:item', $ip_node);

                $sItem['ipRanges'] = array();
                foreach($ips as $ip) {
                    $sItem['ipRanges'][] = $xpath->evaluate('string(ec2:cidrIp/text())', $ip);
                }

                if(count($sItem['ipRanges']) == 1) {
                    $sItem['ipRanges'] = $sItem['ipRanges'][0];
                }

                $item['ipPermissions'][] = $sItem;
                unset($ip_node, $sItem);
            }

            $return[] = $item;

            unset($item, $node);
        }


        return $return;
    }

    /**
     * Deletes a security group.
     *
     * If you attempt to delete a security group that contains instances, a fault is returned.
     * If you attempt to delete a security group that is referenced by another security group,
     * a fault is returned. For example, if security group B has a rule that allows access
     * from security group A, security group A cannot be deleted until the allow rule is removed.
     *
     * @param string $name          Name of the security group to delete.
     * @return boolean
     */
    public function delete($name)
    {
        $params = array();
        $params['Action'] = 'DeleteSecurityGroup';
        $params['GroupName'] = $name;

        $response = $this->sendRequest($params);
        $xpath = $response->getXPath();
        $success  = $xpath->evaluate('string(//ec2:return/text())');

        return ($success === "true");
    }

    /**
     * Adds permissions to a security group
     *
     * Permissions are specified by the IP protocol (TCP, UDP or ICMP), the source of the request
     * (by IP range or an Amazon EC2 user-group pair), the source and destination port ranges
     * (for TCP and UDP), and the ICMP codes and types (for ICMP). When authorizing ICMP, -1
     * can be used as a wildcard in the type and code fields.
     *
     * Permission changes are propagated to instances within the security group as quickly as
     * possible. However, depending on the number of instances, a small delay might occur.
     *
     *
     * @param string $name                  Name of the group to modify.
     * @param string $ipProtocol            IP protocol to authorize access to when operating on a CIDR IP.
     * @param integer $fromPort             Bottom of port range to authorize access to when operating on a CIDR IP.
     *                                      This contains the ICMP type if ICMP is being authorized.
     * @param integer $toPort               Top of port range to authorize access to when operating on a CIDR IP.
     *                                      This contains the ICMP code if ICMP is being authorized.
     * @param string $cidrIp                CIDR IP range to authorize access to when operating on a CIDR IP.
     * @return boolean
     */
    public function authorizeIp($name, $ipProtocol, $fromPort, $toPort, $cidrIp)
    {
        $params = array();
        $params['Action'] = 'AuthorizeSecurityGroupIngress';
        $params['GroupName'] = $name;
        $params['IpProtocol'] = $ipProtocol;
        $params['FromPort'] = $fromPort;
        $params['ToPort'] = $toPort;
        $params['CidrIp'] = $cidrIp;

        $response = $this->sendRequest($params);
        $xpath = $response->getXPath();
        $success  = $xpath->evaluate('string(//ec2:return/text())');

        return ($success === "true");

    }

    /**
     * Adds permissions to a security group
     *
     * When authorizing a user/group pair permission, GroupName, SourceSecurityGroupName and
     * SourceSecurityGroupOwnerId must be specified.
     *
     * Permission changes are propagated to instances within the security group as quickly as
     * possible. However, depending on the number of instances, a small delay might occur.
     *
     * @param string $name                  Name of the group to modify.
     * @param string $groupName             Name of security group to authorize access to when operating on a user/group pair.
     * @param string $ownerId               Owner of security group to authorize access to when operating on a user/group pair.
     * @return boolean
     */
    public function authorizeGroup($name, $groupName, $ownerId)
    {
        $params = array();
        $params['Action'] = 'AuthorizeSecurityGroupIngress';
        $params['GroupName'] = $name;
        $params['SourceSecurityGroupName'] = $groupName;
        $params['SourceSecurityGroupOwnerId'] = $ownerId;


        $response = $this->sendRequest($params);
        $xpath = $response->getXPath();
        $success  = $xpath->evaluate('string(//ec2:return/text())');


        return ($success === "true");
    }

    /**
     * Revokes permissions from a security group. The permissions used to revoke must be specified
     * using the same values used to grant the permissions.
     *
     * Permissions are specified by the IP protocol (TCP, UDP or ICMP), the source of the request
     * (by IP range or an Amazon EC2 user-group pair), the source and destination port ranges
     * (for TCP and UDP), and the ICMP codes and types (for ICMP). When authorizing ICMP, -1
     * can be used as a wildcard in the type and code fields.
     *
     * Permission changes are propagated to instances within the security group as quickly as
     * possible. However, depending on the number of instances, a small delay might occur.
     *
     *
     * @param string $name                  Name of the group to modify.
     * @param string $ipProtocol            IP protocol to revoke access to when operating on a CIDR IP.
     * @param integer $fromPort             Bottom of port range to revoke access to when operating on a CIDR IP.
     *                                      This contains the ICMP type if ICMP is being revoked.
     * @param integer $toPort               Top of port range to revoked access to when operating on a CIDR IP.
     *                                      This contains the ICMP code if ICMP is being revoked.
     * @param string $cidrIp                CIDR IP range to revoke access to when operating on a CIDR IP.
     * @return boolean
     */
    public function revokeIp($name, $ipProtocol, $fromPort, $toPort, $cidrIp)
    {
        $params = array();
        $params['Action'] = 'RevokeSecurityGroupIngress';
        $params['GroupName'] = $name;
        $params['IpProtocol'] = $ipProtocol;
        $params['FromPort'] = $fromPort;
        $params['ToPort'] = $toPort;
        $params['CidrIp'] = $cidrIp;

        $response = $this->sendRequest($params);
        $xpath = $response->getXPath();
        $success  = $xpath->evaluate('string(//ec2:return/text())');

        return ($success === "true");
    }

    /**
     * Revokes permissions from a security group. The permissions used to revoke must be specified
     * using the same values used to grant the permissions.
     *
     * Permission changes are propagated to instances within the security group as quickly as
     * possible. However, depending on the number of instances, a small delay might occur.
     *
     * When revoking a user/group pair permission, GroupName, SourceSecurityGroupName and
     * SourceSecurityGroupOwnerId must be specified.
     *
     * @param string $name                  Name of the group to modify.
     * @param string $groupName             Name of security group to revoke access to when operating on a user/group pair.
     * @param string $ownerId               Owner of security group to revoke access to when operating on a user/group pair.
     * @return boolean
     */
    public function revokeGroup($name, $groupName, $ownerId)
    {
        $params = array();
        $params['Action'] = 'RevokeSecurityGroupIngress';
        $params['GroupName'] = $name;
        $params['SourceSecurityGroupName'] = $groupName;
        $params['SourceSecurityGroupOwnerId'] = $ownerId;


        $response = $this->sendRequest($params);
        $xpath = $response->getXPath();
        $success  = $xpath->evaluate('string(//ec2:return/text())');


        return ($success === "true");
    }
}
