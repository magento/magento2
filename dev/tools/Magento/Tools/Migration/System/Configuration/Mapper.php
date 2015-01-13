<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Tools\Migration\System\Configuration;

class Mapper
{
    /**
     * @var \Magento\Tools\Migration\System\Configuration\Mapper\Tab
     */
    protected $_tabMapper;

    /**
     * @var \Magento\Tools\Migration\System\Configuration\Mapper\Section
     */
    protected $_sectionMapper;

    /**
     * @param \Magento\Tools\Migration\System\Configuration\Mapper\Tab $tabMapper
     * @param \Magento\Tools\Migration\System\Configuration\Mapper\Section $sectionMapper
     */
    public function __construct(
        \Magento\Tools\Migration\System\Configuration\Mapper\Tab $tabMapper,
        \Magento\Tools\Migration\System\Configuration\Mapper\Section $sectionMapper
    ) {
        $this->_tabMapper = $tabMapper;
        $this->_sectionMapper = $sectionMapper;
    }

    /**
     * Transform configuration
     *
     * @param array $config
     * @return array
     */
    public function transform(array $config)
    {
        $output = [];
        $output['comment'] = isset($config['comment']) ? $config['comment'] : '';

        $tabsConfig = isset($config['tabs']) ? $config['tabs'] : [];
        $sectionsConfig = isset($config['sections']) ? $config['sections'] : [];

        /** @var array $nodes  */
        $nodes = $this->_tabMapper->transform($tabsConfig);

        $transformedSections = $this->_sectionMapper->transform($sectionsConfig);

        $nodes = array_merge($nodes, $transformedSections);

        $output['nodes'] = $nodes;

        return $output;
    }
}
