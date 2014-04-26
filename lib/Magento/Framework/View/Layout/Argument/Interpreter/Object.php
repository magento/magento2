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
namespace Magento\Framework\View\Layout\Argument\Interpreter;

use Magento\Framework\ObjectManager;
use Magento\Framework\Data\Argument\InterpreterInterface;

/**
 * Interpreter that instantiates object by a class name
 */
class Object implements InterpreterInterface
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var string|null
     */
    private $expectedClass;

    /**
     * @param ObjectManager $objectManager
     * @param string|null $expectedClass
     */
    public function __construct(ObjectManager $objectManager, $expectedClass = null)
    {
        $this->objectManager = $objectManager;
        $this->expectedClass = $expectedClass;
    }

    /**
     * {@inheritdoc}
     * @return object
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public function evaluate(array $data)
    {
        if (!isset($data['value'])) {
            throw new \InvalidArgumentException('Object class name is missing.');
        }
        $className = $data['value'];
        $result = $this->objectManager->create($className);
        if ($this->expectedClass && !$result instanceof $this->expectedClass) {
            throw new \UnexpectedValueException(
                sprintf("Instance of %s is expected, got %s instead.", $this->expectedClass, get_class($result))
            );
        }
        return $result;
    }
}
