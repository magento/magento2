<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Json\Server;

use Zend\Json\Server\Exception\InvalidArgumentException;
use Zend\Json\Server\Exception\RuntimeException;

class Smd
{
    const ENV_JSONRPC_1 = 'JSON-RPC-1.0';
    const ENV_JSONRPC_2 = 'JSON-RPC-2.0';
    const SMD_VERSION   = '2.0';

    /**
     * Content type
     * @var string
     */
    protected $contentType = 'application/json';

    /**
     * Content type regex
     * @var string
     */
    protected $contentTypeRegex = '#[a-z]+/[a-z][a-z-]+#i';

    /**
     * Service description
     * @var string
     */
    protected $description;

    /**
     * Generate Dojo-compatible SMD
     * @var bool
     */
    protected $dojoCompatible = false;

    /**
     * Current envelope
     * @var string
     */
    protected $envelope = self::ENV_JSONRPC_1;

    /**
     * Allowed envelope types
     * @var array
     */
    protected $envelopeTypes = array(
        self::ENV_JSONRPC_1,
        self::ENV_JSONRPC_2,
    );

    /**
     * Service id
     * @var string
     */
    protected $id;

    /**
     * Services offered
     * @var array
     */
    protected $services = array();

    /**
     * Service target
     * @var string
     */
    protected $target;

    /**
     * Global transport
     * @var string
     */
    protected $transport = 'POST';

    /**
     * Allowed transport types
     * @var array
     */
    protected $transportTypes = array('POST');

    /**
     * Set object state via options
     *
     * @param  array $options
     * @return Smd
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    /**
     * Set transport
     *
     * @param  string $transport
     * @throws Exception\InvalidArgumentException
     * @return \Zend\Json\Server\Smd
     */
    public function setTransport($transport)
    {
        if (!in_array($transport, $this->transportTypes)) {
            throw new InvalidArgumentException("Invalid transport '{$transport}' specified");
        }
        $this->transport = $transport;
        return $this;
    }

    /**
     * Get transport
     *
     * @return string
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * Set envelope
     *
     * @param  string $envelopeType
     * @throws Exception\InvalidArgumentException
     * @return Smd
     */
    public function setEnvelope($envelopeType)
    {
        if (!in_array($envelopeType, $this->envelopeTypes)) {
            throw new InvalidArgumentException("Invalid envelope type '{$envelopeType}'");
        }
        $this->envelope = $envelopeType;
        return $this;
    }

    /**
     * Retrieve envelope
     *
     * @return string
     */
    public function getEnvelope()
    {
        return $this->envelope;
    }

    // Content-Type of response; default to application/json
    /**
     * Set content type
     *
     * @param  string $type
     * @throws Exception\InvalidArgumentException
     * @return \Zend\Json\Server\Smd
     */
    public function setContentType($type)
    {
        if (!preg_match($this->contentTypeRegex, $type)) {
            throw new InvalidArgumentException("Invalid content type '{$type}' specified");
        }
        $this->contentType = $type;
        return $this;
    }

    /**
     * Retrieve content type
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Set service target
     *
     * @param  string $target
     * @return Smd
     */
    public function setTarget($target)
    {
        $this->target = (string) $target;
        return $this;
    }

    /**
     * Retrieve service target
     *
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Set service ID
     *
     * @param  string $id
     * @return Smd
     */
    public function setId($id)
    {
        $this->id = (string) $id;
        return $this->id;
    }

    /**
     * Get service id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set service description
     *
     * @param  string $description
     * @return Smd
     */
    public function setDescription($description)
    {
        $this->description = (string) $description;
        return $this->description;
    }

    /**
     * Get service description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Indicate whether or not to generate Dojo-compatible SMD
     *
     * @param  bool $flag
     * @return Smd
     */
    public function setDojoCompatible($flag)
    {
        $this->dojoCompatible = (bool) $flag;
        return $this;
    }

    /**
     * Is this a Dojo compatible SMD?
     *
     * @return bool
     */
    public function isDojoCompatible()
    {
        return $this->dojoCompatible;
    }

    /**
     * Add Service
     *
     * @param Smd\Service|array $service
     * @throws Exception\RuntimeException
     * @throws Exception\InvalidArgumentException
     * @return Smd
     */
    public function addService($service)
    {
        if ($service instanceof Smd\Service) {
            $name = $service->getName();
        } elseif (is_array($service)) {
            $service = new Smd\Service($service);
            $name = $service->getName();
        } else {
            throw new InvalidArgumentException('Invalid service passed to addService()');
        }

        if (array_key_exists($name, $this->services)) {
            throw new RuntimeException('Attempt to register a service already registered detected');
        }
        $this->services[$name] = $service;
        return $this;
    }

    /**
     * Add many services
     *
     * @param  array $services
     * @return Smd
     */
    public function addServices(array $services)
    {
        foreach ($services as $service) {
            $this->addService($service);
        }
        return $this;
    }

    /**
     * Overwrite existing services with new ones
     *
     * @param  array $services
     * @return Smd
     */
    public function setServices(array $services)
    {
        $this->services = array();
        return $this->addServices($services);
    }

    /**
     * Get service object
     *
     * @param  string $name
     * @return bool|Smd\Service
     */
    public function getService($name)
    {
        if (array_key_exists($name, $this->services)) {
            return $this->services[$name];
        }
        return false;
    }

    /**
     * Return services
     *
     * @return array
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * Remove service
     *
     * @param  string $name
     * @return bool
     */
    public function removeService($name)
    {
        if (array_key_exists($name, $this->services)) {
            unset($this->services[$name]);
            return true;
        }
        return false;
    }

    /**
     * Cast to array
     *
     * @return array
     */
    public function toArray()
    {
        if ($this->isDojoCompatible()) {
            return $this->toDojoArray();
        }

        $description = $this->getDescription();
        $transport   = $this->getTransport();
        $envelope    = $this->getEnvelope();
        $contentType = $this->getContentType();
        $SMDVersion  = static::SMD_VERSION;
        $service = compact('transport', 'envelope', 'contentType', 'SMDVersion', 'description');

        if (null !== ($target = $this->getTarget())) {
            $service['target']     = $target;
        }
        if (null !== ($id = $this->getId())) {
            $service['id'] = $id;
        }

        $services = $this->getServices();
        if (!empty($services)) {
            $service['services'] = array();
            foreach ($services as $name => $svc) {
                $svc->setEnvelope($envelope);
                $service['services'][$name] = $svc->toArray();
            }
            $service['methods'] = $service['services'];
        }

        return $service;
    }

    /**
     * Export to DOJO-compatible SMD array
     *
     * @return array
     */
    public function toDojoArray()
    {
        $SMDVersion  = '.1';
        $serviceType = 'JSON-RPC';
        $service = compact('SMDVersion', 'serviceType');

        $target = $this->getTarget();

        $services = $this->getServices();
        if (!empty($services)) {
            $service['methods'] = array();
            foreach ($services as $name => $svc) {
                $method = array(
                    'name'       => $name,
                    'serviceURL' => $target,
                );
                $params = array();
                foreach ($svc->getParams() as $param) {
                    $paramName = array_key_exists('name', $param) ? $param['name'] : $param['type'];
                    $params[] = array(
                        'name' => $paramName,
                        'type' => $param['type'],
                    );
                }
                if (!empty($params)) {
                    $method['parameters'] = $params;
                }
                $service['methods'][] = $method;
            }
        }

        return $service;
    }

    /**
     * Cast to JSON
     *
     * @return string
     */
    public function toJson()
    {
        return \Zend\Json\Json::encode($this->toArray());
    }

    /**
     * Cast to string (JSON)
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }
}
