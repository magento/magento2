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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\App\DeploymentConfig;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Deployment configuration reader
 */
class Reader
{
    /**
     * Default configuration file name
     */
    const DEFAULT_FILE = 'config.php';

    /**
     * Directory list object
     *
     * @var DirectoryList
     */
    private $dirList;

    /**
     * Custom file name
     *
     * @var string
     */
    private $file;

    /**
     * Constructor
     *
     * @param DirectoryList $dirList
     * @param null|string $file
     * @throws \InvalidArgumentException
     */
    public function __construct(DirectoryList $dirList, $file = null)
    {
        $this->dirList = $dirList;
        if (null !== $file) {
            if (!preg_match('/^[a-z\d\.\-]+\.php$/i', $file)) {
                throw new \InvalidArgumentException("Invalid file name: {$file}");
            }
            $this->file = $file;
        } else {
            $this->file = self::DEFAULT_FILE;
        }
    }

    /**
     * Gets the file name
     *
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Loads the configuration file
     *
     * @return array
     */
    public function load()
    {
        $file = $this->dirList->getPath(DirectoryList::CONFIG) . '/' . $this->file;
        $result = @include $file;
        return $result ?: [];
    }
}
