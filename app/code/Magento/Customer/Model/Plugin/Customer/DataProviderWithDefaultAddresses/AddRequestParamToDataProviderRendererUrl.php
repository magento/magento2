<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Plugin\Customer\DataProviderWithDefaultAddresses;

use Magento\Backend\Model\UrlInterface;
use Magento\Customer\Model\Customer\DataProviderWithDefaultAddresses;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class AddRequestParamToDataProviderRendererUrl
{
    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * AddRequestParamToDataProviderUpdateUrl constructor.
     *
     * @param UrlInterface $url
     * @param ArrayManager $arrayManager
     * @param RequestInterface $request
     */
    public function __construct(
        UrlInterface $url,
        ArrayManager $arrayManager,
        RequestInterface $request
    ) {
        $this->url = $url;
        $this->request = $request;
        $this->arrayManager = $arrayManager;
    }

    /**
     * Modify provider configuration and return meta
     *
     * @param DataProviderWithDefaultAddresses $subject
     * @param array $meta
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetMeta(DataProviderWithDefaultAddresses $subject, array $meta)
    {
        $meta = $this->modifyProviderRenderUrl($meta);
        return $meta;
    }

    /**
     * Add parent id into renderer url request
     *
     * @param array $meta
     * @return array
     */
    private function modifyProviderRenderUrl(array $meta)
    {
        $meta = $this->arrayManager->set(
            'address',
            $meta,
            [
                'children' => [
                    'customer_address_listing' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'render_url' => $this->url->getUrl(
                                        'mui/index/render',
                                        [
                                            'parent_id' => $this->request->getParam('id'),
                                            /*
                                             * Set empty filters to prevent load filters from bookmark
                                             * and sharing between customers
                                             * */
                                            ContextInterface::FILTER_VAR => 0
                                        ]
                                    )
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        );
        return $meta;
    }
}
