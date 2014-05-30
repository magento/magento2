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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Elasticip.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Service_Amazon_Ec2_Abstract
 */
#require_once 'Zend/Service/Amazon/Ec2/Abstract.php';

/**
 * An Amazon EC2 interface to allocate, associate, describe and release Elastic IP address
 * from your account.
 *
 * @category   Zend
 * @package    Zend_Service_Amazon
 * @subpackage Ec2
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Amazon_Ec2_Elasticip extends Zend_Service_Amazon_Ec2_Abstract
{
    /**
     * Acquires an elastic IP address for use with your account
     *
     * @return string                           Returns the newly Allocated IP Address
     */
    public function allocate()
    {
        $params = array();
        $params['Action'] = 'AllocateAddress';

        $response = $this->sendRequest($params);

        $xpath = $response->getXPath();
        $ip = $xpath->evaluate('string(//ec2:publicIp/text())');

        return $ip;
    }

    /**
     * Lists elastic IP addresses assigned to your account.
     *
     * @param string|array $publicIp            Elastic IP or list of addresses to describe.
     * @return array
     */
    public function describe($publicIp = null)
    {
        $params = array();
        $params['Action'] = 'DescribeAddresses';

        if(is_array($publicIp) && !empty($publicIp)) {
            foreach($publicIp as $k=>$name) {
                $params['PublicIp.' . ($k+1)] = $name;
            }
        } elseif($publicIp) {
            $params['PublicIp.1'] = $publicIp;
        }

        $response = $this->sendRequest($params);

        $xpath  = $response->getXPath();
        $nodes  = $xpath->query('//ec2:item');

        $return = array();
        foreach ($nodes as $k => $node) {
            $item = array();
            $item['publicIp']  = $xpath->evaluate('string(ec2:publicIp/text())', $node);
            $item['instanceId']   = $xpath->evaluate('string(ec2:instanceId/text())', $node);

            $return[] = $item;
            unset($item);
        }

        return $return;
    }

    /**
     * Releases an elastic IP address that is associated with your account
     *
     * @param string $publicIp                  IP address that you are releasing from your account.
     * @return boolean
     */
    public function release($publicIp)
    {
        $params = array();
        $params['Action'] = 'ReleaseAddress';
        $params['PublicIp'] = $publicIp;

        $response = $this->sendRequest($params);
        $xpath = $response->getXPath();

        $return = $xpath->evaluate('string(//ec2:return/text())');

        return ($return === "true");
    }

    /**
     * Associates an elastic IP address with an instance
     *
     * @param string $instanceId                The instance to which the IP address is assigned
     * @param string $publicIp                  IP address that you are assigning to the instance.
     * @return boolean
     */
    public function associate($instanceId, $publicIp)
    {
        $params = array();
        $params['Action'] = 'AssociateAddress';
        $params['PublicIp'] = $publicIp;
        $params['InstanceId'] = $instanceId;

        $response = $this->sendRequest($params);
        $xpath = $response->getXPath();

        $return = $xpath->evaluate('string(//ec2:return/text())');

        return ($return === "true");
    }

    /**
     * Disassociates the specified elastic IP address from the instance to which it is assigned.
     * This is an idempotent operation. If you enter it more than once, Amazon EC2 does not return an error.
     *
     * @param string $publicIp                  IP address that you are disassociating from the instance.
     * @return boolean
     */
    public function disassocate($publicIp)
    {
        $params = array();
        $params['Action'] = 'DisssociateAddress';
        $params['PublicIp'] = $publicIp;

        $response = $this->sendRequest($params);
        $xpath = $response->getXPath();

        $return = $xpath->evaluate('string(//ec2:return/text())');

        return ($return === "true");
    }

}
