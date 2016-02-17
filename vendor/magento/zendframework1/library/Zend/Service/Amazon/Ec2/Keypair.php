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
 * An Amazon EC2 interface to create, delete and describe Ec2 KeyPairs.
 *
 * @category   Zend
 * @package    Zend_Service_Amazon
 * @subpackage Ec2
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Amazon_Ec2_Keypair extends Zend_Service_Amazon_Ec2_Abstract
{
    /**
     * Creates a new 2048 bit RSA key pair and returns a unique ID that can
     * be used to reference this key pair when launching new instances.
     *
     * @param string $keyName           A unique name for the key pair.
     * @throws Zend_Service_Amazon_Ec2_Exception
     * @return array
     */
    public function create($keyName)
    {
        $params = array();

        $params['Action'] = 'CreateKeyPair';

        if(!$keyName) {
            #require_once 'Zend/Service/Amazon/Ec2/Exception.php';
            throw new Zend_Service_Amazon_Ec2_Exception('Invalid Key Name');
        }

        $params['KeyName'] = $keyName;

        $response = $this->sendRequest($params);
        $xpath = $response->getXPath();

        $return = array();
        $return['keyName']          = $xpath->evaluate('string(//ec2:keyName/text())');
        $return['keyFingerprint']   = $xpath->evaluate('string(//ec2:keyFingerprint/text())');
        $return['keyMaterial']      = $xpath->evaluate('string(//ec2:keyMaterial/text())');

        return $return;
    }

    /**
     * Returns information about key pairs available to you. If you specify
     * key pairs, information about those key pairs is returned. Otherwise,
     * information for all registered key pairs is returned.
     *
     * @param string|rarray $keyName    Key pair IDs to describe.
     * @return array
     */
    public function describe($keyName = null)
    {
        $params = array();

        $params['Action'] = 'DescribeKeyPairs';
        if(is_array($keyName) && !empty($keyName)) {
            foreach($keyName as $k=>$name) {
                $params['KeyName.' . ($k+1)] = $name;
            }
        } elseif($keyName) {
            $params['KeyName.1'] = $keyName;
        }

        $response = $this->sendRequest($params);
        $xpath = $response->getXPath();

        $nodes  = $xpath->query('//ec2:keySet/ec2:item');

        $return = array();
        foreach ($nodes as $k => $node) {
            $item = array();
            $item['keyName']          = $xpath->evaluate('string(ec2:keyName/text())', $node);
            $item['keyFingerprint']   = $xpath->evaluate('string(ec2:keyFingerprint/text())', $node);

            $return[] = $item;
            unset($item);
        }

        return $return;
    }

    /**
     * Deletes a key pair
     *
     * @param string $keyName           Name of the key pair to delete.
     * @throws Zend_Service_Amazon_Ec2_Exception
     * @return boolean                  Return true or false from the deletion.
     */
    public function delete($keyName)
    {
        $params = array();

        $params['Action'] = 'DeleteKeyPair';

        if(!$keyName) {
            #require_once 'Zend/Service/Amazon/Ec2/Exception.php';
            throw new Zend_Service_Amazon_Ec2_Exception('Invalid Key Name');
        }

        $params['KeyName'] = $keyName;

        $response = $this->sendRequest($params);

        $xpath = $response->getXPath();
        $success  = $xpath->evaluate('string(//ec2:return/text())');

        return ($success === "true");
    }
}
