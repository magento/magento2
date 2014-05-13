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
namespace Magento\Tools\Migration\System\Configuration\Logger;

/**
 * Migration logger. Output result put to file
 */
class File extends \Magento\Tools\Migration\System\Configuration\AbstractLogger
{
    /**
     * Path to log file
     *
     * @var string
     */
    protected $_file = null;

    /**
     * @var \Magento\Tools\Migration\System\FileManager
     */
    protected $_fileManager;

    /**
     * @param string $file
     * @param \Magento\Tools\Migration\System\FileManager $fileManger
     * @throws \InvalidArgumentException
     */
    public function __construct($file, \Magento\Tools\Migration\System\FileManager $fileManger)
    {
        $this->_fileManager = $fileManger;

        $logDir = realpath(__DIR__ . '/../../') . '/log/';

        if (empty($file)) {
            throw new \InvalidArgumentException('Log file name is required');
        }
        $this->_file = $logDir . $file;
    }

    /**
     * Put report to file
     *
     * @return void
     */
    public function report()
    {
        $this->_fileManager->write($this->_file, (string)$this);
    }
}
