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
 * @version    $Id: Ebs.php 22047 2010-04-28 22:14:51Z shahar $
 */

/**
 * @see Zend_Service_Amazon_Ec2_Abstract
 */
#require_once 'Zend/Service/Amazon/Ec2/Abstract.php';

/**
 * An Amazon EC2 interface to create, describe, attach, detach and delete Elastic Block
 * Storage Volumes and Snaphsots.
 *
 * @category   Zend
 * @package    Zend_Service_Amazon
 * @subpackage Ec2
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Amazon_Ec2_Ebs extends Zend_Service_Amazon_Ec2_Abstract
{
    /**
     * Creates a new Amazon EBS volume that you can mount from any Amazon EC2 instance.
     *
     * You must specify an availability zone when creating a volume. The volume and
     * any instance to which it attaches must be in the same availability zone.
     *
     * @param string $size                  The size of the volume, in GiB.
     * @param string $availabilityZone      The availability zone in which to create the new volume.
     * @return array
     */
    public function createNewVolume($size, $availabilityZone)
    {
        $params = array();
        $params['Action'] = 'CreateVolume';
        $params['AvailabilityZone'] = $availabilityZone;
        $params['Size'] = $size;

        $response = $this->sendRequest($params);
        $xpath = $response->getXPath();

        $return = array();
        $return['volumeId']             = $xpath->evaluate('string(//ec2:volumeId/text())');
        $return['size']                 = $xpath->evaluate('string(//ec2:size/text())');
        $return['status']               = $xpath->evaluate('string(//ec2:status/text())');
        $return['createTime']           = $xpath->evaluate('string(//ec2:createTime/text())');
        $return['availabilityZone']     = $xpath->evaluate('string(//ec2:availabilityZone/text())');

        return $return;
    }

    /**
     * Creates a new Amazon EBS volume that you can mount from any Amazon EC2 instance.
     *
     * You must specify an availability zone when creating a volume. The volume and
     * any instance to which it attaches must be in the same availability zone.
     *
     * @param string $snapshotId            The snapshot from which to create the new volume.
     * @param string $availabilityZone      The availability zone in which to create the new volume.
     * @return array
     */
    public function createVolumeFromSnapshot($snapshotId, $availabilityZone)
    {
        $params = array();
        $params['Action'] = 'CreateVolume';
        $params['AvailabilityZone'] = $availabilityZone;
        $params['SnapshotId'] = $snapshotId;

        $response = $this->sendRequest($params);
        $xpath = $response->getXPath();

        $return = array();
        $return['volumeId']             = $xpath->evaluate('string(//ec2:volumeId/text())');
        $return['size']                 = $xpath->evaluate('string(//ec2:size/text())');
        $return['status']               = $xpath->evaluate('string(//ec2:status/text())');
        $return['createTime']           = $xpath->evaluate('string(//ec2:createTime/text())');
        $return['availabilityZone']     = $xpath->evaluate('string(//ec2:availabilityZone/text())');
        $return['snapshotId']           = $xpath->evaluate('string(//ec2:snapshotId/text())');

        return $return;
    }

    /**
     * Lists one or more Amazon EBS volumes that you own, If you do not
     * specify any volumes, Amazon EBS returns all volumes that you own.
     *
     * @param string|array $volumeId        The ID or array of ID's of the volume(s) to list
     * @return array
     */
    public function describeVolume($volumeId = null)
    {
        $params = array();
        $params['Action'] = 'DescribeVolumes';

        if(is_array($volumeId) && !empty($volumeId)) {
            foreach($volumeId as $k=>$name) {
                $params['VolumeId.' . ($k+1)] = $name;
            }
        } elseif($volumeId) {
            $params['VolumeId.1'] = $volumeId;
        }

        $response = $this->sendRequest($params);

        $xpath  = $response->getXPath();
        $nodes = $xpath->query('//ec2:volumeSet/ec2:item', $response->getDocument());

        $return = array();
        foreach ($nodes as $node) {
            $item = array();

            $item['volumeId']   = $xpath->evaluate('string(ec2:volumeId/text())', $node);
            $item['size']       = $xpath->evaluate('string(ec2:size/text())', $node);
            $item['status']     = $xpath->evaluate('string(ec2:status/text())', $node);
            $item['createTime'] = $xpath->evaluate('string(ec2:createTime/text())', $node);

            $attachmentSet = $xpath->query('ec2:attachmentSet/ec2:item', $node);
            if($attachmentSet->length == 1) {
                $_as = $attachmentSet->item(0);
                $as = array();
                $as['volumeId'] = $xpath->evaluate('string(ec2:volumeId/text())', $_as);
                $as['instanceId'] = $xpath->evaluate('string(ec2:instanceId/text())', $_as);
                $as['device'] = $xpath->evaluate('string(ec2:device/text())', $_as);
                $as['status'] = $xpath->evaluate('string(ec2:status/text())', $_as);
                $as['attachTime'] = $xpath->evaluate('string(ec2:attachTime/text())', $_as);
                $item['attachmentSet'] = $as;
            }

            $return[] = $item;
            unset($item, $node);
        }

        return $return;
    }

    public function describeAttachedVolumes($instanceId)
    {
        $volumes = $this->describeVolume();

        $return = array();
        foreach($volumes as $vol) {
            if(isset($vol['attachmentSet']) && $vol['attachmentSet']['instanceId'] == $instanceId) {
                $return[] = $vol;
            }
        }

        return $return;
    }

    /**
     * Attaches an Amazon EBS volume to an instance
     *
     * @param string $volumeId              The ID of the Amazon EBS volume
     * @param string $instanceId            The ID of the instance to which the volume attaches
     * @param string $device                Specifies how the device is exposed to the instance (e.g., /dev/sdh).
     * @return array
     */
    public function attachVolume($volumeId, $instanceId, $device)
    {
        $params = array();
        $params['Action']       = 'AttachVolume';
        $params['VolumeId']     = $volumeId;
        $params['InstanceId']   = $instanceId;
        $params['Device']       = $device;

        $response = $this->sendRequest($params);

        $xpath = $response->getXPath();

        $return = array();
        $return['volumeId']     = $xpath->evaluate('string(//ec2:volumeId/text())');
        $return['instanceId']   = $xpath->evaluate('string(//ec2:instanceId/text())');
        $return['device']       = $xpath->evaluate('string(//ec2:device/text())');
        $return['status']       = $xpath->evaluate('string(//ec2:status/text())');
        $return['attachTime']   = $xpath->evaluate('string(//ec2:attachTime/text())');

        return $return;
    }

    /**
     * Detaches an Amazon EBS volume from an instance
     *
     * @param string $volumeId              The ID of the Amazon EBS volume
     * @param string $instanceId            The ID of the instance from which the volume will detach
     * @param string $device                The device name
     * @param boolean $force                Forces detachment if the previous detachment attempt did not occur cleanly
     *                                      (logging into an instance, unmounting the volume, and detaching normally).
     *                                      This option can lead to data loss or a corrupted file system. Use this option
     *                                      only as a last resort to detach an instance from a failed instance. The
     *                                      instance will not have an opportunity to flush file system caches nor
     *                                      file system meta data.
     * @return array
     */
    public function detachVolume($volumeId, $instanceId = null, $device = null, $force = false)
    {
        $params = array();
        $params['Action']       = 'DetachVolume';
        $params['VolumeId']     = $volumeId;
        $params['InstanceId']   = strval($instanceId);
        $params['Device']       = strval($device);
        $params['Force']        = strval($force);

        $response = $this->sendRequest($params);

        $xpath = $response->getXPath();

        $return = array();
        $return['volumeId']     = $xpath->evaluate('string(//ec2:volumeId/text())');
        $return['instanceId']   = $xpath->evaluate('string(//ec2:instanceId/text())');
        $return['device']       = $xpath->evaluate('string(//ec2:device/text())');
        $return['status']       = $xpath->evaluate('string(//ec2:status/text())');
        $return['attachTime']   = $xpath->evaluate('string(//ec2:attachTime/text())');

        return $return;
    }

    /**
     * Deletes an Amazon EBS volume
     *
     * @param string $volumeId              The ID of the volume to delete
     * @return boolean
     */
    public function deleteVolume($volumeId)
    {
        $params = array();
        $params['Action']       = 'DeleteVolume';
        $params['VolumeId']     = $volumeId;

        $response = $this->sendRequest($params);
        $xpath = $response->getXPath();

        $return = $xpath->evaluate('string(//ec2:return/text())');

        return ($return === "true");
    }

    /**
     * Creates a snapshot of an Amazon EBS volume and stores it in Amazon S3. You can use snapshots for backups,
     * to launch instances from identical snapshots, and to save data before shutting down an instance
     *
     * @param string $volumeId              The ID of the Amazon EBS volume to snapshot
     * @return array
     */
    public function createSnapshot($volumeId)
    {
        $params = array();
        $params['Action']       = 'CreateSnapshot';
        $params['VolumeId']     = $volumeId;

        $response = $this->sendRequest($params);

        $xpath = $response->getXPath();

        $return = array();
        $return['snapshotId']   = $xpath->evaluate('string(//ec2:snapshotId/text())');
        $return['volumeId']     = $xpath->evaluate('string(//ec2:volumeId/text())');
        $return['status']       = $xpath->evaluate('string(//ec2:status/text())');
        $return['startTime']    = $xpath->evaluate('string(//ec2:startTime/text())');
        $return['progress']     = $xpath->evaluate('string(//ec2:progress/text())');

        return $return;
    }

    /**
     * Describes the status of Amazon EBS snapshots
     *
     * @param string|array $snapshotId      The ID or arry of ID's of the Amazon EBS snapshot
     * @return array
     */
    public function describeSnapshot($snapshotId = null)
    {
        $params = array();
        $params['Action'] = 'DescribeSnapshots';

        if(is_array($snapshotId) && !empty($snapshotId)) {
            foreach($snapshotId as $k=>$name) {
                $params['SnapshotId.' . ($k+1)] = $name;
            }
        } elseif($snapshotId) {
            $params['SnapshotId.1'] = $snapshotId;
        }

        $response = $this->sendRequest($params);

        $xpath  = $response->getXPath();
        $nodes = $xpath->query('//ec2:snapshotSet/ec2:item', $response->getDocument());

        $return = array();
        foreach ($nodes as $node) {
            $item = array();

            $item['snapshotId'] = $xpath->evaluate('string(ec2:snapshotId/text())', $node);
            $item['volumeId']   = $xpath->evaluate('string(ec2:volumeId/text())', $node);
            $item['status']     = $xpath->evaluate('string(ec2:status/text())', $node);
            $item['startTime']  = $xpath->evaluate('string(ec2:startTime/text())', $node);
            $item['progress']   = $xpath->evaluate('string(ec2:progress/text())', $node);

            $return[] = $item;
            unset($item, $node);
        }

        return $return;
    }

    /**
     * Deletes a snapshot of an Amazon EBS  volume that is stored in Amazon S3
     *
     * @param string $snapshotId            The ID of the Amazon EBS snapshot to delete
     * @return boolean
     */
    public function deleteSnapshot($snapshotId)
    {
        $params = array();
        $params['Action']       = 'DeleteSnapshot';
        $params['SnapshotId']   = $snapshotId;

        $response = $this->sendRequest($params);

        $xpath = $response->getXPath();
        $return = $xpath->evaluate('string(//ec2:return/text())');

        return ($return === "true");
    }
}
