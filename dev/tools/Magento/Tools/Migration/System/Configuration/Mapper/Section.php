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
    protected $_allowedFieldNames = array('label', 'class', 'resource', 'header_css', 'tab');

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
        $output = array();
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
