<?php

namespace Liip\CustomerHierarchy\Model;

abstract class RolePermission implements \Liip\CustomerHierarchy\Model\Role\PermissionInterface
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $code;

    /**
     * @param string $type
     * @param string $code
     */
    public function __construct($type, $code)
    {
        $this->type  = $type;
        $this->code  = $code;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    abstract public function getLabel();

    /**
     * @return bool
     */
    abstract public function isAllowed();
}
