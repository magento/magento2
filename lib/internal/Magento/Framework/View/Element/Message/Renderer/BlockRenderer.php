<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\Message\Renderer;

use Magento\Framework\Message\MessageInterface;
use Magento\Framework\View\Element\Message\Renderer\BlockRenderer\Template;

/**
 * Class \Magento\Framework\View\Element\Message\Renderer\BlockRenderer
 *
 * @since 2.0.0
 */
class BlockRenderer implements RendererInterface
{
    /**
     * complex_renderer
     */
    const CODE = 'block_renderer';

    /**
     * @var array
     * @since 2.0.0
     */
    private $configuration;

    /**
     * @var Template
     * @since 2.0.0
     */
    private $template;

    /**
     * @param Template $template
     * @since 2.0.0
     */
    public function __construct(
        Template $template
    ) {
        $this->template = $template;
    }

    /**
     * Renders complex message
     *
     * @param MessageInterface $message
     * @param array $initializationData
     * @return string
     * @since 2.0.0
     */
    public function render(MessageInterface $message, array $initializationData)
    {
        $this->setUpConfiguration($message->getData(), $initializationData);

        $result = $this->template->toHtml();

        $this->tearDownConfiguration();

        return $result;
    }

    /**
     * @param array $configuration
     * @param array $initializationData
     * @return void
     * @since 2.0.0
     */
    private function setUpConfiguration(array $configuration, array $initializationData)
    {
        if (!isset($initializationData['template'])) {
            throw new \InvalidArgumentException('Template should be provided for the renderer.');
        }

        $this->configuration = $configuration;

        $this->template->setTemplate($initializationData['template']);
        $this->template->setData($configuration);
    }

    /**
     * @return void
     * @since 2.0.0
     */
    private function tearDownConfiguration()
    {
        foreach (array_keys($this->configuration) as $key) {
            $this->template->unsetData($key);
            unset($this->configuration[$key]);
        }

        $this->template->setTemplate('');
    }
}
