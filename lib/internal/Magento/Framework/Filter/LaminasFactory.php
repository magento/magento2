<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Filter;

/**
 * Laminas filter factory
 */
class LaminasFactory extends AbstractFactory
{
    /**
     * Set of filters
     *
     * @var array
     */
    protected $invokableClasses = [
        'stripNewlines' => \Laminas\Filter\StripNewlines::class,
        'stringTrim' => \Laminas\Filter\StringTrim::class,
        'stringToUpper' => \Laminas\Filter\StringToUpper::class,
        'stringToLower' => \Laminas\Filter\StringToLower::class,
        'realPath' => \Laminas\Filter\RealPath::class,
        'pregReplace' => \Laminas\Filter\PregReplace::class,
        'null' => \Laminas\Filter\ToNull::class,
        'int' => \Laminas\Filter\ToInt::class,
        'inflector' => \Laminas\Filter\Inflector::class,
        'htmlEntities' => \Laminas\Filter\HtmlEntities::class,
        'encrypt' => \Laminas\Filter\Encrypt::class,
        'decrypt' => \Laminas\Filter\Decrypt::class,
        'dir' => \Laminas\Filter\Dir::class,
        'digits' => \Laminas\Filter\Digits::class,
        'decompress' => \Laminas\Filter\Decompress::class,
        'compress' => \Laminas\Filter\Compress::class,
        'callback' => \Laminas\Filter\Callback::class,
        'boolean' => \Laminas\Filter\Boolean::class,
        'baseName' => \Laminas\Filter\BaseName::class,
        'alpha' => \Laminas\I18n\Filter\Alpha::class,
        'alnum' => \Laminas\I18n\Filter\Alnum::class,
        'underscoreToSeparator' => \Laminas\Filter\Word\UnderscoreToSeparator::class,
        'underscoreToDash' => \Laminas\Filter\Word\UnderscoreToDash::class,
        'underscoreToCamelCase' => \Laminas\Filter\Word\UnderscoreToCamelCase::class,
        'separatorToSeparator' => \Laminas\Filter\Word\SeparatorToSeparator::class,
        'separatorToDash' => \Laminas\Filter\Word\SeparatorToDash::class,
        'separatorToCamelCase' => \Laminas\Filter\Word\SeparatorToCamelCase::class,
        'dashToUnderscore' => \Laminas\Filter\Word\DashToUnderscore::class,
        'dashToSeparator' => \Laminas\Filter\Word\DashToSeparator::class,
        'dashToCamelCase' => \Laminas\Filter\Word\DashToCamelCase::class,
        'camelCaseToUnderscore' => \Laminas\Filter\Word\CamelCaseToUnderscore::class,
        'camelCaseToSeparator' => \Laminas\Filter\Word\CamelCaseToSeparator::class,
        'camelCaseToDash' => \Laminas\Filter\Word\CamelCaseToDash::class,
        'fileUpperCase' => \Laminas\Filter\File\UpperCase::class,
        'fileRename' => \Laminas\Filter\File\Rename::class,
        'lowerCase' => \Laminas\Filter\File\LowerCase::class,
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
        \Laminas\Filter\StripNewlines::class => true,
        \Laminas\Filter\Dir::class => true,
        \Laminas\Filter\Digits::class => true,
        \Laminas\Filter\BaseName::class => true,
    ];
}
