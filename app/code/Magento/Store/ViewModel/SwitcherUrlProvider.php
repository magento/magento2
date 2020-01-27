<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Store\ViewModel;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Provides target store redirect url.
 */
class SwitcherUrlProvider implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param EncoderInterface $encoder
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        EncoderInterface $encoder,
        StoreManagerInterface $storeManager,
        UrlInterface $urlBuilder
    ) {
        $this->encoder = $encoder;
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Returns target store redirect url.
     *
     * @param Store $store
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getTargetStoreRedirectUrl(Store $store): string
    {
        return $this->urlBuilder->getUrl(
            'stores/store/redirect',
            [
                '___store' => $store->getCode(),
                '___from_store' => $this->storeManager->getStore()->getCode(),
                ActionInterface::PARAM_NAME_URL_ENCODED => $this->encoder->encode(
                    $store->getCurrentUrl(false)
                ),
            ]
        );
    }
}
