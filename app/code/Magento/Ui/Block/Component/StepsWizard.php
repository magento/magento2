<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Block\Component;

class StepsWizard extends \Magento\Framework\View\Element\Template
{
    protected $_template = 'Magento_Ui::stepswizard.phtml';

    /**
     * @return \Magento\Ui\Block\Component\StepsWizard\Step[]
     */
    public function getSteps()
    {
        $steps = [];
        foreach ($this->getLayout()->getChildBlocks($this->getNameInLayout()) as $step) {
            if ($step instanceof StepsWizard\Step) {
                $steps[] = $step;
            }
        }
        return $steps;
    }
}
