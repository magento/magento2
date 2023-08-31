<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Model;

use Magento\Framework\View\Element\UiComponent\ContextInterface as UiComponentContext;

/**
 * Provides correct Content-Type header value for the Ui Component renderer based on the Accept Type of
 * the Component Context. Additional types may be added to the type map via di.xml configuration for this resolver.
 *
 * This is a workaround for the lacking Content-Type processing in
 * \Magento\Framework\View\Element\UiComponent\ContentType\ContentTypeInterface
 */
class UiComponentTypeResolver
{
    /**
     * @var string
     */
    const DEFAULT_CONTENT_TYPE = 'text/html';

    /**
     * @param array $uiComponentTypeMap
     */
    public function __construct(
        private readonly array $uiComponentTypeMap = []
    ) {
    }

    /**
     * @param UiComponentContext $componentContext
     * @return string
     */
    public function resolve(UiComponentContext $componentContext): string
    {
        $acceptType = $componentContext->getAcceptType();
        return $this->uiComponentTypeMap[$acceptType] ?? static::DEFAULT_CONTENT_TYPE;
    }
}
