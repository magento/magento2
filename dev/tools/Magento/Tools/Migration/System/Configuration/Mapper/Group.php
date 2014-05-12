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
    protected $_allowedFieldNames = array(
        'label',
        'frontend_model',
        'clone_fields',
        'clone_model',
        'fieldset_css',
        'help_url',
        'comment',
        'hide_in_single_store_mode',
        'expanded'
    );

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
        $output = array();
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
