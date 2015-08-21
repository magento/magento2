<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\Message\Renderer;

use Magento\Framework\Message\MessageInterface;
use Magento\Framework\View\Element\AbstractBlock;

class ComplexRenderer extends AbstractBlock implements RendererInterface
{
    /**
     * @var array
     */
    private $configuration;

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
