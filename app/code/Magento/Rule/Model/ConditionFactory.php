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
namespace Magento\Rule\Model;

use Magento\Framework\ObjectManager;

class ConditionFactory
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * Store all used condition models
     *
     * @var array
     */
    private $conditionModels = [];

    /**
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create new object for each requested model.
     * If model is requested first time, store it at array.
     * It's made by performance reasons to avoid initialization of same models each time when rules are being processed.
     *
     * @param string $type
     *
     * @return \Magento\Rule\Model\Condition\ConditionInterface
     *
     * @throws \LogicException
     * @throws \BadMethodCallException
     */
    public function create($type)
    {
        if (!array_key_exists($type, $this->conditionModels)) {
            $this->conditionModels[$type] = $this->objectManager->create($type);
        }

        return clone $this->conditionModels[$type];
    }
}
