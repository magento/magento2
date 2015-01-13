<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout\Argument\Interpreter\Decorator;

use Magento\Framework\Data\Argument\InterpreterInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Interpreter decorator that passes value, computed by subject of decoration, through the sequence of "updaters"
 */
class Updater implements InterpreterInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var InterpreterInterface
     */
    private $subject;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param InterpreterInterface $subject
     */
    public function __construct(ObjectManagerInterface $objectManager, InterpreterInterface $subject)
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
        $updaters = !empty($data['updater']) ? $data['updater'] : [];
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
