<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Http;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Context data for requests
 *
 * @api
 */
class Context implements ResetAfterRequestInterface
{
    /**
     * Currency cache context
     */
    public const CONTEXT_CURRENCY = 'current_currency';

    /**
     * Data storage
     *
     * @var array
     */
    protected $data = [];

    /**
     * @var array
     */
    protected $default = [];

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var DeploymentConfig|null
     */
    private ?DeploymentConfig $deploymentConfig = null;

    /**
     * @param array $data
     * @param array $default
     * @param Json|null $serializer
     */
    public function __construct(array $data = [], array $default = [], Json $serializer = null)
    {
        $this->data = $data;
        $this->default = $default;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * Data setter
     *
     * @param string $name
     * @param mixed $value
     * @param mixed $default
     * @return \Magento\Framework\App\Http\Context
     */
    public function setValue($name, $value, $default)
    {
        if ($default !== null) {
            $this->default[$name] = $default;
        }
        $this->data[$name] = $value;
        return $this;
    }

    /**
     * Unset data from vary array
     *
     * @param string $name
     * @return null
     */
    public function unsValue($name)
    {
        unset($this->data[$name]);
        return $this;
    }

    /**
     * Data getter
     *
     * @param string $name
     * @return mixed|null
     */
    public function getValue($name)
    {
        return $this->data[$name] ?? ($this->default[$name] ?? null);
    }

    /**
     * Return all data
     *
     * @return array
     */
    public function getData()
    {
        $data = [];
        foreach ($this->data as $name => $value) {
            if ($value && $value != $this->default[$name]) {
                $data[$name] = $value;
            }
        }
        return $data;
    }

    /**
     * Return vary string to be used as a part of page cache identifier
     *
     * @return string|null
     */
    public function getVaryString()
    {
        $data = $this->getData();
        if (!empty($data)) {
            $salt = (string)$this->getDeploymentConfig()->get(
                ConfigOptionsListConstants::CONFIG_PATH_CRYPT_KEY
            );
            ksort($data);
            return hash('sha256', $this->serializer->serialize($data) . '|' . $salt);
        }
        return null;
    }

    /**
     * Get data and default data in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'data' => $this->data,
            'default' => $this->default
        ];
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->data = [];
        $this->default = [];
    }

    /**
     * Get DeploymentConfig
     *
     * @return DeploymentConfig
     */
    private function getDeploymentConfig() : DeploymentConfig
    {
        if ($this->deploymentConfig === null) {
            $this->deploymentConfig = ObjectManager::getInstance()
                ->get(DeploymentConfig::class);
        }
        return $this->deploymentConfig;
    }
}
