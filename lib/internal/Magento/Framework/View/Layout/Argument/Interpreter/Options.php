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
 * Interpreter that retrieves options from an option source model
 */
class Options implements InterpreterInterface
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
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
        $result = array();
        foreach ($modelInstance->toOptionArray() as $value => $label) {
            if (is_array($label)) {
                $result[] = $label;
            } else {
                $result[] = array('value' => $value, 'label' => $label);
            }
        }
        return $result;
    }
}
