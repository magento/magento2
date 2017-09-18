<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model\Method;

use Psr\Log\LoggerInterface;

/**
 * Class Logger for payment related information (request, response, etc.) which is used for debug
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 *
 * @api
 * @since 100.0.2
 */
class Logger
{
    const DEBUG_KEYS_MASK = '****';

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Payment\Gateway\ConfigInterface
     */
    private $config;

    /**
     * @param LoggerInterface $logger
     * @param \Magento\Payment\Gateway\ConfigInterface $config
     */
    public function __construct(
        LoggerInterface $logger,
        \Magento\Payment\Gateway\ConfigInterface $config = null
    ) {
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     * Logs payment related information used for debug
     *
     * @param array $data
     * @param array|null $maskKeys
     * @param bool|null $forceDebug
     * @return void
     */
    public function debug(array $data, array $maskKeys = null, $forceDebug = null)
    {
        $maskKeys = $maskKeys !== null ? $maskKeys : $this->getDebugReplaceFields();
        $debugOn = $forceDebug !== null ? $forceDebug : $this->isDebugOn();
        if ($debugOn === true) {
            $data = $this->filterDebugData(
                $data,
                $maskKeys
            );
            $this->logger->debug(var_export($data, true));
        }
    }

    /**
     * Returns configured keys to be replaced with mask
     *
     * @return array
     */
    private function getDebugReplaceFields()
    {
        if ($this->config and $this->config->getValue('debugReplaceKeys')) {
            return explode(',', $this->config->getValue('debugReplaceKeys'));
        }
        return [];
    }

    /**
     * Whether debug is enabled in configuration
     *
     * @return bool
     */
    private function isDebugOn()
    {
        return $this->config and (bool)$this->config->getValue('debug');
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
