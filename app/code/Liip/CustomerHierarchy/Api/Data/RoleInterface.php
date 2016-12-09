<?php
/**
 * Created by PhpStorm.
 * User: nrg
 * Date: 07.12.16
 * Time: 15:29
 */

namespace Liip\CustomerHierarchy\Api\Data;

interface RoleInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return mixed
     */
    public function getPermissions();

    /**
     * @return string
     */
    public function getCreatedAt();
}
