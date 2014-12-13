<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Test\Repository;

use Mtf\Repository\AbstractRepository;

/**
 * Class Category Repository
 *
 */
class Category extends AbstractRepository
{
    /**
     * Attribute set for mapping data into ui tabs
     */
    const GROUP_GENERAL_INFORMATION = 'general_information';
    const GROUP_DISPLAY_SETTINGS = 'display_setting';

    /**
     * {inheritdoc}
     */
    public function __construct(array $defaultConfig = [], array $defaultData = [])
    {
        $this->_data['default'] = [
            'config' => $defaultConfig,
            'data' => $defaultData,
        ];
        $this->_data['anchor_category'] = $this->_getAnchorCategory();
    }

    /**
     * Enable anchor category
     *
     * @return array
     */
    protected function _getAnchorCategory()
    {
        $anchor = [
            'data' => [
                'fields' => [
                    'is_anchor' => [
                        'value' => 'Yes',
                        'input_value' => '1',
                        'group' => static::GROUP_DISPLAY_SETTINGS,
                        'input' => 'select',
                    ],
                ],
            ],
        ];
        return array_replace_recursive($this->_data['default'], $anchor);
    }
}
