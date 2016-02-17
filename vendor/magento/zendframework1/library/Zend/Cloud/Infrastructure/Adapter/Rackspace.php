<?php
/**
 * @category   Zend
 * @package    Zend_Cloud_Infrastructure
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

#require_once 'Zend/Service/Rackspace/Servers.php';
#require_once 'Zend/Cloud/Infrastructure/Instance.php';
#require_once 'Zend/Cloud/Infrastructure/InstanceList.php';
#require_once 'Zend/Cloud/Infrastructure/Image.php';
#require_once 'Zend/Cloud/Infrastructure/ImageList.php';
#require_once 'Zend/Cloud/Infrastructure/Adapter/AbstractAdapter.php';

/**
 * Rackspace servers adapter for infrastructure service
 *
 * @package    Zend_Cloud_Infrastructure
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cloud_Infrastructure_Adapter_Rackspace extends Zend_Cloud_Infrastructure_Adapter_AbstractAdapter
{
    /**
     * RACKSPACE constants
     */
    const RACKSPACE_USER      = 'rackspace_user';
    const RACKSPACE_KEY       = 'rackspace_key';
    const RACKSPACE_REGION    = 'rackspace_region';
    const RACKSPACE_ZONE_USA  = 'USA';
    const RACKSPACE_ZONE_UK   = 'UK';
    const MONITOR_CPU_SAMPLES = 3;
    /**
     * Rackspace Servers Instance
     *
     * @var Zend_Service_Rackspace_Servers
     */
    protected $rackspace;
    /**
     * Rackspace access user
     *
     * @var string
     */
    protected $accessUser;

    /**
     * Rackspace access key
     *
     * @var string
     */
    protected $accessKey;
    /**
     * Rackspace Region
     *
     * @var string
     */
    protected $region;
    /**
     * Flavors
     *
     * @var array
     */
    protected $flavors;
    /**
     * Map array between Rackspace and Infrastructure status
     *
     * @var array
     */
    protected $mapStatus = array (
        'ACTIVE'             => Zend_Cloud_Infrastructure_Instance::STATUS_RUNNING,
        'SUSPENDED'          => Zend_Cloud_Infrastructure_Instance::STATUS_STOPPED,
        'BUILD'              => Zend_Cloud_Infrastructure_Instance::STATUS_REBUILD,
        'REBUILD'            => Zend_Cloud_Infrastructure_Instance::STATUS_REBUILD,
        'QUEUE_RESIZE'       => Zend_Cloud_Infrastructure_Instance::STATUS_PENDING,
        'PREP_RESIZE'        => Zend_Cloud_Infrastructure_Instance::STATUS_PENDING,
        'RESIZE'             => Zend_Cloud_Infrastructure_Instance::STATUS_REBUILD,
        'VERIFY_RESIZE'      => Zend_Cloud_Infrastructure_Instance::STATUS_REBUILD,
        'PASSWORD'           => Zend_Cloud_Infrastructure_Instance::STATUS_PENDING,
        'RESCUE'             => Zend_Cloud_Infrastructure_Instance::STATUS_PENDING,
        'REBOOT'             => Zend_Cloud_Infrastructure_Instance::STATUS_REBOOTING,
        'HARD_REBOOT'        => Zend_Cloud_Infrastructure_Instance::STATUS_REBOOTING,
        'SHARE_IP'           => Zend_Cloud_Infrastructure_Instance::STATUS_PENDING,
        'SHARE_IP_NO_CONFIG' => Zend_Cloud_Infrastructure_Instance::STATUS_PENDING,
        'DELETE_IP'          => Zend_Cloud_Infrastructure_Instance::STATUS_PENDING,
        'UNKNOWN'            => Zend_Cloud_Infrastructure_Instance::STATUS_PENDING
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

        if (!isset($options[self::RACKSPACE_USER])) {
            #require_once 'Zend/Cloud/Infrastructure/Exception.php';
            throw new Zend_Cloud_Infrastructure_Exception('Rackspace access user not specified!');
        }

        if (!isset($options[self::RACKSPACE_KEY])) {
            #require_once 'Zend/Cloud/Infrastructure/Exception.php';
            throw new Zend_Cloud_Infrastructure_Exception('Rackspace access key not specified!');
        }

        $this->accessUser = $options[self::RACKSPACE_USER];
        $this->accessKey  = $options[self::RACKSPACE_KEY];

        if (isset($options[self::RACKSPACE_REGION])) {
            switch ($options[self::RACKSPACE_REGION]) {
                case self::RACKSPACE_ZONE_UK:
                    $this->region= Zend_Service_Rackspace_Servers::UK_AUTH_URL;
                    break;
                case self::RACKSPACE_ZONE_USA:
                    $this->region = Zend_Service_Rackspace_Servers::US_AUTH_URL;
                    break;
                default:
                    #require_once 'Zend/Cloud/Infrastructure/Exception.php';
                    throw new Zend_Cloud_Infrastructure_Exception('The region is not valid');
            }
        } else {
            $this->region = Zend_Service_Rackspace_Servers::US_AUTH_URL;
        }

        try {
            $this->rackspace = new Zend_Service_Rackspace_Servers($this->accessUser,$this->accessKey, $this->region);
        } catch (Exception  $e) {
            #require_once 'Zend/Cloud/Infrastructure/Exception.php';
            throw new Zend_Cloud_Infrastructure_Exception('Error on create: ' . $e->getMessage(), $e->getCode(), $e);
        }

        if (isset($options[self::HTTP_ADAPTER])) {
            $this->rackspace->getHttpClient()->setAdapter($options[self::HTTP_ADAPTER]);
        }

    }
    /**
     * Convert the attributes of Rackspace server into attributes of Infrastructure
     *
     * @param  array $attr
     * @return array|boolean
     */
    protected function convertAttributes($attr)
    {
        $result = array();
        if (!empty($attr) && is_array($attr)) {
            $result[Zend_Cloud_Infrastructure_Instance::INSTANCE_ID]      = $attr['id'];
            $result[Zend_Cloud_Infrastructure_Instance::INSTANCE_NAME]    = $attr['name'];
            $result[Zend_Cloud_Infrastructure_Instance::INSTANCE_STATUS]  = $this->mapStatus[$attr['status']];
            $result[Zend_Cloud_Infrastructure_Instance::INSTANCE_IMAGEID] = $attr['imageId'];
            if ($this->region==Zend_Service_Rackspace_Servers::US_AUTH_URL) {
                $result[Zend_Cloud_Infrastructure_Instance::INSTANCE_ZONE] = self::RACKSPACE_ZONE_USA;
            } else {
                $result[Zend_Cloud_Infrastructure_Instance::INSTANCE_ZONE] = self::RACKSPACE_ZONE_UK;
            }
            $result[Zend_Cloud_Infrastructure_Instance::INSTANCE_RAM]     = $this->flavors[$attr['flavorId']]['ram'];
            $result[Zend_Cloud_Infrastructure_Instance::INSTANCE_STORAGE] = $this->flavors[$attr['flavorId']]['disk'];
        }
        return $result;
    }
    /**
     * Return a list of the available instancies
     *
     * @return InstanceList|boolean
     */
    public function listInstances()
    {
        $this->adapterResult = $this->rackspace->listServers(true);
        if ($this->adapterResult===false) {
            return false;
        }
        $array= $this->adapterResult->toArray();
        $result = array();
        foreach ($array as $instance) {
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
        $this->adapterResult = $this->rackspace->getServer($id);
        if ($this->adapterResult===false) {
            return false;
        }
        $array= $this->adapterResult->toArray();
        return $this->mapStatus[$array['status']];
    }
    /**
     * Return the public DNS name/Ip address of the instance
     *
     * @param  string $id
     * @return string|boolean
     */
    public function publicDnsInstance($id)
    {
        $this->adapterResult = $this->rackspace->getServerPublicIp($id);
        if (empty($this->adapterResult)) {
            return false;
        }
        return $this->adapterResult[0];
    }
    /**
     * Reboot an instance
     *
     * @param string $id
     * @return boolean
     */
    public function rebootInstance($id)
    {
        return $this->rackspace->rebootServer($id,true);
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
        if (empty($name)) {
            #require_once 'Zend/Cloud/Infrastructure/Exception.php';
            throw new Zend_Cloud_Infrastructure_Exception('You must specify the name of the instance');
        }
        if (empty($options) || !is_array($options)) {
            #require_once 'Zend/Cloud/Infrastructure/Exception.php';
            throw new Zend_Cloud_Infrastructure_Exception('The options must be an array');
        }
        // @todo create an generic abstract definition for an instance?
        $metadata= array();
        if (isset($options['metadata'])) {
            $metadata= $options['metadata'];
            unset($options['metadata']);
        }
        $files= array();
        if (isset($options['files'])) {
            $files= $options['files'];
            unset($options['files']);
        }
        $options['name']= $name;
        $this->adapterResult = $this->rackspace->createServer($options,$metadata,$files);
        if ($this->adapterResult===false) {
            return false;
        }
        return new Zend_Cloud_Infrastructure_Instance($this, $this->convertAttributes($this->adapterResult->toArray()));
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
        $this->adapterResult= $this->rackspace->deleteServer($id);
        return $this->adapterResult;
    }
    /**
     * Return a list of all the available instance images
     *
     * @return ImageList|boolean
     */
    public function imagesInstance()
    {
        $this->adapterResult = $this->rackspace->listImages(true);
        if ($this->adapterResult===false) {
            return false;
        }

        $images= $this->adapterResult->toArray();
        $result= array();

        foreach ($images as $image) {
            if (strtolower($image['status'])==='active') {
                if (strpos($image['name'],'Windows')!==false) {
                    $platform = Zend_Cloud_Infrastructure_Image::IMAGE_WINDOWS;
                } else {
                    $platform = Zend_Cloud_Infrastructure_Image::IMAGE_LINUX;
                }
                if (strpos($image['name'],'x64')!==false) {
                    $arch = Zend_Cloud_Infrastructure_Image::ARCH_64BIT;
                } else {
                    $arch = Zend_Cloud_Infrastructure_Image::ARCH_32BIT;
                }
                $result[]= array (
                    Zend_Cloud_Infrastructure_Image::IMAGE_ID           => $image['id'],
                    Zend_Cloud_Infrastructure_Image::IMAGE_NAME         => $image['name'],
                    Zend_Cloud_Infrastructure_Image::IMAGE_DESCRIPTION  => $image['name'],
                    Zend_Cloud_Infrastructure_Image::IMAGE_ARCHITECTURE => $arch,
                    Zend_Cloud_Infrastructure_Image::IMAGE_PLATFORM     => $platform,
                );
            }
        }
        return new Zend_Cloud_Infrastructure_ImageList($result,$this->adapterResult);
    }
    /**
     * Return all the available zones
     *
     * @return array
     */
    public function zonesInstance()
    {
        return array(self::RACKSPACE_ZONE_USA,self::RACKSPACE_ZONE_UK);
    }
    /**
     * Return the system information about the $metric of an instance
     * NOTE: it works only for Linux servers
     *
     * @param  string $id
     * @param  string $metric
     * @param  null|array $options
     * @return array|boolean
     */
    public function monitorInstance($id, $metric, $options = null)
    {
        if (!function_exists("ssh2_connect")) {
            #require_once 'Zend/Cloud/Infrastructure/Exception.php';
            throw new Zend_Cloud_Infrastructure_Exception('Monitor requires the PHP "SSH" extension (ext/ssh2)');
        }
        if (empty($id)) {
            #require_once 'Zend/Cloud/Infrastructure/Exception.php';
            throw new Zend_Cloud_Infrastructure_Exception('You must specify the id of the instance to monitor');
        }
        if (empty($metric)) {
            #require_once 'Zend/Cloud/Infrastructure/Exception.php';
            throw new Zend_Cloud_Infrastructure_Exception('You must specify the metric to monitor');
        }
        if (!in_array($metric,$this->validMetrics)) {
            #require_once 'Zend/Cloud/Infrastructure/Exception.php';
            throw new Zend_Cloud_Infrastructure_Exception(sprintf('The metric "%s" is not valid', $metric));
        }
        if (!empty($options) && !is_array($options)) {
            #require_once 'Zend/Cloud/Infrastructure/Exception.php';
            throw new Zend_Cloud_Infrastructure_Exception('The options must be an array');
        }

        switch ($metric) {
            case Zend_Cloud_Infrastructure_Instance::MONITOR_CPU:
                $cmd= 'top -b -n '.self::MONITOR_CPU_SAMPLES.' | grep \'Cpu\'';
                break;
            case Zend_Cloud_Infrastructure_Instance::MONITOR_RAM:
                $cmd= 'top -b -n 1 | grep \'Mem\'';
                break;
            case Zend_Cloud_Infrastructure_Instance::MONITOR_DISK:
                $cmd= 'df --total | grep total';
                break;
        }
        if (empty($cmd)) {
            #require_once 'Zend/Cloud/Infrastructure/Exception.php';
            throw new Zend_Cloud_Infrastructure_Exception('The metric specified is not supported by the adapter');
        }

        $params= array(
            Zend_Cloud_Infrastructure_Instance::SSH_USERNAME => $options['username'],
            Zend_Cloud_Infrastructure_Instance::SSH_PASSWORD => $options['password']
        );
        $exec_time= time();
        $result= $this->deployInstance($id,$params,$cmd);

        if (empty($result)) {
            return false;
        }

        $monitor = array();
        $num     = 0;
        $average = 0;

        $outputs= explode("\n",$result);
        foreach ($outputs as $output) {
            if (!empty($output)) {
                switch ($metric) {
                    case Zend_Cloud_Infrastructure_Instance::MONITOR_CPU:
                        if (preg_match('/(\d+\.\d)%us/', $output,$match)) {
                            $usage = (float) $match[1];
                        }
                        break;
                    case Zend_Cloud_Infrastructure_Instance::MONITOR_RAM:
                        if (preg_match('/(\d+)k total/', $output,$match)) {
                            $total = (integer) $match[1];
                        }
                        if (preg_match('/(\d+)k used/', $output,$match)) {
                            $used = (integer) $match[1];
                        }
                        if ($total>0) {
                            $usage= (float) $used/$total;
                        }
                        break;
                    case Zend_Cloud_Infrastructure_Instance::MONITOR_DISK:
                        if (preg_match('/(\d+)%/', $output,$match)) {
                            $usage = (float) $match[1];
                        }
                        break;
                }

                $monitor['series'][] = array (
                    'timestamp' => $exec_time,
                    'value'     => number_format($usage,2).'%'
                );

                $average += $usage;
                $exec_time+= 60; // seconds
                $num++;
            }
        }

        if ($num>0) {
            $monitor['average'] = number_format($average/$num,2).'%';
        }
        return $monitor;
    }
    /**
     * Get the adapter
     *
     * @return Zend_Service_Rackspace_Servers
     */
    public function getAdapter()
    {
        return $this->rackspace;
    }
    /**
     * Get last HTTP request
     *
     * @return string
     */
    public function getLastHttpRequest()
    {
        return $this->rackspace->getHttpClient()->getLastRequest();
    }
    /**
     * Get the last HTTP response
     *
     * @return Zend_Http_Response
     */
    public function getLastHttpResponse()
    {
        return $this->rackspace->getHttpClient()->getLastResponse();
    }
}
