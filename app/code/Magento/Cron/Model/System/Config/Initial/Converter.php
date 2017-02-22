<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model\System\Config\Initial;

class Converter
{
    /**
     * @var \Magento\Cron\Model\Groups\Config\Data
     */
    protected $groupsConfig;

    /**
     * @param \Magento\Cron\Model\Groups\Config\Data $groupsConfig
     */
    public function __construct(\Magento\Cron\Model\Groups\Config\Data $groupsConfig)
    {
        $this->groupsConfig = $groupsConfig;
    }

    /**
     * Modify global configuration for cron
     *
     * @param \Magento\Framework\App\Config\Initial\Converter $subject
     * @param array $result
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterConvert(\Magento\Framework\App\Config\Initial\Converter $subject, array $result)
    {
        if (isset($result['data']['default']['system'])) {
            $result['data']['default']['system']['cron'] = $this->groupsConfig->get();
        }
        return $result;
    }
}
