<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter;

/**
 * Zend filter factory
 */
class ZendFactory extends AbstractFactory
{
    /**
     * Set of filters
     *
     * @var array
     */
    protected $invokableClasses = [
        'stripTags' => 'Zend_Filter_StripTags',
        'stripNewlines' => 'Zend_Filter_StripNewlines',
        'stringTrim' => 'Zend_Filter_StringTrim',
        'stringToUpper' => 'Zend_Filter_StringToUpper',
        'stringToLower' => 'Zend_Filter_StringToLower',
        'realPath' => 'Zend_Filter_RealPath',
        'pregReplace' => 'Zend_Filter_PregReplace',
        'null' => 'Zend_Filter_Null',
        'normalizedToLocalized' => 'Zend_Filter_NormalizedToLocalized',
        'localizedToNormalized' => 'Zend_Filter_LocalizedToNormalized',
        'int' => 'Zend_Filter_Int',
        'inflector' => 'Zend_Filter_Inflector',
        'htmlEntities' => 'Zend_Filter_HtmlEntities',
        'zendEncrypt' => 'Zend_Filter_Encrypt',
        'zendDecrypt' => 'Zend_Filter_Decrypt',
        'dir' => 'Zend_Filter_Dir',
        'digits' => 'Zend_Filter_Digits',
        'zendDecompress' => 'Zend_Filter_Decompress',
        'zendCompress' => 'Zend_Filter_Compress',
        'callback' => 'Zend_Filter_Callback',
        'boolean' => 'Zend_Filter_Boolean',
        'baseName' => 'Zend_Filter_BaseName',
        'alpha' => 'Zend_Filter_Alpha',
        'alnum' => 'Zend_Filter_Alnum',
        'underscoreToSeparator' => 'Zend_Filter_Word_UnderscoreToSeparator',
        'underscoreToDash' => 'Zend_Filter_Word_UnderscoreToDash',
        'underscoreToCamelCase' => 'Zend_Filter_Word_UnderscoreToCamelCase',
        'separatorToSeparator' => 'Zend_Filter_Word_SeparatorToSeparator',
        'separatorToDash' => 'Zend_Filter_Word_SeparatorToDash',
        'separatorToCamelCase' => 'Zend_Filter_Word_SeparatorToCamelCase',
        'dashToUnderscore' => 'Zend_Filter_Word_DashToUnderscore',
        'dashToSeparator' => 'Zend_Filter_Word_DashToSeparator',
        'dashToCamelCase' => 'Zend_Filter_Word_DashToCamelCase',
        'camelCaseToUnderscore' => 'Zend_Filter_Word_CamelCaseToUnderscore',
        'camelCaseToSeparator' => 'Zend_Filter_Word_CamelCaseToSeparator',
        'camelCaseToDash' => 'Zend_Filter_Word_CamelCaseToDash',
        'fileUpperCase' => 'Zend_Filter_File_UpperCase',
        'fileRename' => 'Zend_Filter_File_Rename',
        'lowerCase' => 'Zend_Filter_File_LowerCase',
        'fileEncrypt' => 'Zend_Filter_File_Encrypt',
        'fileDecrypt' => 'Zend_Filter_File_Decrypt',
    ];

    /**
     * Whether or not to share by default; default to false
     *
     * @var bool
     */
    protected $shareByDefault = false;

    /**
     * Shared instances, by default is shared
     *
     * @var array
     */
    protected $shared = [
        'Zend_Filter_StripNewlines' => true,
        'Zend_Filter_Int' => true,
        'Zend_Filter_Dir' => true,
        'Zend_Filter_Digits' => true,
        'Zend_Filter_BaseName' => true,
    ];
}
