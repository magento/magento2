<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\Message\Renderer;

use Magento\Framework\Message\MessageInterface;
use Magento\Framework\View\Element\Template;

class BlockRenderer extends Template implements RendererInterface
{
    /**
     * complex_renderer
     */
    const CODE = 'block_renderer';

    /**
     * @var array
     */
    private $configuration;

    /**
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Renders complex message
     *
     * @param MessageInterface $message
     * @param array $initializationData
     * @return string
     */
    public function render(MessageInterface $message, array $initializationData)
    {
        $this->setUpConfiguration($message->getData(), $initializationData);
        return $this->toHtml();
    }

    /**
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return array_merge((array)$this->configuration, [$this->getTemplate()]);
    }

    /**
     * @param array $configuration
     * @param array $initializationData
     */
    private function setUpConfiguration(array $configuration, array $initializationData)
    {
        if (!isset($initializationData['template'])) {
            throw new \InvalidArgumentException('Template should be provided for renderer.');
        }

        $this->setTemplate($initializationData['template']);

        $this->configuration = $configuration;
        $this->setData($configuration);
    }

    /**
     * @return void
     */
    private function tearDownConfiguration()
    {
        foreach (array_keys($this->configuration) as $key) {
            $this->unsetData($key);
            unset($this->configuration[$key]);
        }

        unset($this->_template);
    }

    /**
     * Processing block html after rendering
     *
     * @param   string $html
     * @return  string
     */
    protected function _afterToHtml($html)
    {
        $this->tearDownConfiguration();
        return parent::_afterToHtml($html);
    }
}
