<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Block\Component\StepsWizard;

abstract class StepAbstract extends \Magento\Framework\View\Element\Template implements StepInterface
{
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Math\Random $mathRandom,
        $data
    ) {
        parent::__construct($context, $data);
        $this->mathRandom = $mathRandom;
    }

    /**
     * Get step id
     *
     * @return string
     */
    public function getId()
    {
        if (null === $this->getData('id')) {
            $this->setData('id', $this->mathRandom->getUniqueHash('step_'));
        }
        return $this->getData('id');
    }

    /**
     * {@inheritdoc}
     */
    public function getContent()
    {
        return $this->toHtml();
    }

    /**
     * Get json data
     * @return string json
     */
    public function getJsonData()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getComponentName()
    {
        return $this->getParentBlock()->getComponentName();
    }
}
