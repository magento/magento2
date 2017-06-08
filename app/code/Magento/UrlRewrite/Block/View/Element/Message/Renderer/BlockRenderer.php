<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Block\View\Element\Message\Renderer;

use Magento\Framework\Message\MessageInterface;
use Magento\Framework\View\Element\Message\Renderer\BlockRenderer\Template;
use Magento\Framework\View\Element\Message\Renderer\RendererInterface;

class BlockRenderer implements RendererInterface
{
    /**
     * complex_renderer
     */
    const CODE = 'url_duplicate_block_renderer';

    /**
     * @var array
     */
    private $configuration;

    /**
     * @var Template
     */
    private $template;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @param Template $template
     * @param \Magento\Framework\UrlInterface $urlBuilder
     */
    public function __construct(
        Template $template,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->template = $template;
        $this->urlBuilder = $urlBuilder;
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
        $data = $message->getData();
        if (isset($data['urls'])) {
            /** @var \Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException $exception */
            $generatedUrls = [];
            if (is_array($data['urls'])) {
                foreach ($data['urls'] as $id => $url) {
                    $adminEditUrl = $this->urlBuilder->getUrl(
                        'adminhtml/url_rewrite/edit',
                        ['id' => $id]
                    );
                    $generatedUrls[$adminEditUrl] = $url['request_path'];
                }
                $data = ['urls' => $generatedUrls];
            }
        }

        $this->setUpConfiguration($data, $initializationData);

        $result = $this->template->toHtml();

        $this->tearDownConfiguration();

        return $result;
    }

    /**
     * @param array $configuration
     * @param array $initializationData
     * @return void
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
