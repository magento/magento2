<?php

namespace Liip\CustomerHierarchy\Api;

/**
 * @api
 */
interface PermissionManagerInterface
{
    /**
     * @return \Liip\CustomerHierarchy\Api\Data\PermissionInterface[]
     */
    public function getList();
}
