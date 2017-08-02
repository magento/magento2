<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model\System\Config\Initial;

/**
 * Class \Magento\Cron\Model\System\Config\Initial\Converter
 *
 * @since 2.0.0
 */
class Converter
{
    /**
     * @var \Magento\Cron\Model\Groups\Config\Data
     * @since 2.0.0
     */
    protected $groupsConfig;

    /**
     * @param \Magento\Cron\Model\Groups\Config\Data $groupsConfig
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function afterConvert(\Magento\Framework\App\Config\Initial\Converter $subject, array $result)
    {
        if (isset($result['data']['default']['system'])) {
            $groups = $this->groupsConfig->get();
            foreach ($groups as $group => $fields) {
                foreach ($fields as $key => $field) {
                    $groups[$group][$key] = $field['value'];
                }
            }
            $result['data']['default']['system']['cron'] = $groups;
        }
        return $result;
    }
}
