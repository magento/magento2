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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
    protected $invokableClasses = array(
        'email' => 'Magento\Framework\Filter\Email',
        'money' => 'Magento\Framework\Filter\Money',
        'simple' => 'Magento\Framework\Filter\Template\Simple',
        'object' => 'Magento\Framework\Filter\Object',
        'sprintf' => 'Magento\Framework\Filter\Sprintf',
        'template' => 'Magento\Framework\Filter\Template',
        'arrayFilter' => 'Magento\Framework\Filter\ArrayFilter',
        'removeAccents' => 'Magento\Framework\Filter\RemoveAccents',
        'splitWords' => 'Magento\Framework\Filter\SplitWords',
        'removeTags' => 'Magento\Framework\Filter\RemoveTags',
        'stripTags' => 'Magento\Framework\Filter\StripTags',
        'truncate' => 'Magento\Framework\Filter\Truncate',
        'encrypt' => 'Magento\Framework\Filter\Encrypt',
        'decrypt' => 'Magento\Framework\Filter\Decrypt',
        'translit' => 'Magento\Framework\Filter\Translit',
        'translitUrl' => 'Magento\Framework\Filter\TranslitUrl'
    );

    /**
     * Shared instances, by default is shared
     *
     * @var array
     */
    protected $shared = array(
        'Magento\Framework\Filter\Sprintf' => false,
        'Magento\Framework\Filter\Money' => false,
        'Magento\Framework\Filter\RemoveAccents' => false,
        'Magento\Framework\Filter\SplitWords' => false,
        'Magento\Framework\Filter\StripTags' => false,
        'Magento\Framework\Filter\Truncate' => false
    );
}
