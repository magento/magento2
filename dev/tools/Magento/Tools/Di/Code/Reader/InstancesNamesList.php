<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Tools\Di\Code\Reader;

interface InstancesNamesList {

    /**
     * Retrieves list of classes for given path
     *
     * @param $path
     *
     * @return array
     */
    public function getList($path);
}