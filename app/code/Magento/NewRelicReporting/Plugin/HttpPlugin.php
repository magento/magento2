<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Plugin;

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Http;
use Magento\NewRelicReporting\Model\Config;
use Magento\NewRelicReporting\Model\NewRelicWrapper;

class HttpPlugin
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var NewRelicWrapper
     */
    private $newRelicWrapper;

    /**
     * @param Config $config
     * @param NewRelicWrapper $newRelicWrapper
     */
    public function __construct(
        Config $config,
        NewRelicWrapper $newRelicWrapper
    ) {
        $this->config = $config;
        $this->newRelicWrapper = $newRelicWrapper;
    }

    /**
     * Report exception to New Relic
     *
     * @param Http $subject
     * @param Bootstrap $bootstrap
     * @param \Exception $exception
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeCatchException(Http $subject, Bootstrap $bootstrap, \Exception $exception)
    {
        if ($this->config->isNewRelicEnabled()) {
            $this->newRelicWrapper->reportError($exception);
        }
    }
}
