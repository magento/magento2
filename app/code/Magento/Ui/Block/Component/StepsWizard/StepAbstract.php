<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Block\Component\StepsWizard;

use Magento\Framework\View\Element\Template;

/**
 * Abstract block for multi-step wizard UI
 */
abstract class StepAbstract extends Template implements StepInterface
{
    /**
     * @inheritdoc
     */
    public function getContent()
    {
        return $this->toHtml();
    }

    /**
     * @inheritdoc
     */
    public function getParentComponentName()
    {
        return $this->getParentBlock()->getComponentName();
    }

    /**
     * @inheritdoc
     */
    public function getComponentName()
    {
        if (null === $this->getData('component_name')) {
            $this->setData('component_name', $this->getParentComponentName() . '_' . $this->getNameInLayout());
        }
        return $this->getData('component_name');
    }
}
