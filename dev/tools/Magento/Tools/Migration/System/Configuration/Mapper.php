<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        $output = array();
        $output['comment'] = $config['comment'];

        $tabsConfig = isset($config['tabs']) ? $config['tabs'] : array();
        $sectionsConfig = isset($config['sections']) ? $config['sections'] : array();

        /** @var array $nodes  */
        $nodes = $this->_tabMapper->transform($tabsConfig);

        $transformedSections = $this->_sectionMapper->transform($sectionsConfig);

        $nodes = array_merge($nodes, $transformedSections);

        $output['nodes'] = $nodes;

        return $output;
    }
}
