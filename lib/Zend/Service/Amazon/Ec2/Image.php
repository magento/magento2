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
 * @version    $Id: Image.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Service_Amazon_Ec2_Abstract
 */
#require_once 'Zend/Service/Amazon/Ec2/Abstract.php';

/**
 * An Amazon EC2 interface to register, describe and deregister Amamzon Machine Instances (AMI)
 *
 * @category   Zend
 * @package    Zend_Service_Amazon
 * @subpackage Ec2
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Amazon_Ec2_Image extends Zend_Service_Amazon_Ec2_Abstract
{
    /**
     * Registers an AMI with Amazon EC2. Images must be registered before
     * they can be launched.
     *
     * Each AMI is associated with an unique ID which is provided by the Amazon
     * EC2 service through the RegisterImage operation. During registration, Amazon
     * EC2 retrieves the specified image manifest from Amazon S3 and verifies that
     * the image is owned by the user registering the image.
     *
     * The image manifest is retrieved once and stored within the Amazon EC2.
     * Any modifications to an image in Amazon S3 invalidates this registration.
     * If you make changes to an image, deregister the previous image and register
     * the new image. For more information, see DeregisterImage.
     *
     * @param string $imageLocation         Full path to your AMI manifest in Amazon S3 storage.
     * @return string                       The ami fro the newly registred image;
     */
    public function register($imageLocation)
    {
        $params                 = array();
        $params['Action']       = 'RegisterImage';
        $params['ImageLocation']= $imageLocation;

        $response = $this->sendRequest($params);
        $xpath = $response->getXPath();

        $amiId = $xpath->evaluate('string(//ec2:imageId/text())');

        return $amiId;
    }

    /**
     * Returns information about AMIs, AKIs, and ARIs available to the user.
     * Information returned includes image type, product codes, architecture,
     * and kernel and RAM disk IDs. Images available to the user include public
     * images available for any user to launch, private images owned by the user
     * making the request, and private images owned by other users for which the
     * user has explicit launch permissions.
     *
     * Launch permissions fall into three categories:
     *      public: The owner of the AMI granted launch permissions for the AMI
     *              to the all group. All users have launch permissions for these AMIs.
     *      explicit: The owner of the AMI granted launch permissions to a specific user.
     *      implicit: A user has implicit launch permissions for all AMIs he or she owns.
     *
     * The list of AMIs returned can be modified by specifying AMI IDs, AMI owners,
     * or users with launch permissions. If no options are specified, Amazon EC2 returns
     * all AMIs for which the user has launch permissions.
     *
     * If you specify one or more AMI IDs, only AMIs that have the specified IDs are returned.
     * If you specify an invalid AMI ID, a fault is returned. If you specify an AMI ID for which
     * you do not have access, it will not be included in the returned results.
     *
     * If you specify one or more AMI owners, only AMIs from the specified owners and for
     * which you have access are returned. The results can include the account IDs of the
     * specified owners, amazon for AMIs owned by Amazon or self for AMIs that you own.
     *
     * If you specify a list of executable users, only users that have launch permissions
     * for the AMIs are returned. You can specify account IDs (if you own the AMI(s)), self
     * for AMIs for which you own or have explicit permissions, or all for public AMIs.
     *
     * @param string|array $imageId             A list of image descriptions
     * @param string|array $owner               Owners of AMIs to describe.
     * @param string|array $executableBy        AMIs for which specified users have access.
     * @return array
     */
    public function describe($imageId = null, $owner = null, $executableBy = null)
    {
        $params = array();
        $params['Action'] = 'DescribeImages';

        if(is_array($imageId) && !empty($imageId)) {
            foreach($imageId as $k=>$name) {
                $params['ImageId.' . ($k+1)] = $name;
            }
        } elseif($imageId) {
            $params['ImageId.1'] = $imageId;
        }

        if(is_array($owner) && !empty($owner)) {
            foreach($owner as $k=>$name) {
                $params['Owner.' . ($k+1)] = $name;
            }
        } elseif($owner) {
            $params['Owner.1'] = $owner;
        }

        if(is_array($executableBy) && !empty($executableBy)) {
            foreach($executableBy as $k=>$name) {
                $params['ExecutableBy.' . ($k+1)] = $name;
            }
        } elseif($executableBy) {
            $params['ExecutableBy.1'] = $executableBy;
        }

        $response = $this->sendRequest($params);

        $xpath  = $response->getXPath();
        $nodes = $xpath->query('//ec2:imagesSet/ec2:item');

        $return = array();
        foreach ($nodes as $node) {
            $item = array();

            $item['imageId']        = $xpath->evaluate('string(ec2:imageId/text())', $node);
            $item['imageLocation']  = $xpath->evaluate('string(ec2:imageLocation/text())', $node);
            $item['imageState']     = $xpath->evaluate('string(ec2:imageState/text())', $node);
            $item['imageOwnerId']   = $xpath->evaluate('string(ec2:imageOwnerId/text())', $node);
            $item['isPublic']       = $xpath->evaluate('string(ec2:isPublic/text())', $node);
            $item['architecture']   = $xpath->evaluate('string(ec2:architecture/text())', $node);
            $item['imageType']      = $xpath->evaluate('string(ec2:imageType/text())', $node);
            $item['kernelId']       = $xpath->evaluate('string(ec2:kernelId/text())', $node);
            $item['ramdiskId']      = $xpath->evaluate('string(ec2:ramdiskId/text())', $node);
            $item['platform']       = $xpath->evaluate('string(ec2:platform/text())', $node);

            $return[] = $item;
            unset($item, $node);
        }

        return $return;
    }

    /**
     * Deregisters an AMI. Once deregistered, instances of the AMI can no longer be launched.
     *
     * @param string $imageId                   Unique ID of a machine image, returned by a call
     *                                          to RegisterImage or DescribeImages.
     * @return boolean
     */
    public function deregister($imageId)
    {
        $params                 = array();
        $params['Action']       = 'DeregisterImage';
        $params['ImageId']      = $imageId;

        $response = $this->sendRequest($params);
        $xpath = $response->getXPath();

        $return = $xpath->evaluate('string(//ec2:return/text())');

        return ($return === "true");
    }

    /**
     * Modifies an attribute of an AMI.
     *
     * Valid Attributes:
     *       launchPermission:  Controls who has permission to launch the AMI. Launch permissions
     *                          can be granted to specific users by adding userIds.
     *                          To make the AMI public, add the all group.
     *       productCodes:      Associates a product code with AMIs. This allows developers to
     *                          charge users for using AMIs. The user must be signed up for the
     *                          product before they can launch the AMI. This is a write once attribute;
     *                          after it is set, it cannot be changed or removed.
     *
     * @param string $imageId                   AMI ID to modify.
     * @param string $attribute                 Specifies the attribute to modify. See the preceding
     *                                          attributes table for supported attributes.
     * @param string $operationType             Specifies the operation to perform on the attribute.
     *                                          See the preceding attributes table for supported operations for attributes.
     *                                          Valid Values: add | remove
     *                                          Required for launchPermssion Attribute
     *
     * @param string|array $userId              User IDs to add to or remove from the launchPermission attribute.
     *                                          Required for launchPermssion Attribute
     * @param string|array $userGroup           User groups to add to or remove from the launchPermission attribute.
     *                                          Currently, the all group is available, which will make it a public AMI.
     *                                          Required for launchPermssion Attribute
     * @param string $productCode               Attaches a product code to the AMI. Currently only one product code
     *                                          can be associated with an AMI. Once set, the product code cannot be changed or reset.
     *                                          Required for productCodes Attribute
     * @return boolean
     */
    public function modifyAttribute($imageId, $attribute, $operationType = 'add', $userId = null, $userGroup = null, $productCode = null)
    {
        $params = array();
        $params['Action'] = 'ModifyImageAttribute';
        $parmas['ImageId'] = $imageId;
        $params['Attribute'] = $attribute;

        switch($attribute) {
            case 'launchPermission':
                // break left out
            case 'launchpermission':
                $params['Attribute'] = 'launchPermission';
                $params['OperationType'] = $operationType;

                if(is_array($userId) && !empty($userId)) {
                    foreach($userId as $k=>$name) {
                        $params['UserId.' . ($k+1)] = $name;
                    }
                } elseif($userId) {
                    $params['UserId.1'] = $userId;
                }

                if(is_array($userGroup) && !empty($userGroup)) {
                    foreach($userGroup as $k=>$name) {
                        $params['UserGroup.' . ($k+1)] = $name;
                    }
                } elseif($userGroup) {
                    $params['UserGroup.1'] = $userGroup;
                }

                break;
            case 'productCodes':
                // break left out
            case 'productcodes':
                $params['Attribute'] = 'productCodes';
                $params['ProductCode.1'] = $productCode;
                break;
            default:
                #require_once 'Zend/Service/Amazon/Ec2/Exception.php';
                throw new Zend_Service_Amazon_Ec2_Exception('Invalid Attribute Passed In.  Valid Image Attributes are launchPermission and productCode.');
                break;
        }

        $response = $this->sendRequest($params);
        $xpath = $response->getXPath();

        $return = $xpath->evaluate('string(//ec2:return/text())');

        return ($return === "true");
    }

    /**
     * Returns information about an attribute of an AMI. Only one attribute can be specified per call.
     *
     * @param string $imageId                   ID of the AMI for which an attribute will be described.
     * @param string $attribute                 Specifies the attribute to describe.  Valid Attributes are
     *                                          launchPermission, productCodes
     */
    public function describeAttribute($imageId, $attribute)
    {
        $params = array();
        $params['Action'] = 'DescribeImageAttribute';
        $params['ImageId'] = $imageId;
        $params['Attribute'] = $attribute;

        $response = $this->sendRequest($params);
        $xpath = $response->getXPath();

        $return = array();
        $return['imageId'] = $xpath->evaluate('string(//ec2:imageId/text())');

        // check for launchPermission
        if($attribute == 'launchPermission') {
            $lPnodes = $xpath->query('//ec2:launchPermission/ec2:item');

            if($lPnodes->length > 0) {
                $return['launchPermission'] = array();
                foreach($lPnodes as $node) {
                    $return['launchPermission'][] = $xpath->evaluate('string(ec2:userId/text())', $node);
                }
            }
        }

        // check for product codes
        if($attribute == 'productCodes') {
            $pCnodes = $xpath->query('//ec2:productCodes/ec2:item');
            if($pCnodes->length > 0) {
                $return['productCodes'] = array();
                foreach($pCnodes as $node) {
                    $return['productCodes'][] = $xpath->evaluate('string(ec2:productCode/text())', $node);
                }
            }
        }

        return $return;

    }

    /**
     * Resets an attribute of an AMI to its default value.  The productCodes attribute cannot be reset
     *
     * @param string $imageId                   ID of the AMI for which an attribute will be reset.
     * @param String $attribute                 Specifies the attribute to reset. Currently, only launchPermission is supported.
     *                                          In the case of launchPermission, all public and explicit launch permissions for
     *                                          the AMI are revoked.
     * @return boolean
     */
    public function resetAttribute($imageId, $attribute)
    {
        $params = array();
        $params['Action'] = 'ResetImageAttribute';
        $params['ImageId'] = $imageId;
        $params['Attribute'] = $attribute;

        $response = $this->sendRequest($params);
        $xpath = $response->getXPath();

        $return = $xpath->evaluate('string(//ec2:return/text())');

        return ($return === "true");
    }
}
