<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model\Backend\Config\Structure;

/**
 * Class \Magento\Cron\Model\Backend\Config\Structure\Converter
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
     * Modify system configuration for cron
     *
     * @param \Magento\Config\Model\Config\Structure\Converter $subject
     * @param array $result
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @since 2.0.0
     */
    public function afterConvert(\Magento\Config\Model\Config\Structure\Converter $subject, array $result)
    {
        $groupIterator = 0;
        if (!isset($result['config']['system']['sections']['system']['children']['cron']['children']['template'])) {
            return $result;
        }
        foreach ($this->groupsConfig->get() as $group => $fields) {
            $template = $result['config']['system']['sections']['system']['children']['cron']['children']['template'];
            $template['id'] = $group;
            $template['label'] .= $group;
            $template['sortOrder'] += $groupIterator++;

            $fieldIterator = 0;
            foreach ($fields as $fieldName => $value) {
                $template['children'][$fieldName]['path'] = 'system/cron/' . $group;
                $template['children'][$fieldName]['sortOrder'] += $fieldIterator++;
                if (isset($value['tooltip'])) {
                    $template['children'][$fieldName]['tooltip'] = $value['tooltip'];
                }
            }
            $result['config']['system']['sections']['system']['children']['cron']['children'][$group] = $template;
        }
        unset($result['config']['system']['sections']['system']['children']['cron']['children']['template']);
        return $result;
    }
}
