<?php
/**
 * Default session storage
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Session;

/**
 * Class \Magento\Framework\Session\Storage
 *
 * @since 2.0.0
 */
class Storage extends \Magento\Framework\DataObject implements StorageInterface
{
    /**
     * Namespace of storage
     *
     * @var string
     * @since 2.0.0
     */
    protected $namespace;

    /**
     * Constructor
     *
     * @param string $namespace
     * @param array $data
     * @since 2.0.0
     */
    public function __construct($namespace = 'default', array $data = [])
    {
        $this->namespace = $namespace;
        parent::__construct($data);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function init(array $data)
    {
        $namespace = $this->getNamespace();
        if (isset($data[$namespace])) {
            $this->setData($data[$namespace]);
        }
        $_SESSION[$namespace] = & $this->_data;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Additional get data with clear mode
     *
     * @param string $key
     * @param bool $clear
     * @return mixed
     * @since 2.0.0
     */
    public function getData($key = '', $clear = false)
    {
        $data = parent::getData($key);
        if ($clear && isset($this->_data[$key])) {
            unset($this->_data[$key]);
        }
        return $data;
    }
}
