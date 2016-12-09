<?php

namespace Liip\CustomerHierarchy\Api\Data;

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
}
