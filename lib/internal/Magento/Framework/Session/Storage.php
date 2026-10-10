<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Session;

use Magento\Framework\ObjectManager\ResetAfterRequestInterface;

/**
 * Default session storage
 */
class Storage extends \Magento\Framework\DataObject implements StorageInterface, ResetAfterRequestInterface
{
    /**
     * Storage instances attached to the active PHP session.
     *
     * @var Storage[]
     */
    private static array $instances = [];

    /**
     * Namespace of storage
     *
     * @var string
     */
    protected $namespace;

    /**
     * Whether this storage has been attached to the active session.
     */
    private bool $initialized = false;

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->_data = [];
        unset(self::$instances[spl_object_id($this)]);
    }

    /**
     * Constructor
     *
     * @param string $namespace
     * @param array $data
     */
    public function __construct($namespace = 'default', array $data = [])
    {
        $this->namespace = $namespace;
        parent::__construct($data);
        self::$instances[spl_object_id($this)] = $this;
    }

    /**
     * Rebinds every initialized storage namespace after a session is reopened.
     *
     * @param array $data
     * @return void
     */
    public static function refresh(array $data): void
    {
        foreach (self::$instances as $storage) {
            if ($storage->initialized) {
                $storage->init($data);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function init(array $data)
    {
        $this->initialized = true;
        $namespace = $this->getNamespace() ?? '';
        $this->setData($data[$namespace] ?? []);
        $_SESSION[$namespace] = & $this->_data;
        return $this;
    }

    /**
     * @inheritdoc
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
