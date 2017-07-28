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
 * @since 2.0.0
 */
class StepsWizard extends \Magento\Framework\View\Element\Template
{
    /**
     * Wizard step template
     *
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'Magento_Ui::stepswizard.phtml';

    /**
     * @var array
     * @since 2.0.0
     */
    protected $initData = [];

    /**
     * @var null|\Magento\Ui\Block\Component\StepsWizard\StepInterface[]
     * @since 2.0.0
     */
    private $steps;

    /**
     * @return \Magento\Ui\Block\Component\StepsWizard\StepInterface[]
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getStepComponents()
    {
        return array_keys($this->getSteps());
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getComponentName()
    {
        return $this->getNameInLayout();
    }

    /**
     * @return array
     * @since 2.0.0
     */
    public function getInitData()
    {
        return $this->initData;
    }

    /**
     * @param array $initData
     * @return $this
     * @since 2.0.0
     */
    public function setInitData($initData)
    {
        $this->initData = $initData;

        return $this;
    }
}
