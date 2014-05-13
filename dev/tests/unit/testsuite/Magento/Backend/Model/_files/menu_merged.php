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
?>
<?php return array(
    array (
        'type' => 'add',
        'id' => 'elem_one_zero',
        'title' => 'Title one.zero',
        'toolTip' => 'toolTip 1',
        'module' => 'Module_One',
        'sortOrder' => 90,
        'action' => 'adminhtml/system',
        'resource' => 'Module_One::one_zero',
        'dependsOnModule' => 'Module_One',
        'dependsOnConfig' => '/one/two',
        ),
    array (
        'type' => 'add',
        'id' => 'elem_one_one',
        'title' => 'Title one.one',
        'toolTip' => 'toolTip 2',
        'module' => 'Module_One',
        'sortOrder' => 90,
        'action' => 'adminhtml/system',
        'resource' => 'Module_One::one_one',
        'parent' => 'elem_one_zero',
        ),
    array (
        'type' => 'update',
        'id' => 'elem_one_zero',
        'title' => 'Title one.zero update',
        'toolTip' => 'toolTip 3',
        'module' => 'Module_One_Update',
        'sortOrder' => 90,
        'action' => 'adminhtml/system',
        'parent' => 'elem_one_zero',
        ),
    array (
        'type' => 'remove',
        'id' => 'elem_one_one',
        ),
    array (
        'type' => 'add',
        'id' => 'elem_two_zero',
        'title' => 'Title two.zero',
        'toolTip' => 'toolTip 4',
        'module' => 'Module_Two',
        'resource' => 'Module_Two::two_zero',
        'sortOrder' => 90,
        'action' => 'adminhtml/system',
        ),
    array (
        'type' => 'add',
        'id' => 'elem_two_two',
        'title' => 'Title two.two',
        'toolTip' => 'toolTip 5',
        'module' => 'Module_Two',
        'sortOrder' => 90,
        'action' => 'adminhtml/system',
        'resource' => 'Module_Two::two_two',
        'parent' => 'elem_two_zero',
        ),
    array (
        'type' => 'update',
        'id' => 'elem_two_zero',
        'title' => 'Title two.zero update',
        'toolTip' => 'toolTip 6',
        'module' => 'Module_Two_Update',
        'sortOrder' => 90,
        'action' => 'adminhtml/system',
        'parent' => 'elem_two_zero',
        ),
    array (
        'type' => 'remove',
        'id' => 'elem_two_two',
        ),
);
