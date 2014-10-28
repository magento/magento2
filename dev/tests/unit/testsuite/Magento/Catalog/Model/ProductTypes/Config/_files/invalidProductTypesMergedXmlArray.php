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
    'type_without_required_name' => array(
        '<?xml version="1.0" encoding="UTF-8"?><config><type label="some label" modelInstance="model_name" /></config>',
        array(
            "Element 'type': The attribute 'name' is required but missing.",
            "Element 'type': Not all fields of key identity-constraint 'productTypeKey' evaluate to a node."
        )
    ),
    'type_without_required_label' => array(
        '<?xml version="1.0" encoding="UTF-8"?><config><type name="some_name" modelInstance="model_name" /></config>',
        array("Element 'type': The attribute 'label' is required but missing.")
    ),
    'type_without_required_modelInstance' => array(
        '<?xml version="1.0" encoding="UTF-8"?><config><type label="some_label" name="some_name" /></config>',
        array("Element 'type': The attribute 'modelInstance' is required but missing.")
    ),
    'type_pricemodel_without_required_instance_attribute' => array(
        '<?xml version="1.0" encoding="UTF-8"?><config>' .
        '<type label="some_label" name="some_name" modelInstance="model_name"><priceModel/></type></config>',
        array("Element 'priceModel': The attribute 'instance' is required but missing.")
    ),
    'type_indexmodel_without_required_instance_attribute' => array(
        '<?xml version="1.0" encoding="UTF-8"?><config>' .
        '<type label="some_label" name="some_name" modelInstance="model_name"><indexerModel/></type></config>',
        array("Element 'indexerModel': The attribute 'instance' is required but missing.")
    ),
    'type_stockindexermodel_without_required_instance_attribute' => array(
        '<?xml version="1.0" encoding="UTF-8"?><config><type label="some_label" ' .
        'name="some_name" modelInstance="model_name"><stockIndexerModel/></type></config>',
        array("Element 'stockIndexerModel': The attribute 'instance' is required but missing.")
    )
);
