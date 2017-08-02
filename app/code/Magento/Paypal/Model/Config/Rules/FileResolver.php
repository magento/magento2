<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Config\Rules;

use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\Module\Dir\Reader as DirReader;

/**
 * Class FileResolver
 * @since 2.0.0
 */
class FileResolver implements FileResolverInterface
{
    /**
     * Module configuration file reader
     *
     * @var DirReader
     * @since 2.0.0
     */
    protected $moduleReader;

    /**
     * Constructor
     *
     * @param DirReader $moduleReader
     * @since 2.0.0
     */
    public function __construct(DirReader $moduleReader)
    {
        $this->moduleReader = $moduleReader;
    }

    /**
     * Retrieve the list of configuration files with given name that relate to specified scope
     *
     * @param string $filename
     * @param string $scope
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function get($filename, $scope)
    {
        return $this->moduleReader->getConfigurationFiles($filename);
    }
}
