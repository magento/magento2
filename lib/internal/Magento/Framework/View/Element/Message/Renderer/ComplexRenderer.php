<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\Message\Renderer;

use Magento\Framework\Message\MessageInterface;
use Magento\Framework\View\Element\Template;

class ComplexRenderer extends Template implements RendererInterface
{
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
     * @return string
     */
    public function render(MessageInterface $message)
    {
        $this->setUpConfiguration($message->getData());
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
     * @return void
     */
    private function setUpConfiguration(array $configuration)
    {
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

    /**
     * Initialize renderer with state
     *
     * @param array $data
     * @return void
     */
    public function initialize(array $data)
    {
        if (!isset($data['template'])) {
            throw new \InvalidArgumentException('Template should be provided for renderer.');
        }

        $this->setTemplate($data['template']);
    }
}
