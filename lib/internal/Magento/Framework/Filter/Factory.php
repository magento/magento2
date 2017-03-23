<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter;

/**
 * Magento filter factory
 */
class Factory extends AbstractFactory
{
    /**
     * Set of filters
     *
     * @var array
     */
    protected $invokableClasses = [
        'email' => \Magento\Framework\Filter\Email::class,
        'money' => \Magento\Framework\Filter\Money::class,
        'simple' => \Magento\Framework\Filter\Template\Simple::class,
        'object' => \Magento\Framework\Filter\DataObject::class,
        'sprintf' => \Magento\Framework\Filter\Sprintf::class,
        'template' => \Magento\Framework\Filter\Template::class,
        'arrayFilter' => \Magento\Framework\Filter\ArrayFilter::class,
        'removeAccents' => \Magento\Framework\Filter\RemoveAccents::class,
        'splitWords' => \Magento\Framework\Filter\SplitWords::class,
        'removeTags' => \Magento\Framework\Filter\RemoveTags::class,
        'stripTags' => \Magento\Framework\Filter\StripTags::class,
        'truncate' => \Magento\Framework\Filter\Truncate::class,
        'encrypt' => \Magento\Framework\Filter\Encrypt::class,
        'decrypt' => \Magento\Framework\Filter\Decrypt::class,
        'translit' => \Magento\Framework\Filter\Translit::class,
        'translitUrl' => \Magento\Framework\Filter\TranslitUrl::class,
    ];

    /**
     * Shared instances, by default is shared
     *
     * @var array
     */
    protected $shared = [
        \Magento\Framework\Filter\Sprintf::class => false,
        \Magento\Framework\Filter\Money::class => false,
        \Magento\Framework\Filter\RemoveAccents::class => false,
        \Magento\Framework\Filter\SplitWords::class => false,
        \Magento\Framework\Filter\StripTags::class => false,
        \Magento\Framework\Filter\Truncate::class => false,
    ];
}
