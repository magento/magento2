<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
?>
<?php return [
    [
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
        ],
    [
        'type' => 'add',
        'id' => 'elem_one_one',
        'title' => 'Title one.one',
        'toolTip' => 'toolTip 2',
        'module' => 'Module_One',
        'sortOrder' => 90,
        'action' => 'adminhtml/system',
        'resource' => 'Module_One::one_one',
        'parent' => 'elem_one_zero',
        ],
    [
        'type' => 'update',
        'id' => 'elem_one_zero',
        'title' => 'Title one.zero update',
        'toolTip' => 'toolTip 3',
        'module' => 'Module_One_Update',
        'sortOrder' => 90,
        'action' => 'adminhtml/system',
        'parent' => 'elem_one_zero',
        ],
    [
        'type' => 'remove',
        'id' => 'elem_one_one',
        ],
    [
        'type' => 'add',
        'id' => 'elem_two_zero',
        'title' => 'Title two.zero',
        'toolTip' => 'toolTip 4',
        'module' => 'Module_Two',
        'resource' => 'Module_Two::two_zero',
        'sortOrder' => 90,
        'action' => 'adminhtml/system',
        ],
    [
        'type' => 'add',
        'id' => 'elem_two_two',
        'title' => 'Title two.two',
        'toolTip' => 'toolTip 5',
        'module' => 'Module_Two',
        'sortOrder' => 90,
        'action' => 'adminhtml/system',
        'resource' => 'Module_Two::two_two',
        'parent' => 'elem_two_zero',
        ],
    [
        'type' => 'update',
        'id' => 'elem_two_zero',
        'title' => 'Title two.zero update',
        'toolTip' => 'toolTip 6',
        'module' => 'Module_Two_Update',
        'sortOrder' => 90,
        'action' => 'adminhtml/system',
        'parent' => 'elem_two_zero',
        ],
    [
        'type' => 'remove',
        'id' => 'elem_two_two',
        ],
];
