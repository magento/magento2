<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Migration\System\Configuration\Mapper;

class Section extends \Magento\Tools\Migration\System\Configuration\Mapper\AbstractMapper
{
    /**
     * @var \Magento\Tools\Migration\System\Configuration\Mapper\Group
     */
    protected $_groupMapper;

    /**
     * List of allowed filed names for section
     *
     * @var array
     */
    protected $_allowedFieldNames = ['label', 'class', 'resource', 'header_css', 'tab'];

    /**
     * @param \Magento\Tools\Migration\System\Configuration\Mapper\Group $groupMapper
     */
    public function __construct(\Magento\Tools\Migration\System\Configuration\Mapper\Group $groupMapper)
    {
        $this->_groupMapper = $groupMapper;
    }

    /**
     * Transform section config
     *
     * @param array $config
     * @return array
     */
    public function transform(array $config)
    {
        $output = [];
        foreach ($config as $sectionName => $sectionConfig) {
            $output[] = $this->_transformElement($sectionName, $sectionConfig, 'section');
        }
        return $output;
    }

    /**
     * Transform section sub configuration
     *
     * @param array $config
     * @param array $parentNode
     * @param array $element
     * @return array
     */
    protected function _transformSubConfig(array $config, $parentNode, $element)
    {
        if ($parentNode['name'] == 'groups') {
            $subConfig = $this->_groupMapper->transform($config);
            if (null !== $subConfig) {
                $element['subConfig'] = $subConfig;
            }
        }
        return $element;
    }
}
