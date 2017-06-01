<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Model\Exception;

use Magento\Framework\View\Element\Message\Renderer\BlockRenderer\Template;

class Renderer implements \Magento\Framework\Exception\RendererInterface
{
    /**
     * @var Template
     */
    private $renderer;

    /**
     * Template file
     *
     * @var string
     */
    private $template;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;


    /**
     * @param Template $renderer
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param string|null $template
     */
    public function __construct(
        Template $renderer,
        \Magento\Framework\UrlInterface $urlBuilder,
        $template = 'Magento_UrlRewrite::messages/url_duplicate_message.phtml'
    ) {
        $this->renderer = $renderer;
        $this->urlBuilder = $urlBuilder;
        $this->template = $template;
    }

    /**
     * Renders an exception
     *
     * @param \Exception $exception
     * @return string
     */
    public function render(\Exception $exception)
    {
        $generatedUrls = [];
        foreach ($exception->getUrls() as $id => $url) {
            $adminEditUrl = $this->urlBuilder->getUrl(
                'adminhtml/url_rewrite/edit',
                ['id' => $id]
            );
            $generatedUrls[$adminEditUrl] = $url->getRequestPath();
        }
        $this->renderer->setTemplate($this->template);
        $this->renderer->setData('urls', $generatedUrls);
        return  $this->renderer->toHtml();
    }
}
