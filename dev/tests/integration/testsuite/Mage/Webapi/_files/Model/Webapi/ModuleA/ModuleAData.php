<?php
/**
 * Test data structure fixture
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NamespaceA_ModuleA_Model_Webapi_ModuleAData
{
    /**
     * String param.
     *
     * @var string inline doc.
     */
    public $stringParam;

    /**
     * Integer param.
     *
     * @var int
     */
    public $integerParam;

    /**
     * Optional string param.
     *
     * @var string
     */
    public $optionalParam = 'default';

    /**
     * Recursive link to self.
     *
     * @var NamespaceA_ModuleA_Model_Webapi_ModuleAData
     */
    public $linkToSelf;

    /**
     * Recursive link to array of selves.
     *
     * @var NamespaceA_ModuleA_Model_Webapi_ModuleAData[]
     */
    public $linkToArrayOfSelves;

    /**
     * Link to complex type which has link to this type.
     *
     * @var NamespaceA_ModuleA_Model_Webapi_ModuleADataB
     */
    public $loopLink;

    /**
     * Link to array of loops
     *
     * @var NamespaceA_ModuleA_Model_Webapi_ModuleADataB[]
     * @optional true
     */
    public $loopArray;
}
