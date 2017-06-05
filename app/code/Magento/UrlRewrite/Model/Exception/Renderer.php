<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Model\Exception;

class Renderer implements \Magento\Framework\Exception\RendererInterface
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var string
     */
    private $identifier;

    /**
     * @param string
     * @param \Magento\Framework\UrlInterface $urlBuilder
     */
    public function __construct(
        $identifier,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->identifier = $identifier;
    }

    /**
     * Renders an exception
     *
     * @param \Exception $exception
     * @return array
     */
    public function render(\Exception $exception)
    {
        $generatedUrls = [];
        if (is_array($exception->getUrls())) {
            foreach ($exception->getUrls() as $id => $url) {
                /** @var  $url */
                $adminEditUrl = $this->urlBuilder->getUrl(
                    'adminhtml/url_rewrite/edit',
                    ['id' => $id]
                );
                $generatedUrls[$adminEditUrl] = $url->getRequestPath();
            }
            return ['urls' => $generatedUrls];
        }
        return [];
    }

    /**
     * Returns the identifier towards which the renderer return data is intended
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }
}
