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
    'root' => array(
        array(
            'item' => array(
                array(
                    '__attributes__' => array(
                        'id' => 'id1',
                        'attrZero' => 'value 0',
                    ),
                    '__content__' => 'Item 1.1',
                ),
                array(
                    '__attributes__' => array(
                        'id' => 'id2',
                        'attrOne' => 'value 2',
                    ),
                    'subitem' => array(
                        array(
                            '__attributes__' => array(
                                'id' => 'id2.1',
                                'attrTwo' => 'value 2.1',
                            ),
                            '__content__' => 'Item 2.1',
                        ),
                        array(
                            '__attributes__' => array(
                                'id' => 'id2.2',
                            ),
                            'value' => array(
                                array('__content__' => 1),
                                array('__content__' => 2),
                                array('__content__' => 'test'),
                            ),
                        ),
                    ),
                ),
                array(
                    '__attributes__' => array(
                        'id' => 'id3',
                        'attrThree' => 'value 3',
                    ),
                    '__content__' => 'Item 3.1',
                ),
            ),
        ),
    ),
);