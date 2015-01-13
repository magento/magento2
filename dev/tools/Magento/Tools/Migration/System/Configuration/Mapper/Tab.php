<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Migration\System\Configuration\Mapper;

class Tab extends \Magento\Tools\Migration\System\Configuration\Mapper\AbstractMapper
{
    /**
     * Attribute maps
     * oldName => newName
     * @var array
     */
    protected $_attributeMaps = ['sort_order' => 'sortOrder', 'frontend_type' => 'type', 'class' => 'class'];

    /**
     * List of allowed filed names for tab
     *
     * @var array
     */
    protected $_allowedFieldNames = ['label'];

    /**
     * Transform tabs configuration
     *
     * @param array $config
     * @return array
     */
    public function transform(array $config)
    {
        $output = [];
        foreach ($config as $tabName => $tabConfig) {
            $output[] = $this->_transformElement($tabName, $tabConfig, 'tab');
        }
        return $output;
    }

    /**
     * Transform sub configuration
     *
     * @param array $config
     * @param array $parentNode
     * @param array $element
     * @return array
     */
    protected function _transformSubConfig(array $config, $parentNode, $element)
    {
        return $element;
    }
}
