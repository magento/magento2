<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Container;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Element\UiComponent\ContainerInterface;
use Magento\Framework\View\Element\Template\Context as TemplateContext;

/**
 * Class Component
 */
class Component extends Template implements ContainerInterface
{
    /**
     * Ui component
     *
     * @var UiComponentInterface
     */
    protected $component;

    /**
     * @var BlockFactory
     */
    protected $containerFactory;

    /**
     * Constructor
     *
     * @param TemplateContext $context
     * @param UiComponentInterface $component
     * @param BlockFactory $containerFactory
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        UiComponentInterface $component,
        BlockFactory $containerFactory,
        array $data = []
    ) {
        $this->component = $component;
        $this->containerFactory = $containerFactory;
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
                $container = $this->containerFactory->create([
                    'block' => $childBlock
                ]);
                $this->component->addComponent('block_' . $childName, $container);
            }
        }

        $result = $this->component->render();
        return (string)$result;
    }
}
