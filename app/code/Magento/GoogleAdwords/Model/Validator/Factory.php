<?php
/**
 * Google AdWords Validator Factory
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @SuppressWarnings(PHPMD.LongVariable)
 */
namespace Magento\GoogleAdwords\Model\Validator;

use Magento\Framework\Validator\Int;
use Magento\Framework\Validator\Regex;
use Magento\Framework\Validator\UniversalFactory;

class Factory
{
    /**
     * @var UniversalFactory
     */
    protected $_validatorBuilderFactory;

    /**
     * @param UniversalFactory $validatorBuilderFactory
     */
    public function __construct(UniversalFactory $validatorBuilderFactory)
    {
        $this->_validatorBuilderFactory = $validatorBuilderFactory;
    }

    /**
     * Create color validator
     *
     * @param string $currentColor
     * @return \Magento\Framework\Validator
     */
    public function createColorValidator($currentColor)
    {
        $message = __(
            'Conversion Color value is not valid "%1". Please set hexadecimal 6-digit value.',
            $currentColor
        );
        /** @var \Magento\Framework\Validator\Builder $builder */
        $builder = $this->_validatorBuilderFactory->create(
            'Magento\Framework\Validator\Builder',
            array(
                'constraints' => array(
                    array(
                        'alias' => 'Regex',
                        'type' => '',
                        'class' => 'Magento\Framework\Validator\Regex',
                        'options' => array(
                            'arguments' => array('pattern' => '/^[0-9a-f]{6}$/i'),
                            'methods' => array(
                                array(
                                    'method' => 'setMessages',
                                    'arguments' => array(
                                        array(Regex::NOT_MATCH => $message, Regex::INVALID => $message)
                                    )
                                )
                            )
                        )
                    )
                )
            )
        );
        return $builder->createValidator();
    }

    /**
     * Create Conversion id validator
     *
     * @param int|string $currentId
     * @return \Magento\Framework\Validator
     */
    public function createConversionIdValidator($currentId)
    {
        $message = __('Conversion Id value is not valid "%1". Conversion Id should be an integer.', $currentId);
        /** @var \Magento\Framework\Validator\Builder $builder */
        $builder = $this->_validatorBuilderFactory->create(
            'Magento\Framework\Validator\Builder',
            array(
                'constraints' => array(
                    array(
                        'alias' => 'Int',
                        'type' => '',
                        'class' => 'Magento\Framework\Validator\Int',
                        'options' => array(
                            'methods' => array(
                                array(
                                    'method' => 'setMessages',
                                    'arguments' => array(array(Int::NOT_INT => $message, Int::INVALID => $message))
                                )
                            )
                        )
                    )
                )
            )
        );
        return $builder->createValidator();
    }
}
