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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\ObjectManager\Config\Argument\Interpreter;

use Magento\ObjectManager\Config;
use Magento\ObjectManager\Config\Argument\ObjectFactory;
use Magento\Data\Argument\InterpreterInterface;
use Magento\Stdlib\BooleanUtils;

/**
 * Interpreter that creates an instance by a type name taking into account whether it's shared or not
 */
class Object implements InterpreterInterface
{
    /**
     * @var BooleanUtils
     */
    private $booleanUtils;

    /**
     * @var ObjectFactory
     */
    private $objectFactory;

    /**
     * @param BooleanUtils $booleanUtils
     * @param ObjectFactory $objectFactory
     */
    public function __construct(BooleanUtils $booleanUtils, ObjectFactory $objectFactory)
    {
        $this->booleanUtils = $booleanUtils;
        $this->objectFactory = $objectFactory;
    }

    /**
     * {@inheritdoc}
     * @return object
     * @throws \InvalidArgumentException
     */
    public function evaluate(array $data)
    {
        if (empty($data['value'])) {
            throw new \InvalidArgumentException('Object class name is missing.');
        }
        $className = $data['value'];
        $isShared = isset($data['shared']) ? $this->booleanUtils->toBoolean($data['shared']) : null;
        $result = $this->objectFactory->create($className, $isShared);
        return $result;
    }
}
