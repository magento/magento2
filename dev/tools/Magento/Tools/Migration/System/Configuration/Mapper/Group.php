<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Migration\System\Configuration\Mapper;

class Group extends \Magento\Tools\Migration\System\Configuration\Mapper\AbstractMapper
{
    /**
     * @var Tools_Migration_System_Configuration_Mapper_Field
     */
    protected $_fieldMapper;

    /**
     * List of allowed field names for group
     * @var array
     */
    protected $_allowedFieldNames = [
        'label',
        'frontend_model',
        'clone_fields',
        'clone_model',
        'fieldset_css',
        'help_url',
        'comment',
        'hide_in_single_store_mode',
        'expanded',
    ];

    /**
     * @param Tools_Migration_System_Configuration_Mapper_Field $fieldMapper
     */
    public function __construct(\Magento\Tools\Migration\System\Configuration\Mapper\Field $fieldMapper)
    {
        $this->_fieldMapper = $fieldMapper;
    }

    /**
     * Transform group configuration
     *
     * @param array $config
     * @return array
     */
    public function transform(array $config)
    {
        $output = [];
        foreach ($config as $groupName => $groupConfig) {
            $output[] = $this->_transformElement($groupName, $groupConfig, 'group');
        }
        return $output;
    }

    /**
     * @param array $config
     * @param array $parentNode
     * @param array $element
     * @return array
     */
    protected function _transformSubConfig(array $config, $parentNode, $element)
    {
        if ($parentNode['name'] == 'fields') {
            $subConfig = $this->_fieldMapper->transform($config);
            if (null !== $subConfig) {
                $element['subConfig'] = $subConfig;
            }
        }
        return $element;
    }
}
