<?php
/**
 * Fixture of processed API action controller for resource config.
 * Controller files is at _files/controllers/Webapi/ResourceAController.php
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
    'methods' => array(
        'create' => array(
            'documentation' => "Short description.
Long description.
Multiline <b>with html</b>.",
            'interface' => array(
                'in' => array(
                    'parameters' => array(
                        'resourceData' => array(
                            'type' => 'NamespaceAModuleAData',
                            'required' => true,
                            'documentation' => 'Data of the resource'
                        )
                    )
                ),
                'out' => array(
                    'parameters' => array(
                        'result' => array(
                            'type' => 'int',
                            'documentation' => 'ID of created resource',
                            'required' => true,
                        )
                    ),
                ),
            ),
        ),
        'get' => array(
            'documentation' => 'Get resource.',
            'interface' => array(
                'in' => array(
                    'parameters' => array(
                        'resourceId' => array(
                            'type' => 'int',
                            'required' => true,
                            'documentation' => 'ID of resource'
                        )
                    )
                ),
                'out' => array(
                    'parameters' => array(
                        'result' => array(
                            'type' => 'NamespaceAModuleAData',
                            'documentation' => 'data of resource',
                            'required' => true,
                        )
                    ),
                ),
            ),
        ),
    ),
);
