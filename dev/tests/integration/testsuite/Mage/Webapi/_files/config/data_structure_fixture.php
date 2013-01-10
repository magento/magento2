<?php
/**
 * Fixture of processed complex type class.
 * Complex type class is at /_files/controllers/Webapi/ModuleA/SubresourceData.php
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
return array(
    'documentation' => 'Test data structure fixture',
    'parameters' => array(
        'stringParam' => array(
            'type' => 'string',
            'required' => true,
            'default' => null,
            'documentation' => 'inline doc.String param.',
        ),
        'integerParam' => array(
            'type' => 'int',
            'required' => true,
            'default' => null,
            'documentation' => 'Integer param.',
        ),
        'optionalParam' => array(
            'type' => 'string',
            'required' => false,
            'default' => 'default',
            'documentation' => 'Optional string param.',
        ),
        'linkToSelf' => array(
            'type' => 'NamespaceAModuleAData',
            'required' => true,
            'default' => null,
            'documentation' => 'Recursive link to self.',
        ),
        'linkToArrayOfSelves' => array(
            'type' => 'NamespaceAModuleAData[]',
            'required' => true,
            'default' => null,
            'documentation' => 'Recursive link to array of selves.',
        ),
        'loopLink' => array(
            'type' => 'NamespaceAModuleADataB',
            'required' => true,
            'default' => null,
            'documentation' => 'Link to complex type which has link to this type.',
        ),
        'loopArray' => array(
            'type' => 'NamespaceAModuleADataB[]',
            'required' => false,
            'default' => null,
            'documentation' => 'Link to array of loops',
        ),
    ),
);
