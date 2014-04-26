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
    'without_mview_handle' => array(
        '<?xml version="1.0"?><config></config>',
        array("Element 'config': Missing child element(s). Expected is ( view ).")
    ),
    'mview_with_notallowed_attribute' => array(
        '<?xml version="1.0" encoding="UTF-8"?><config>' .
        '<view id="view_one" notallow="notallow" class="Ogogo\Class\One" group="some_view_group">' .
        '<subscriptions><table name="some_entity" entity_column="entity_id" /></subscriptions></view></config>',
        array("Element 'view', attribute 'notallow': The attribute 'notallow' is not allowed.")
    ),
    'mview_without_class_attribute' => array(
        '<?xml version="1.0" encoding="UTF-8"?><config><view id="view_one" group="some_view_group"><subscriptions>' .
        '<table name="some_entity" entity_column="entity_id" /></subscriptions></view></config>',
        array("Element 'view': The attribute 'class' is required but missing.")
    ),
    'mview_without_group_attribute' => array(
        '<?xml version="1.0" encoding="UTF-8"?><config><view id="view_one" class="Ogogo\Class\One"><subscriptions>' .
        '<table name="some_entity" entity_column="entity_id" /></subscriptions></view></config>',
        array("Element 'view': The attribute 'group' is required but missing.")
    ),
    'mview_with_empty_subscriptions' => array(
        '<?xml version="1.0" encoding="UTF-8"?><config>' .
        '<view id="view_one" class="Ogogo\Class\One" group="some_view_group"><subscriptions>' .
        '</subscriptions></view></config>',
        array("Element 'subscriptions': Missing child element(s). Expected is ( table ).")
    ),
    'subscriptions_without_table' => array(
        '<?xml version="1.0" encoding="UTF-8"?><config>' .
        '<view id="view_one" class="Ogogo\Class\One" group="some_view_group"><subscriptions>' .
        '</subscriptions></view></config>',
        array("Element 'subscriptions': Missing child element(s). Expected is ( table ).")
    ),
    'table_without_column_attribute' => array(
        '<?xml version="1.0" encoding="UTF-8"?><config>' .
        '<view id="view_one" class="Ogogo\Class\One" group="some_view_group"><subscriptions>' .
        '<table name="some_entity" /></subscriptions></view></config>',
        array("Element 'table': The attribute 'entity_column' is required but missing.")
    ),
    'subscriptions_duplicate_table' => array(
        '<?xml version="1.0" encoding="UTF-8"?><config>' .
        '<view id="view_one" class="Ogogo\Class\One" group="some_view_group"><subscriptions>' .
        '<table name="some_entity" entity_column="entity_id" />' .
        '<table name="some_entity" entity_column="entity_id" /></subscriptions></view></config>',
        array(
            "Element 'table': Duplicate key-sequence ['some_entity', 'entity_id'] in unique identity-constraint " .
            "'uniqueSubscriptionsTable'."
        )
    )
);
