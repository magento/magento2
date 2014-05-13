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
    'name' => array(
        'group.1' => array(
            'fields' => array('f1.1' => array('value' => 'f1.1.val'), 'f1.2' => array('value' => 'f1.2.val'))
        ),
        'group.2' => array(
            'fields' => array(
                'f2.1' => array('value' => 'f2.1.val'),
                'f2.2' => array('value' => 'f2.2.val'),
                'f2.3' => array('value' => '')
            ),
            'groups' => array(
                'group.2.1' => array(
                    'fields' => array(
                        'f2.1.1' => array('value' => 'f2.1.1.val'),
                        'f2.1.2' => array('value' => 'f2.1.2.val'),
                        'f2.1.3' => array('value' => '')
                    ),
                    'groups' => array(
                        'group.2.1.1' => array(
                            'fields' => array(
                                'f2.1.1.1' => array('value' => 'f2.1.1.1.val'),
                                'f2.1.1.2' => array('value' => 'f2.1.1.2.val'),
                                'f2.1.1.3' => array('value' => '')
                            )
                        )
                    )
                )
            )
        ),
        'group.3' => 'some.data'
    )
);
