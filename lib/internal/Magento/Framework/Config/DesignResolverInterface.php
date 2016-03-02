<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config;

/**
 * Interface DesignResolverInterface
 * @api
 */
interface DesignResolverInterface extends FileResolverInterface
{
    /**
     * Retrieve parent configs
     *
     * @param string $filename
     * @param string $scope
     * @return array
     */
    public function getParents($filename, $scope);
}
