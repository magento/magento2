<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Model\Rest;

use Magento\Webapi\Model\Config as ModelConfig;

/**
 * Webapi Swagger Specification Model
 *
 * @link https://github.com/swagger-api/swagger-spec/blob/master/versions/2.0.md Swagger specification
 *
 * @method Swagger setHost(string $host)
 * @method Swagger setDefinitions(array $definitions)
 * @method Swagger setSchemes(array $schemes)
 */
class Swagger extends \Magento\Framework\DataObject
{
    /**
     * Swagger specification version
     */
    const SWAGGER_VERSION = '2.0';

    /**
     * Constructor
     */
    public function __construct()
    {
        $data = [
            'swagger' => self::SWAGGER_VERSION,
            'info' => [
                'version' => '',
                'title' => '',
            ],
        ];
        parent::__construct($data);
    }

    /**
     * Add a path
     *
     * @param string $path
     * @param string $httpOperation
     * @param string[] $pathInfo
     * @return $this
     */
    public function addPath($path, $httpOperation, $pathInfo)
    {
        $this->_data['paths'][$path][$httpOperation] = $pathInfo;
        return $this;
    }

    /**
     * Add a tag
     *
     * @param string $tagInfo
     * @return $this
     */
    public function addTag($tagInfo)
    {
        $this->_data['tags'][] = $tagInfo;
        return $this;
    }

    /**
     * Get JSON encoded REST schema
     *
     * @return string
     */
    public function toSchema()
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_SLASHES);
    }

    /**
     * Add Info section data
     *
     * @param array $info
     * @return $this
     */
    public function setInfo($info)
    {
        if (! is_array($info)) {
            return $this;
        }
        if (isset($info['title'])) {
            $this->_data['info']['title'] = $info['title'];
        }
        if (isset($info['version'])) {
            $this->_data['info']['version'] = $info['version'];
        }
        return $this;
    }

    /**
     * Set base path
     *
     * @param string $basePath
     * @return $this
     */
    public function setBasePath($basePath)
    {
        $this->_data['basePath'] = $basePath;
        return $this;
    }
}
