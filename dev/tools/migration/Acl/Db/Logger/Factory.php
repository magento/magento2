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
 * @copyright  Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Tools_Migration_Acl_Db_Logger_Factory
{
    /**
     * List of allowed logger types
     * @var array
     */
    protected $_allowedLoggerTypes = array();

    public function __construct()
    {
        $this->_allowedLoggerTypes = array(
            'console',
            'file',
        );
    }

    /**
     * @param string $loggerType
     * @param string $filePath
     * @return Tools_Migration_Acl_Db_LoggerAbstract
     * @throws InvalidArgumentException
     */
    public function getLogger($loggerType, $filePath = null)
    {
        $loggerType = empty($loggerType) ? 'console' : $loggerType;
        if (false == in_array($loggerType, $this->_allowedLoggerTypes)) {
            throw new InvalidArgumentException('Invalid logger type: ' . $loggerType);
        }

        $loggerClassName = null;
        switch ($loggerType) {
            case 'file':
                $loggerClassName = 'Tools_Migration_Acl_Db_Logger_File';
                break;
            default:
                $loggerClassName = 'Tools_Migration_Acl_Db_Logger_Console';
                break;
        }

        return new $loggerClassName($filePath);
    }
}
