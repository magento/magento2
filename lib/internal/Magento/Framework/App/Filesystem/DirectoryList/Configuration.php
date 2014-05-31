<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\App\Filesystem\DirectoryList;

use Magento\Framework\App\Filesystem;
use Magento\Framework\Filesystem\DirectoryList;

/**
 * Class Configuration
 */
class Configuration
{
    /**
     * Path to filesystem directory configuration
     *
     * @var string
     */
    const XML_FILESYSTEM_DIRECTORY_PATH = 'system/filesystem/directory';

    /**
     * Declaration wrapper configuration
     */
    const XML_FILESYSTEM_WRAPPER_PATH = 'system/filesystem/protocol';

    /**
     * Filesystem Directory configuration
     *
     * @var array
     */
    protected $directories;

    /**
     * Filesystem protocols configuration
     *
     * @var array
     */
    protected $protocols;

    /**
     * Store directory configuration
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     */
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $config)
    {
        $this->directories = $config->getValue(self::XML_FILESYSTEM_DIRECTORY_PATH) ?: array();
        $this->protocols = $config->getValue(self::XML_FILESYSTEM_WRAPPER_PATH) ?: array();
    }

    /**
     * Add directories from configuration to Filesystem
     *
     * @param DirectoryList $directoryList
     * @return void
     */
    public function configure(DirectoryList $directoryList)
    {
        foreach ($this->directories as $code => $directoryConfiguration) {
            if ($directoryList->isConfigured($code)) {
                $existingDirectoryConfiguration = $directoryList->getConfig($code);
                $directoryConfiguration = array_merge($directoryConfiguration, $existingDirectoryConfiguration);
            }
            $directoryList->setDirectory($code, $directoryConfiguration);
        }

        foreach ($this->protocols as $code => $protocolConfiguration) {
            $directoryList->addProtocol($code, $protocolConfiguration);
        }
    }
}
