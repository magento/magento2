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
namespace Magento\Framework\App;

use Magento\Framework\App\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Application Maintenance Mode
 */
class MaintenanceMode
{
    /**
     * Maintenance flag file name
     *
     * DO NOT consolidate this file and the IP white list into one.
     * It is going to work much faster in 99% of cases: the isOn() will return false whenever file doesn't exist.
     */
    const FLAG_FILENAME = '.maintenance.flag';

    /**
     * IP-addresses file name
     */
    const IP_FILENAME = '.maintenance.ip';

    /**
     * Maintenance flag dir
     */
    const FLAG_DIR = Filesystem::VAR_DIR;

    /**
     * Path to store files
     *
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $flagDir;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->flagDir = $filesystem->getDirectoryWrite(self::FLAG_DIR);
    }

    /**
     * Checks whether mode is on
     *
     * Optionally specify an IP-address to compare against the white list
     *
     * @param string $remoteAddr
     * @return bool
     */
    public function isOn($remoteAddr = '')
    {
        if (!$this->flagDir->isExist(self::FLAG_FILENAME)) {
            return false;
        }
        $info = $this->getAddressInfo();
        return !in_array($remoteAddr, $info);
    }

    /**
     * Sets maintenance mode "on" or "off"
     *
     * @param bool $isOn
     * @return bool
     */
    public function set($isOn)
    {
        if ($isOn) {
            return $this->flagDir->touch(self::FLAG_FILENAME);
        }
        if ($this->flagDir->isExist(self::FLAG_FILENAME)) {
            return $this->flagDir->delete(self::FLAG_FILENAME);
        }
        return true;
    }

    /**
     * Sets list of allowed IP addresses
     *
     * @param string $addresses
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function setAddresses($addresses)
    {
        $addresses = (string)$addresses;
        if (empty($addresses)) {
            if ($this->flagDir->isExist(self::IP_FILENAME)) {
                return $this->flagDir->delete(self::IP_FILENAME);
            }
            return true;
        }
        if (!preg_match('/^[^\s,]+(,[^\s,]+)*$/', $addresses)) {
            throw new \InvalidArgumentException("One or more IP-addresses is expected (comma-separated)\n");
        }
        $result = $this->flagDir->writeFile(self::IP_FILENAME, $addresses);
        return false !== $result ? true : false;
    }

    /**
     * Get list of IP addresses effective for maintenance mode
     *
     * @return string[]
     */
    public function getAddressInfo()
    {
        if ($this->flagDir->isExist(self::IP_FILENAME)) {
            $temp = $this->flagDir->readFile(self::IP_FILENAME);
            return explode(',', trim($temp));
        } else {
            return [];
        }
    }
}
