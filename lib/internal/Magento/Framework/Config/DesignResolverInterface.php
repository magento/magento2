<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Config;

/**
 * Interface DesignResolverInterface
 * @api
 * @since 100.1.0
 */
interface DesignResolverInterface extends FileResolverInterface
{
    /**
     * Retrieve parent configs
     *
     * @param string $filename
     * @param string $scope
     * @return array
     * @since 100.1.0
     */
    public function getParents($filename, $scope);
}
