<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Config\Rules;

use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\Module\Dir\Reader as DirReader;

/**
 * Class FileResolver
 */
class FileResolver implements FileResolverInterface
{
    /**
     * Module configuration file reader
     *
     * @var DirReader
     */
    protected $moduleReader;

    /**
     * Constructor
     *
     * @param DirReader $moduleReader
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
     */
    public function get($filename, $scope)
    {
        return $this->moduleReader->getConfigurationFiles($filename);
    }
}
