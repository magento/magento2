<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'without_mview_handle' => [
        '<?xml version="1.0"?><config></config>',
        ["Element 'config': Missing child element(s). Expected is ( view )."],
    ],
    'mview_with_notallowed_attribute' => [
        '<?xml version="1.0" encoding="UTF-8"?><config>' .
        '<view id="view_one" notallow="notallow" class="Ogogo\Class\One" group="some_view_group">' .
        '<subscriptions><table name="some_entity" entity_column="entity_id" /></subscriptions></view></config>',
        ["Element 'view', attribute 'notallow': The attribute 'notallow' is not allowed."],
    ],
    'mview_without_class_attribute' => [
        '<?xml version="1.0" encoding="UTF-8"?><config><view id="view_one" group="some_view_group"><subscriptions>' .
        '<table name="some_entity" entity_column="entity_id" /></subscriptions></view></config>',
        ["Element 'view': The attribute 'class' is required but missing."],
    ],
    'mview_without_group_attribute' => [
        '<?xml version="1.0" encoding="UTF-8"?><config><view id="view_one" class="Ogogo\Class\One"><subscriptions>' .
        '<table name="some_entity" entity_column="entity_id" /></subscriptions></view></config>',
        ["Element 'view': The attribute 'group' is required but missing."],
    ],
    'mview_with_empty_subscriptions' => [
        '<?xml version="1.0" encoding="UTF-8"?><config>' .
        '<view id="view_one" class="Ogogo\Class\One" group="some_view_group"><subscriptions>' .
        '</subscriptions></view></config>',
        ["Element 'subscriptions': Missing child element(s). Expected is ( table )."],
    ],
    'subscriptions_without_table' => [
        '<?xml version="1.0" encoding="UTF-8"?><config>' .
        '<view id="view_one" class="Ogogo\Class\One" group="some_view_group"><subscriptions>' .
        '</subscriptions></view></config>',
        ["Element 'subscriptions': Missing child element(s). Expected is ( table )."],
    ],
    'table_without_column_attribute' => [
        '<?xml version="1.0" encoding="UTF-8"?><config>' .
        '<view id="view_one" class="Ogogo\Class\One" group="some_view_group"><subscriptions>' .
        '<table name="some_entity" /></subscriptions></view></config>',
        ["Element 'table': The attribute 'entity_column' is required but missing."],
    ],
    'subscriptions_duplicate_table' => [
        '<?xml version="1.0" encoding="UTF-8"?><config>' .
        '<view id="view_one" class="Ogogo\Class\One" group="some_view_group"><subscriptions>' .
        '<table name="some_entity" entity_column="entity_id" />' .
        '<table name="some_entity" entity_column="entity_id" /></subscriptions></view></config>',
        [
            "Element 'table': Duplicate key-sequence ['some_entity', 'entity_id'] in unique identity-constraint " .
            "'uniqueSubscriptionsTable'."
        ],
    ]
];
