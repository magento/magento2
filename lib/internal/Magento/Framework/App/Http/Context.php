<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\App\Http;

/**
 * Context data for requests
 */
class Context
{
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
        return;
    }

    /**
     * Data getter
     *
     * @param string $name
     * @return mixed|null
     */
    public function getValue($name)
    {
        return isset($this->data[$name])
            ? $this->data[$name]
            : (isset($this->default[$name]) ? $this->default[$name] : null);
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
}
