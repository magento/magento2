<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Block\Adminhtml\Bulk\Details;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Framework\UrlInterface;

/**
 * Back button configuration provider
 * @since 2.2.0
 */
class BackButton implements ButtonProviderInterface
{
    /**
     * URL builder
     *
     * @var UrlInterface
     * @since 2.2.0
     */
    private $urlBuilder;

    /**
     * @param UrlInterface $urlBuilder
     * @since 2.2.0
     */
    public function __construct(
        UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Retrieve button data
     *
     * @return array button configuration
     * @since 2.2.0
     */
    public function getButtonData()
    {
        return [
            'label' => __('Back'),
            'on_click' => sprintf("location.href = '%s';", $this->urlBuilder->getUrl('*/')),
            'class' => 'back',
            'sort_order' => 10
        ];
    }
}
