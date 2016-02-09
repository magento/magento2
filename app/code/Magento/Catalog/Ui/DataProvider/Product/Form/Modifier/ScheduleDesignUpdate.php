<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Grouper;

/**
 * Class ScheduleDesignUpdateMetaProvider customizes Schedule Design Update panel
 */
class ScheduleDesignUpdate extends AbstractModifier
{
    /**#@+
     * Field names
     */
    const CODE_CUSTOM_DESIGN_FROM = 'custom_design_from';
    const CODE_CUSTOM_DESIGN_TO = 'custom_design_to';
    /**#@-*/

    /**
     * @var Grouper
     */
    protected $grouper;

    /**
     * @param Grouper $grouper
     */
    public function __construct(Grouper $grouper)
    {
        $this->grouper = $grouper;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        return $this->customizeDateRangeField($meta);
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * Customize date range field if from and to fields belong to one group
     *
     * @param array $meta
     * @return array
     */
    protected function customizeDateRangeField(array $meta)
    {
        if (
            $this->getGroupCodeByField($meta, self::CODE_CUSTOM_DESIGN_FROM)
            !== $this->getGroupCodeByField($meta, self::CODE_CUSTOM_DESIGN_TO)
        ) {
            return $meta;
        }

        $groupCode = $this->getGroupCodeByField($meta, self::CODE_CUSTOM_DESIGN_FROM);
        $parentChildren = &$meta[$groupCode]['children'];

        if (isset($parentChildren[self::CODE_CUSTOM_DESIGN_FROM])) {
            $parentChildrenConfig = $parentChildren[self::CODE_CUSTOM_DESIGN_FROM]['arguments']['data']['config'];
            $meta = $this->grouper->groupMetaElements(
                $meta,
                [
                    self::CODE_CUSTOM_DESIGN_FROM => [
                        'meta' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'label' => __('Schedule Update From'),
                                        'scopeLabel' => null,
                                        'additionalClasses' => 'admin__field-date'
                                    ]

                                ]
                            ],
                        ],
                    ],
                    'custom_design_to' => [
                        'meta' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'label' => __('To'),
                                        'scopeLabel' => null,
                                        'additionalClasses' => 'admin__field-date',
                                    ],
                                ]
                            ]
                        ]
                    ],
                    [
                        'targetCode' => 'custom_design_date_range',
                        'meta' => [
                            'arguments' => [
                                'data' => [
                                    'label' => __('Schedule Update From'),
                                    'additionalClasses' => 'admin__control-grouped-date',
                                    'breakLine' => false,
                                    'scopeLabel' => $parentChildrenConfig['scopeLabel'],
                                ],
                            ],
                        ]
                    ],
                ]
            );
        }

        return $meta;
    }
}
