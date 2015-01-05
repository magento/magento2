<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Ui\Context;

use Magento\Framework\View\Element\UiComponent\ConfigInterface;

/**
 * Class Configuration
 */
class Configuration implements ConfigInterface
{
    /**
     * Configuration data
     *
     * @var array
     */
    protected $configuration = [];

    /**
     * Name of owner
     *
     * @var string
     */
    protected $name;

    /**
     * Name of parent owner
     *
     * @var string
     */
    protected $parentName;

    /**
     * Constructor
     *
     * @param string $name
     * @param  string $parentName
     * @param array $configuration
     */
    public function __construct($name, $parentName, $configuration = [])
    {
        $this->name = $name;
        $this->parentName = $parentName;
        $this->configuration = $configuration;
    }

    /**
     * Get configuration data
     *
     * @param string|null $key
     * @return mixed
     */
    public function getData($key = null)
    {
        if ($key === null) {
            return (array)$this->configuration;
        }
        return isset($this->configuration[$key]) ? $this->configuration[$key] : null;
    }

    /**
     * Add configuration data
     *
     * @param string $key
     * @param mixed $data
     * @return mixed
     */
    public function addData($key, $data)
    {
        if (!isset($this->configuration[$key])) {
            $this->configuration[$key] = $data;
        }
    }

    /**
     * Update configuration data
     *
     * @param string $key
     * @param mixed $data
     * @return mixed
     */
    public function updateData($key, $data)
    {
        $this->configuration[$key] = $data;
    }

    /**
     * Get owner name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get owner parent name
     *
     * @return string
     */
    public function getParentName()
    {
        return $this->parentName;
    }
}
