<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Wrapper;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Element\UiComponent\ContainerInterface;
use Magento\Framework\View\Element\Template\Context as TemplateContext;

/**
 * Class UiComponent
 *
 * Encapsulate UI Component to represent it as standard Layout Block
 */
class UiComponent extends Template implements ContainerInterface
{
    /**
     * Constructor
     *
     * @param TemplateContext $context
     * @param UiComponentInterface $component
     * @param BlockFactory $blockWrapperFactory
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        protected readonly UiComponentInterface $component,
        protected readonly BlockFactory $blockWrapperFactory,
        array $data = []
    ) {
        $this->setNameInLayout($this->component->getName());
        parent::__construct($context, $data);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        foreach ($this->getChildNames() as $childName) {
            $childBlock = $this->getLayout()->getBlock($childName);
            if ($childBlock) {
                $wrapper = $this->blockWrapperFactory->create([
                    'block' => $childBlock,
                    'data' => [
                        'name' => 'block_' . $childName
                    ]
                ]);
                $this->component->addComponent('block_' . $childName, $wrapper);
            }
        }

        $result = $this->component->render();
        return (string)$result;
    }
}
