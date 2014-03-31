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
 * @category   Magento
 * @package    Magento_Filter
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Filter;

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
        'email' => 'Magento\Filter\Email',
        'money' => 'Magento\Filter\Money',
        'simple' => 'Magento\Filter\Template\Simple',
        'object' => 'Magento\Filter\Object',
        'sprintf' => 'Magento\Filter\Sprintf',
        'template' => 'Magento\Filter\Template',
        'arrayFilter' => 'Magento\Filter\ArrayFilter',
        'removeAccents' => 'Magento\Filter\RemoveAccents',
        'splitWords' => 'Magento\Filter\SplitWords',
        'removeTags' => 'Magento\Filter\RemoveTags',
        'stripTags' => 'Magento\Filter\StripTags',
        'truncate' => 'Magento\Filter\Truncate',
        'encrypt' => 'Magento\Filter\Encrypt',
        'decrypt' => 'Magento\Filter\Decrypt',
        'translit' => 'Magento\Filter\Translit',
        'translitUrl' => 'Magento\Filter\TranslitUrl'
    );

    /**
     * Shared instances, by default is shared
     *
     * @var array
     */
    protected $shared = array(
        'Magento\Filter\Sprintf' => false,
        'Magento\Filter\Money' => false,
        'Magento\Filter\RemoveAccents' => false,
        'Magento\Filter\SplitWords' => false,
        'Magento\Filter\StripTags' => false,
        'Magento\Filter\Truncate' => false
    );
}
