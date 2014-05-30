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
namespace Magento\Framework\App\State;

use Magento\Framework\App\Filesystem;

/**
 * Application Maintenance Mode
 */
class MaintenanceMode
{
    /**
     * Maintenance flag name
     */
    const FLAG_FILENAME = 'maintenance.flag';

    /**
     * Maintenance flag dir
     */
    const FLAG_DIR = Filesystem::VAR_DIR;

    /**
     * @var \Magento\Framework\App\Filesystem
     */
    protected $filesystem;

    /**
     * @param \Magento\Framework\App\Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Turn on store maintenance mode
     *
     * @param string $data
     * @return bool
     */
    public function turnOn($data = 'maintenance')
    {
        try {
            $this->filesystem->getDirectoryWrite(self::FLAG_DIR)
                ->writeFile(self::FLAG_FILENAME, $data);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * Turn off store maintenance mode
     *
     * @return bool
     */
    public function turnOff()
    {
        try {
            $this->filesystem->getDirectoryWrite(self::FLAG_DIR)->delete(self::FLAG_FILENAME);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }
}
