<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model\Method;

use Psr\Log\LoggerInterface;

/**
 * Class Logger for payment related information (request, response, etc.) which is used for debug
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Logger
{
    const DEBUG_KEYS_MASK = '****';
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Logs payment related information used for debug
     *
     * @param array $debugData
     * @param array $debugReplaceKeys
     * @param bool $debugFlag
     * @return void
     */
    public function debug(array $debugData, array $debugReplaceKeys, $debugFlag)
    {
        if ($debugFlag == true && !empty($debugData) && !empty($debugReplaceKeys)) {
                $debugData = $this->filterDebugData(
                    $debugData,
                    $debugReplaceKeys
                );
            $this->logger->debug(var_export($debugData, true));
        }
    }

    /**
     * Recursive filter data by private conventions
     *
     * @param array $debugData
     * @param array $debugReplacePrivateDataKeys
     * @return array
     */
    protected function filterDebugData(array $debugData, array $debugReplacePrivateDataKeys)
    {
        $debugReplacePrivateDataKeys = array_map('strtolower', $debugReplacePrivateDataKeys);

        foreach (array_keys($debugData) as $key) {
            if (in_array(strtolower($key), $debugReplacePrivateDataKeys)) {
                $debugData[$key] = self::DEBUG_KEYS_MASK;
            } elseif (is_array($debugData[$key])) {
                $debugData[$key] = $this->filterDebugData($debugData[$key], $debugReplacePrivateDataKeys);
            }
        }
        return $debugData;
    }
}
