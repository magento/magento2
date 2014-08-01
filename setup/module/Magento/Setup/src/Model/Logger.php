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

namespace Magento\Setup\Model;

class Logger
{
    /**
     * @var string
     */
    protected $logFile = 'install.log';

    /**
     * @var resource
     */
    protected $resource;

    /**
     * @var bool
     */
    protected $hasError = false;

    public function __construct()
    {
        $this->logFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $this->logFile;
    }

    public function open()
    {
        $this->resource = @fopen($this->logFile, 'a+');
    }

    public function close()
    {
        fclose($this->resource);
    }

    /**
     * @param string $moduleName
     */
    public function logSuccess($moduleName)
    {
        $this->open();
        fwrite($this->resource, '<span class="text-success">[SUCCESS] ' . $moduleName . ' ... installed</span>' . PHP_EOL);
        $this->close();
    }

    /**
     * @param \Exception $e
     */
    public function logError(\Exception $e)
    {
        $this->open();
        fwrite($this->resource, '<span class="text-danger">[ERROR] ' . $e . '<span>' . PHP_EOL);
        $this->close();
    }

    /**
     * @return bool
     */
    public function hasError()
    {
        return $this->hasError;
    }

    /**
     * @return array
     */
    public function get()
    {
        $this->open();
        fseek($this->resource, 0);
        $messages = [];
        while (($string = fgets($this->resource)) !== false) {
            if (strpos($string, '[ERROR]') !== false) {
                $this->hasError = true;
            }
            $messages[] = $string;
        }
        $this->close();
        return $messages;
    }

    public function clear()
    {
        @unlink($this->logFile);
    }
}
 