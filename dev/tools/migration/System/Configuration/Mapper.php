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
 * @category   Magento
 * @package    tools
 * @copyright  Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Tools_Migration_System_Configuration_Mapper
{

    /**
     * @var Tools_Migration_System_Configuration_Mapper_Tab
     */
    protected $_tabMapper;

    /**
     * @var Tools_Migration_System_Configuration_Mapper_Section
     */
    protected $_sectionMapper;

    /**
     * @param Tools_Migration_System_Configuration_Mapper_Tab $tabMapper
     * @param Tools_Migration_System_Configuration_Mapper_Section $sectionMapper
     */
    public function __construct(Tools_Migration_System_Configuration_Mapper_Tab $tabMapper,
        Tools_Migration_System_Configuration_Mapper_Section $sectionMapper
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
