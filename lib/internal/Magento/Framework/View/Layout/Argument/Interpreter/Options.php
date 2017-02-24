<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout\Argument\Interpreter;

use Magento\Framework\Data\Argument\InterpreterInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Interpreter that retrieves options from an option source model
 */
class Options implements InterpreterInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     * @return array Format: array(array('value' => <value>, 'label' => '<label>'), ...)
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public function evaluate(array $data)
    {
        if (!isset($data['model'])) {
            throw new \InvalidArgumentException('Options source model class is missing.');
        }
        $modelClass = $data['model'];
        $modelInstance = $this->objectManager->get($modelClass);
        if (!$modelInstance instanceof \Magento\Framework\Data\OptionSourceInterface) {
            throw new \UnexpectedValueException(
                sprintf("Instance of the options source model is expected, got %s instead.", get_class($modelInstance))
            );
        }
        $result = [];
        foreach ($modelInstance->toOptionArray() as $value => $label) {
            if (is_array($label)) {
                $result[] = $label;
            } else {
                $result[] = ['value' => $value, 'label' => $label];
            }
        }
        return $result;
    }
}
