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
 * @category    Magento
 * @package     Magento_Di
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

return array(
    'Magento_Di_Definition_CompilerDefinition_TestAsset_NoConstructor'                   =>
    array(
        'supertypes'   =>
        array(),
        'instantiator' => '__construct',
        'methods'      =>
        array(),
        'parameters'   =>
        array(),
    ),
    'Magento_Di_Definition_CompilerDefinition_TestAsset_OneOptionalArgument'             =>
    array(
        'supertypes'   =>
        array(),
        'instantiator' => '__construct',
        'methods'      =>
        array(
            '__construct' => true,
        ),
        'parameters'   =>
        array(
            '__construct' =>
            array(
                'Magento_Di_Definition_CompilerDefinition_TestAsset_OneOptionalArgument::__construct:0' =>
                array(
                    0 => 'varA',
                    1 => 'Magento_Di_Definition_CompilerDefinition_TestAsset_NoConstructor',
                    2 => false,
                    3 => NULL,
                ),
            ),
        ),
    ),
    'Magento_Di_Definition_CompilerDefinition_TestAsset_OneRequiredArgument'             =>
    array(
        'supertypes'   =>
        array(),
        'instantiator' => '__construct',
        'methods'      =>
        array(
            '__construct' => true,
        ),
        'parameters'   =>
        array(
            '__construct' =>
            array(
                'Magento_Di_Definition_CompilerDefinition_TestAsset_OneRequiredArgument::__construct:0' =>
                array(
                    0 => 'varA',
                    1 => 'Magento_Di_Definition_CompilerDefinition_TestAsset_NoConstructor',
                    2 => true,
                    3 => NULL,
                ),
            ),
        ),
    ),
    'Magento_Di_Definition_CompilerDefinition_TestAsset_OneRequiredOneOptionalArguments' =>
    array(
        'supertypes'   =>
        array(),
        'instantiator' => '__construct',
        'methods'      =>
        array(
            '__construct' => true,
        ),
        'parameters'   =>
        array(
            '__construct' =>
            array(
                'Magento_Di_Definition_CompilerDefinition_TestAsset_OneRequiredOneOptionalArguments::__construct:0' =>
                array(
                    0 => 'varA',
                    1 => NULL,
                    2 => true,
                    3 => NULL,
                ),
                'Magento_Di_Definition_CompilerDefinition_TestAsset_OneRequiredOneOptionalArguments::__construct:1' =>
                array(
                    0 => 'varB',
                    1 => 'Magento_Di_Definition_CompilerDefinition_TestAsset_OneOptionalArgument',
                    2 => false,
                    3 => NULL,
                ),
            ),
        ),
    ),
    'Magento_Di_Definition_CompilerDefinition_TestAsset_TwoOptionalArguments'            =>
    array(
        'supertypes'   =>
        array(),
        'instantiator' => '__construct',
        'methods'      =>
        array(
            '__construct' => true,
        ),
        'parameters'   =>
        array(
            '__construct' =>
            array(
                'Magento_Di_Definition_CompilerDefinition_TestAsset_TwoOptionalArguments::__construct:0' =>
                array(
                    0 => 'varA',
                    1 => NULL,
                    2 => false,
                    3 => 1,
                ),
                'Magento_Di_Definition_CompilerDefinition_TestAsset_TwoOptionalArguments::__construct:1' =>
                array(
                    0 => 'varB',
                    1 => 'Magento_Di_Definition_CompilerDefinition_TestAsset_OneOptionalArgument',
                    2 => false,
                    3 => NULL,
                ),
            ),
        ),
    ),
    'Magento_Di_Definition_CompilerDefinition_TestAsset_TwoRequiredArguments'            =>
    array(
        'supertypes'   =>
        array(),
        'instantiator' => '__construct',
        'methods'      =>
        array(
            '__construct' => true,
        ),
        'parameters'   =>
        array(
            '__construct' =>
            array(
                'Magento_Di_Definition_CompilerDefinition_TestAsset_TwoRequiredArguments::__construct:0' =>
                array(
                    0 => 'varA',
                    1 => 'Magento_Di_Definition_CompilerDefinition_TestAsset_NoConstructor',
                    2 => true,
                    3 => NULL,
                ),
                'Magento_Di_Definition_CompilerDefinition_TestAsset_TwoRequiredArguments::__construct:1' =>
                array(
                    0 => 'varB',
                    1 => 'Magento_Di_Definition_CompilerDefinition_TestAsset_OneRequiredArgument',
                    2 => true,
                    3 => NULL,
                ),
            ),
        ),
    ),
);
