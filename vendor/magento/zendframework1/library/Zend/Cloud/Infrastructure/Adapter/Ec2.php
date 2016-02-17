<?php
/**
 * @category   Zend
 * @package    Zend_Cloud_Infrastructure
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

#require_once 'Zend/Service/Amazon/Ec2/Instance.php';
#require_once 'Zend/Service/Amazon/Ec2/Image.php';
#require_once 'Zend/Service/Amazon/Ec2/Availabilityzones.php';
#require_once 'Zend/Service/Amazon/Ec2/CloudWatch.php';
#require_once 'Zend/Cloud/Infrastructure/Instance.php';
#require_once 'Zend/Cloud/Infrastructure/InstanceList.php';
#require_once 'Zend/Cloud/Infrastructure/Image.php';
#require_once 'Zend/Cloud/Infrastructure/ImageList.php';
#require_once 'Zend/Cloud/Infrastructure/Adapter/AbstractAdapter.php';

/**
 * Amazon EC2 adapter for infrastructure service
 *
 * @package    Zend_Cloud_Infrastructure
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cloud_Infrastructure_Adapter_Ec2 extends Zend_Cloud_Infrastructure_Adapter_AbstractAdapter
{
    /**
     * AWS constants
     */
    const AWS_ACCESS_KEY     = 'aws_accesskey';
    const AWS_SECRET_KEY     = 'aws_secretkey';
    const AWS_REGION         = 'aws_region';
    const AWS_SECURITY_GROUP = 'securityGroup';

    /**
     * Ec2 Instance
     *
     * @var Ec2Instance
     */
    protected $ec2;

    /**
     * Ec2 Image
     *
     * @var Ec2Image
     */
    protected $ec2Image;

    /**
     * Ec2 Zone
     *
     * @var Ec2Zone
     */
    protected $ec2Zone;

    /**
     * Ec2 Monitor
     *
     * @var Ec2Monitor
     */
    protected $ec2Monitor;

    /**
     * AWS Access Key
     *
     * @var string
     */
    protected $accessKey;

    /**
     * AWS Access Secret
     *
     * @var string
     */
    protected $accessSecret;

    /**
     * Region zone
     *
     * @var string
     */
    protected $region;

    /**
     * Map array between EC2 and Infrastructure status
     *
     * @var array
     */
    protected $mapStatus = array (
        'running'       => Zend_Cloud_Infrastructure_Instance::STATUS_RUNNING,
        'terminated'    => Zend_Cloud_Infrastructure_Instance::STATUS_TERMINATED,
        'pending'       => Zend_Cloud_Infrastructure_Instance::STATUS_PENDING,
        'shutting-down' => Zend_Cloud_Infrastructure_Instance::STATUS_SHUTTING_DOWN,
        'stopping'      => Zend_Cloud_Infrastructure_Instance::STATUS_PENDING,
        'stopped'       => Zend_Cloud_Infrastructure_Instance::STATUS_STOPPED,
        'rebooting'     => Zend_Cloud_Infrastructure_Instance::STATUS_REBOOTING,
    );

    /**
     * Map monitor metrics between Infrastructure and EC2
     *
     * @var array
     */
    protected $mapMetrics= array (
        Zend_Cloud_Infrastructure_Instance::MONITOR_CPU         => 'CPUUtilization',
        Zend_Cloud_Infrastructure_Instance::MONITOR_DISK_READ   => 'DiskReadBytes',
        Zend_Cloud_Infrastructure_Instance::MONITOR_DISK_WRITE  => 'DiskWriteBytes',
        Zend_Cloud_Infrastructure_Instance::MONITOR_NETWORK_IN  => 'NetworkIn',
        Zend_Cloud_Infrastructure_Instance::MONITOR_NETWORK_OUT => 'NetworkOut',
    );

    /**
     * Constructor
     *
     * @param  array|Zend_Config $options
     * @return void
     */
    public function __construct($options = array())
    {
        if (is_object($options)) {
            if (method_exists($options, 'toArray')) {
                $options= $options->toArray();
            } elseif ($options instanceof Traversable) {
                $options = iterator_to_array($options);
            }
        }

        if (empty($options) || !is_array($options)) {
            #require_once 'Zend/Cloud/Infrastructure/Exception.php';
            throw new Zend_Cloud_Infrastructure_Exception('Invalid options provided');
        }

        if (!isset($options[self::AWS_ACCESS_KEY])
            || !isset($options[self::AWS_SECRET_KEY])
        ) {
            #require_once 'Zend/Cloud/Infrastructure/Exception.php';
            throw new Zend_Cloud_Infrastructure_Exception('AWS keys not specified!');
        }

        $this->accessKey    = $options[self::AWS_ACCESS_KEY];
        $this->accessSecret = $options[self::AWS_SECRET_KEY];
        $this->region       = '';

        if (isset($options[self::AWS_REGION])) {
            $this->region= $options[self::AWS_REGION];
        }

        try {
            $this->ec2 = new Zend_Service_Amazon_Ec2_Instance($options[self::AWS_ACCESS_KEY], $options[self::AWS_SECRET_KEY], $this->region);
        } catch (Exception  $e) {
            #require_once 'Zend/Cloud/Infrastructure/Exception.php';
            throw new Zend_Cloud_Infrastructure_Exception('Error on create: ' . $e->getMessage(), $e->getCode(), $e);
        }

        if (isset($options[self::HTTP_ADAPTER])) {
            $this->ec2->getHttpClient()->setAdapter($options[self::HTTP_ADAPTER]);
        }
    }

    /**
     * Convert the attributes of EC2 into attributes of Infrastructure
     *
     * @param  array $attr
     * @return array|boolean
     */
    private function convertAttributes($attr)
    {
        $result = array();
        if (!empty($attr) && is_array($attr)) {
            $result[Zend_Cloud_Infrastructure_Instance::INSTANCE_ID]         = $attr['instanceId'];
            $result[Zend_Cloud_Infrastructure_Instance::INSTANCE_STATUS]     = $this->mapStatus[$attr['instanceState']['name']];
            $result[Zend_Cloud_Infrastructure_Instance::INSTANCE_IMAGEID]    = $attr['imageId'];
            $result[Zend_Cloud_Infrastructure_Instance::INSTANCE_ZONE]       = $attr['availabilityZone'];
            $result[Zend_Cloud_Infrastructure_Instance::INSTANCE_LAUNCHTIME] = $attr['launchTime'];

            switch ($attr['instanceType']) {
                case Zend_Service_Amazon_Ec2_Instance::MICRO:
                    $result[Zend_Cloud_Infrastructure_Instance::INSTANCE_CPU]     = '1 virtual core';
                    $result[Zend_Cloud_Infrastructure_Instance::INSTANCE_RAM]     = '613MB';
                    $result[Zend_Cloud_Infrastructure_Instance::INSTANCE_STORAGE] = '0GB';
                    break;
                case Zend_Service_Amazon_Ec2_Instance::SMALL:
                    $result[Zend_Cloud_Infrastructure_Instance::INSTANCE_CPU]     = '1 virtual core';
                    $result[Zend_Cloud_Infrastructure_Instance::INSTANCE_RAM]     = '1.7GB';
                    $result[Zend_Cloud_Infrastructure_Instance::INSTANCE_STORAGE] = '160GB';
                    break;
                case Zend_Service_Amazon_Ec2_Instance::LARGE:
                    $result[Zend_Cloud_Infrastructure_Instance::INSTANCE_CPU]     = '2 virtual core';
                    $result[Zend_Cloud_Infrastructure_Instance::INSTANCE_RAM]     = '7.5GB';
                    $result[Zend_Cloud_Infrastructure_Instance::INSTANCE_STORAGE] = '850GB';
                    break;
                case Zend_Service_Amazon_Ec2_Instance::XLARGE:
                    $result[Zend_Cloud_Infrastructure_Instance::INSTANCE_CPU]     = '4 virtual core';
                    $result[Zend_Cloud_Infrastructure_Instance::INSTANCE_RAM]     = '15GB';
                    $result[Zend_Cloud_Infrastructure_Instance::INSTANCE_STORAGE] = '1690GB';
                    break;
                case Zend_Service_Amazon_Ec2_Instance::HCPU_MEDIUM:
                    $result[Zend_Cloud_Infrastructure_Instance::INSTANCE_CPU]     = '2 virtual core';
                    $result[Zend_Cloud_Infrastructure_Instance::INSTANCE_RAM]     = '1.7GB';
                    $result[Zend_Cloud_Infrastructure_Instance::INSTANCE_STORAGE] = '350GB';
                    break;
                case Zend_Service_Amazon_Ec2_Instance::HCPU_XLARGE:
                    $result[Zend_Cloud_Infrastructure_Instance::INSTANCE_CPU]     = '8 virtual core';
                    $result[Zend_Cloud_Infrastructure_Instance::INSTANCE_RAM]     = '7GB';
                    $result[Zend_Cloud_Infrastructure_Instance::INSTANCE_STORAGE] = '1690GB';
                    break;
            }
        }
        return $result;
    }

    /**
     * Return a list of the available instancies
     *
     * @return Zend_Cloud_Infrastructure_InstanceList
     */
    public function listInstances()
    {
        $this->adapterResult = $this->ec2->describe();

        $result = array();
        foreach ($this->adapterResult['instances'] as $instance) {
            $result[]= $this->convertAttributes($instance);
        }
        return new Zend_Cloud_Infrastructure_InstanceList($this, $result);
    }

    /**
     * Return the status of an instance
     *
     * @param  string
     * @return string|boolean
     */
    public function statusInstance($id)
    {
        $this->adapterResult = $this->ec2->describe($id);
        if (empty($this->adapterResult['instances'])) {
            return false;
        }
        $result = $this->adapterResult['instances'][0];
        return $this->mapStatus[$result['instanceState']['name']];
    }

    /**
     * Return the public DNS name of the instance
     *
     * @param  string $id
     * @return string|boolean
     */
    public function publicDnsInstance($id)
    {
        $this->adapterResult = $this->ec2->describe($id);
        if (empty($this->adapterResult['instances'])) {
            return false;
        }
        $result = $this->adapterResult['instances'][0];
        return $result['dnsName'];
    }

    /**
     * Reboot an instance
     *
     * @param string $id
     * @return boolean
     */
    public function rebootInstance($id)
    {
        $this->adapterResult= $this->ec2->reboot($id);
        return $this->adapterResult;
    }

    /**
     * Create a new instance
     *
     * @param string $name
     * @param array $options
     * @return Instance|boolean
     */
    public function createInstance($name, $options)
    {
        // @todo instance's name management?
        $this->adapterResult = $this->ec2->run($options);
        if (empty($this->adapterResult['instances'])) {
            return false;
        }
        $this->error= false;
        return new Zend_Cloud_Infrastructure_Instance($this, $this->convertAttributes($this->adapterResult['instances'][0]));
    }

    /**
     * Stop an instance
     *
     * @param  string $id
     * @return boolean
     */
    public function stopInstance($id)
    {
        #require_once 'Zend/Cloud/Infrastructure/Exception.php';
        throw new Zend_Cloud_Infrastructure_Exception('The stopInstance method is not implemented in the adapter');
    }

    /**
     * Start an instance
     *
     * @param  string $id
     * @return boolean
     */
    public function startInstance($id)
    {
        #require_once 'Zend/Cloud/Infrastructure/Exception.php';
        throw new Zend_Cloud_Infrastructure_Exception('The startInstance method is not implemented in the adapter');
    }

    /**
     * Destroy an instance
     *
     * @param  string $id
     * @return boolean
     */
    public function destroyInstance($id)
    {
        $this->adapterResult = $this->ec2->terminate($id);
        return (!empty($this->adapterResult));
    }

    /**
     * Return a list of all the available instance images
     *
     * @return ImageList
     */
    public function imagesInstance()
    {
        if (!isset($this->ec2Image)) {
            $this->ec2Image = new Zend_Service_Amazon_Ec2_Image($this->accessKey, $this->accessSecret, $this->region);
        }

        $this->adapterResult = $this->ec2Image->describe();

        $images = array();

        foreach ($this->adapterResult as $result) {
            switch (strtolower($result['platform'])) {
                case 'windows' :
                    $platform = Zend_Cloud_Infrastructure_Image::IMAGE_WINDOWS;
                    break;
                default:
                    $platform = Zend_Cloud_Infrastructure_Image::IMAGE_LINUX;
                    break;
            }

            $images[]= array (
                Zend_Cloud_Infrastructure_Image::IMAGE_ID           => $result['imageId'],
                Zend_Cloud_Infrastructure_Image::IMAGE_NAME         => '',
                Zend_Cloud_Infrastructure_Image::IMAGE_DESCRIPTION  => $result['imageLocation'],
                Zend_Cloud_Infrastructure_Image::IMAGE_OWNERID      => $result['imageOwnerId'],
                Zend_Cloud_Infrastructure_Image::IMAGE_ARCHITECTURE => $result['architecture'],
                Zend_Cloud_Infrastructure_Image::IMAGE_PLATFORM     => $platform,
            );
        }
        return new Zend_Cloud_Infrastructure_ImageList($images,$this->ec2Image);
    }

    /**
     * Return all the available zones
     *
     * @return array
     */
    public function zonesInstance()
    {
        if (!isset($this->ec2Zone)) {
            $this->ec2Zone = new Zend_Service_Amazon_Ec2_AvailabilityZones($this->accessKey,$this->accessSecret,$this->region);
        }
        $this->adapterResult = $this->ec2Zone->describe();

        $zones = array();
        foreach ($this->adapterResult as $zone) {
            if (strtolower($zone['zoneState']) === 'available') {
                $zones[] = array (
                    Zend_Cloud_Infrastructure_Instance::INSTANCE_ZONE => $zone['zoneName'],
                );
            }
        }
        return $zones;
    }

    /**
     * Return the system information about the $metric of an instance
     *
     * @param  string $id
     * @param  string $metric
     * @param  null|array $options
     * @return array
     */
    public function monitorInstance($id, $metric, $options = null)
    {
        if (empty($id) || empty($metric)) {
            return false;
        }

        if (!in_array($metric,$this->validMetrics)) {
            #require_once 'Zend/Cloud/Infrastructure/Exception.php';
            throw new Zend_Cloud_Infrastructure_Exception(sprintf(
                'The metric "%s" is not valid',
                $metric
            ));
        }

        if (!empty($options) && !is_array($options)) {
            #require_once 'Zend/Cloud/Infrastructure/Exception.php';
            throw new Zend_Cloud_Infrastructure_Exception('The options must be an array');
        }

        if (!empty($options)
            && (empty($options[Zend_Cloud_Infrastructure_Instance::MONITOR_START_TIME])
                || empty($options[Zend_Cloud_Infrastructure_Instance::MONITOR_END_TIME]))
        ) {
            #require_once 'Zend/Cloud/Infrastructure/Exception.php';
            throw new Zend_Cloud_Infrastructure_Exception(sprintf(
                'The options array must contain: "%s" and "%s"',
                $options[Zend_Cloud_Infrastructure_Instance::MONITOR_START_TIME],
                $options[Zend_Cloud_Infrastructure_Instance::MONITOR_END_TIME]
            ));
        }

        if (!isset($this->ec2Monitor)) {
            $this->ec2Monitor = new Zend_Service_Amazon_Ec2_CloudWatch($this->accessKey, $this->accessSecret, $this->region);
        }

        $param = array(
            'MeasureName' => $this->mapMetrics[$metric],
            'Statistics'  => array('Average'),
            'Dimensions'  => array('InstanceId' => $id),
        );

        if (!empty($options)) {
            $param['StartTime'] = $options[Zend_Cloud_Infrastructure_Instance::MONITOR_START_TIME];
            $param['EndTime']   = $options[Zend_Cloud_Infrastructure_Instance::MONITOR_END_TIME];
        }

        $this->adapterResult = $this->ec2Monitor->getMetricStatistics($param);

        $monitor             = array();
        $num                 = 0;
        $average             = 0;

        if (!empty($this->adapterResult['datapoints'])) {
            foreach ($this->adapterResult['datapoints'] as $result) {
                $monitor['series'][] = array (
                    'timestamp' => $result['Timestamp'],
                    'value'     => $result['Average'],
                );
                $average += $result['Average'];
                $num++;
            }
        }

        if ($num > 0) {
            $monitor['average'] = $average / $num;
        }

        return $monitor;
    }

    /**
     * Get the adapter
     *
     * @return Zend_Service_Amazon_Ec2_Instance
     */
    public function getAdapter()
    {
        return $this->ec2;
    }

    /**
     * Get last HTTP request
     *
     * @return string
     */
    public function getLastHttpRequest()
    {
        return $this->ec2->getHttpClient()->getLastRequest();
    }

    /**
     * Get the last HTTP response
     *
     * @return Zend_Http_Response
     */
    public function getLastHttpResponse()
    {
        return $this->ec2->getHttpClient()->getLastResponse();
    }
}
