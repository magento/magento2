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
 * @version    $Id: Region.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Service_Amazon_Ec2_Abstract
 */
#require_once 'Zend/Service/Amazon/Ec2/Abstract.php';

/**
 * An Amazon EC2 interface to query which Regions your account has access to.
 *
 * @category   Zend
 * @package    Zend_Service_Amazon
 * @subpackage Ec2
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Amazon_Ec2_Region extends Zend_Service_Amazon_Ec2_Abstract
{

    /**
     * Describes availability zones that are currently available to the account
     * and their states.
     *
     * @param string|array $region              Name of an region.
     * @return array                            An array that contains all the return items.  Keys: regionName and regionUrl.
     */
    public function describe($region = null)
    {
        $params = array();
        $params['Action'] = 'DescribeRegions';

        if(is_array($region) && !empty($region)) {
            foreach($region as $k=>$name) {
                $params['Region.' . ($k+1)] = $name;
            }
        } elseif($region) {
            $params['Region.1'] = $region;
        }

        $response = $this->sendRequest($params);

        $xpath  = $response->getXPath();
        $nodes  = $xpath->query('//ec2:item');

        $return = array();
        foreach ($nodes as $k => $node) {
            $item = array();
            $item['regionName']   = $xpath->evaluate('string(ec2:regionName/text())', $node);
            $item['regionUrl']  = $xpath->evaluate('string(ec2:regionUrl/text())', $node);

            $return[] = $item;
            unset($item);
        }

        return $return;
    }
}
