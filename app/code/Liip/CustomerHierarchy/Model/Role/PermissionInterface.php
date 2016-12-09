<?php

namespace Liip\CustomerHierarchy\Model\Role;

interface PermissionInterface
{
    /**
     * @return string
     */
    public function getCode();

    /**
     * @return string
     */
    public function getLabel();

    /**
     * @return string
     */
    public function getType();

    /**
     * @return bool
     */
    public function isAllowed();
}
