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
namespace Magento\Framework\View\Layout\Argument\Interpreter\Decorator;

use Magento\Framework\ObjectManager;
use Magento\Framework\Data\Argument\InterpreterInterface;

/**
 * Interpreter decorator that passes value, computed by subject of decoration, through the sequence of "updaters"
 */
class Updater implements InterpreterInterface
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var InterpreterInterface
     */
    private $subject;

    /**
     * @param ObjectManager $objectManager
     * @param InterpreterInterface $subject
     */
    public function __construct(ObjectManager $objectManager, InterpreterInterface $subject)
    {
        $this->objectManager = $objectManager;
        $this->subject = $subject;
    }

    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     */
    public function evaluate(array $data)
    {
        $updaters = !empty($data['updater']) ? $data['updater'] : array();
        unset($data['updater']);
        if (!is_array($updaters)) {
            throw new \InvalidArgumentException('Layout argument updaters are expected to be an array of classes.');
        }
        $result = $this->subject->evaluate($data);
        foreach ($updaters as $updaterClass) {
            $result = $this->applyUpdater($result, $updaterClass);
        }
        return $result;
    }

    /**
     * Invoke an updater, passing an input value to it, and return invocation result
     *
     * @param mixed $value
     * @param string $updaterClass
     * @return mixed
     * @throws \UnexpectedValueException
     */
    protected function applyUpdater($value, $updaterClass)
    {
        /** @var \Magento\Framework\View\Layout\Argument\UpdaterInterface $updaterInstance */
        $updaterInstance = $this->objectManager->get($updaterClass);
        if (!$updaterInstance instanceof \Magento\Framework\View\Layout\Argument\UpdaterInterface) {
            throw new \UnexpectedValueException(
                sprintf(
                    'Instance of layout argument updater is expected, got %s instead.',
                    get_class($updaterInstance)
                )
            );
        }
        return $updaterInstance->update($value);
    }
}
