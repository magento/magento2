<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Ui\Component\Column;

use Magento\Config\Model\Config\Backend\Admin\Custom;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class DateConfigProvider
 *
 * @package Magento\Catalog\Ui\Component\Column
 */
class DateConfigProvider implements DataTypeConfigProviderInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * DateConfigProvider constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return ['timeZone' => $this->getTimeZone()];
    }

    /**
     * @return string
     */
    private function getTimeZone(): string
    {
        return (string)$this->scopeConfig->getValue(
            Custom::XML_PATH_GENERAL_LOCALE_TIMEZONE
        );
    }
}
