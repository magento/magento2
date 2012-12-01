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
 * @category   Magento
 * @package    tools
 * @copyright  Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Tools_Migration_System_Configuration_Logger_Factory
{
    /**
     * Get logger instance
     *
     * @param string $loggerType
     * @param string $filePath
     * @param Tools_Migration_System_FileManager $fileManager
     * @return Tools_Migration_System_Configuration_LoggerAbstract
     */
    public function getLogger($loggerType, $filePath, Tools_Migration_System_FileManager $fileManager)
    {
        /** @var Tools_Migration_System_Configuration_LoggerAbstract $loggerInstance  */
        $loggerInstance = null;
        switch ($loggerType) {
            case 'file':
                $loggerInstance = new Tools_Migration_System_Configuration_Logger_File($filePath, $fileManager);
                break;
            default:
                $loggerInstance = new Tools_Migration_System_Configuration_Logger_Console();
                break;
        }

        return $loggerInstance;
    }
}
