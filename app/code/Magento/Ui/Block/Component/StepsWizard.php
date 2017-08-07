<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Block\Component;

/**
 * Multi steps wizard block
 *
 * @api
 */
class StepsWizard extends \Magento\Framework\View\Element\Template
{
    /**
     * Wizard step template
     *
     * @var string
     */
    protected $_template = 'Magento_Ui::stepswizard.phtml';

    /**
     * @var array
     */
    protected $initData = [];

    /**
     * @var null|\Magento\Ui\Block\Component\StepsWizard\StepInterface[]
     */
    private $steps;

    /**
     * @return \Magento\Ui\Block\Component\StepsWizard\StepInterface[]
     */
    public function getSteps()
    {
        if ($this->steps === null) {
            foreach ($this->getLayout()->getChildBlocks($this->getNameInLayout()) as $step) {
                if ($step instanceof StepsWizard\StepInterface) {
                    $this->steps[$step->getComponentName()] = $step;
                }
            }
        }
        return $this->steps;
    }

    // @codeCoverageIgnoreStart

    /**
     * @return array
     */
    public function getStepComponents()
    {
        return array_keys($this->getSteps());
    }

    /**
     * @return string
     */
    public function getComponentName()
    {
        return $this->getNameInLayout();
    }

    /**
     * @return array
     */
    public function getInitData()
    {
        return $this->initData;
    }

    /**
     * @param array $initData
     * @return $this
     */
    public function setInitData($initData)
    {
        $this->initData = $initData;

        return $this;
    }
}
