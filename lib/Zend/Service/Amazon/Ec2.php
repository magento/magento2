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
 * @subpackage Amazon
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Ec2.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * Amazon Ec2 Interface to allow easy creation of the Ec2 Components
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Amazon
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Amazon_Ec2
{
    /**
     * Factory method to fetch what you want to work with.
     *
     * @param string $section           Create the method that you want to work with
     * @param string $key               Override the default aws key
     * @param string $secret_key        Override the default aws secretkey
     * @throws Zend_Service_Amazon_Ec2_Exception
     * @return object
     */
    public static function factory($section, $key = null, $secret_key = null)
    {
        switch(strtolower($section)) {
            case 'keypair':
                $class = 'Zend_Service_Amazon_Ec2_Keypair';
                break;
            case 'eip':
                // break left out
            case 'elasticip':
                $class = 'Zend_Service_Amazon_Ec2_Elasticip';
                break;
            case 'ebs':
                $class = 'Zend_Service_Amazon_Ec2_Ebs';
                break;
            case 'availabilityzones':
                // break left out
            case 'zones':
                $class = 'Zend_Service_Amazon_Ec2_Availabilityzones';
                break;
            case 'ami':
                // break left out
            case 'image':
                $class = 'Zend_Service_Amazon_Ec2_Image';
                break;
            case 'instance':
                $class = 'Zend_Service_Amazon_Ec2_Instance';
                break;
            case 'security':
                // break left out
            case 'securitygroups':
                $class = 'Zend_Service_Amazon_Ec2_Securitygroups';
                break;
            default:
                throw new Zend_Service_Amazon_Ec2_Exception('Invalid Section: ' . $section);
                break;
        }

        if (!class_exists($class)) {
            #require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($class);
        }
        return new $class($key, $secret_key);
    }
}

