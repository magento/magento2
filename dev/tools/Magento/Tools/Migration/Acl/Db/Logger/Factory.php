<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Tools\Migration\Acl\Db\Logger;

class Factory
{
    /**
     * List of allowed logger types
     *
     * @var array
     */
    protected $_allowedLoggerTypes = [];

    /**
     * Constructor for Db\Logger\Factory
     */
    public function __construct()
    {
        $this->_allowedLoggerTypes = ['console', 'file'];
    }

    /**
     * @param string $loggerType
     * @param string $filePath
     * @return \Magento\Tools\Migration\Acl\Db\AbstractLogger
     * @throws \InvalidArgumentException
     */
    public function getLogger($loggerType, $filePath = null)
    {
        $loggerType = empty($loggerType) ? 'console' : $loggerType;
        if (false == in_array($loggerType, $this->_allowedLoggerTypes)) {
            throw new \InvalidArgumentException('Invalid logger type: ' . $loggerType);
        }

        $loggerClassName = null;
        switch ($loggerType) {
            case 'file':
                $loggerClassName = 'Magento\Tools\Migration\Acl\Db\Logger\File';
                break;
            default:
                $loggerClassName = 'Magento\Tools\Migration\Acl\Db\Logger\Console';
                break;
        }

        return new $loggerClassName($filePath);
    }
}
