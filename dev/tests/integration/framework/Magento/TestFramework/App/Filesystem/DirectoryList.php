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
namespace Magento\TestFramework\App\Filesystem;

class DirectoryList extends \Magento\Framework\App\Filesystem\DirectoryList
{
    /**
     * Check whether configured directory
     *
     * @param string $code
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isConfigured($code)
    {
        return false;
    }

    /**
     * Add directory configuration
     *
     * @param string $code
     * @param array $directoryConfig
     * @return void
     */
    public function addDirectory($code, array $directoryConfig)
    {
        if (!isset($directoryConfig['path'])) {
            $directoryConfig['path'] = null;
        }
        if (!$this->isAbsolute($directoryConfig['path'])) {
            $directoryConfig['path'] = $this->makeAbsolute($directoryConfig['path']);
        }

        $this->directories[$code] = $directoryConfig;
    }
}
