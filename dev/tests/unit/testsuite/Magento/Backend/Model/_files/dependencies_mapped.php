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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

return array(
    'config' => array(
        'system' => array(
            'sections' => array(
                'section_1' => array(
                    'id' => 'section_1',
                    '_elementType' => 'section',
                    'children' => array(
                        'group_1' => array(
                            'id' => 'group_1',
                            '_elementType' => 'group',
                            'path' => 'section_1',
                            'depends' => array(
                                'fields' => array(
                                    'field_2' => array(
                                        'id' => 'section_1/group_1/field_2',
                                        'value' => 10,
                                        'dependPath' => array(
                                            'section_1',
                                            'group_1',
                                            'field_2',
                                        ),
                                    ),
                                ),
                            ),
                            'children' => array(
                                'field_2' => array(
                                    'id' => 'field_2',
                                    '_elementType' => 'field',
                                ),
                            ),
                        ),
                        'group_2' => array(
                            'id' => 'group_2',
                            '_elementType' => 'group',
                            'children' => array(
                                'field_3' => array(
                                    'id' => 'field_3',
                                    '_elementType' => 'field',
                                ),
                            ),
                        ),
                    ),
                ),
                'section_2' => array(
                    'id' => 'section_2',
                    '_elementType' => 'section',
                    'children' => array(
                        'group_3' => array(
                            'id' => 'group_3',
                            '_elementType' => 'group',
                            'children' => array(
                                'field_3' => array(
                                    'id' => 'field_3',
                                    '_elementType' => 'field',
                                    'path' => 'section_2/group_3',
                                    'depends' => array(
                                        'fields' => array(
                                            'field_4' => array(
                                                'id' => 'section_2/group_3/field_4',
                                                'value' => 'someValue',
                                                'dependPath' => array(
                                                    'section_2',
                                                    'group_3',
                                                    'field_4',
                                                ),
                                            ),
                                            'field_1' => array(
                                                'id' => 'section_1/group_3/field_1',
                                                'value' => 'someValue',
                                                'dependPath' => array(
                                                    'section_1',
                                                    'group_3',
                                                    'field_1',
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                                'field_4' => array(
                                    'id' => 'field_4',
                                    '_elementType' => 'field',
                                    'path' => 'section_2/group_3',
                                    'depends' => array(
                                        'fields' => array(
                                            'field_3' => array(
                                                'id' => 'section_2/group_3/field_3',
                                                'value' => 0,
                                                'dependPath' => array(
                                                    'section_2',
                                                    'group_3',
                                                    'field_3',
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                                'group_4_1' => array(
                                    'id' => 'group_4_1',
                                    '_elementType' => 'group',
                                    'path' => 'section_2/group_3',
                                    'depends' => array(
                                        'fields' => array(
                                            'field_3' => array(
                                                'id' => 'section_2/group_3/field_3',
                                                'value' => 0,
                                                'dependPath' => array(
                                                    'section_2',
                                                    'group_3',
                                                    'field_3',
                                                ),
                                            ),
                                        ),
                                    ),
                                    'children' => array(
                                        'field_5' => array(
                                            'id' => 'field_5',
                                            '_elementType' => 'field',
                                            'path' => 'section_2/group_3/group_4_1',
                                            'depends' => array(
                                                'fields' => array(
                                                    'field_4' => array(
                                                        'id' => 'section_2/group_3/group_4_1/field_4',
                                                        'value' => 'someValue',
                                                        'dependPath' => array(
                                                            'section_2',
                                                            'group_3',
                                                            'group_4_1',
                                                            'field_4',
                                                        ),
                                                    ),
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
);
