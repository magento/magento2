<?php
/**
 * Copyright © Magento, Inc. All rights reserved. 
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Argument\Interpreter;

use Magento\Framework\Data\Argument\InterpreterInterface;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Store\Model\StoreRepository;

/**
 * Interpreter that builds Service URL by input path and optional parameters
 */
class ServiceUrl implements InterpreterInterface
{
    /**
     * @var \Magento\Framework\Url
     */
    private $url;

    /**
     * @var string
     */
    private $service;

    /**
     * @var StoreResolverInterface
     */
    private $storeResolver;

    /**
     * @var string
     */
    private $version;

    /**
     * @var StoreRepository
     */
    private $storeRepository;

    /**
     * @param \Magento\Framework\Url $url
     * @param StoreResolverInterface $storeResolver
     * @param StoreRepository $storeRepository
     * @param string $service
     * @param string $version
     */
    public function __construct(
        \Magento\Framework\Url $url,
        StoreResolverInterface $storeResolver,
        StoreRepository $storeRepository,
        $service = "rest",
        $version = "V1"
    ) {
        $this->url = $url;
        $this->service = $service;
        $this->storeResolver = $storeResolver;
        $this->version = $version;
        $this->storeRepository = $storeRepository;
    }

    /**
     * Prepare rest suffix for url. For example rest/default/V1
     *
     * @return string
     */
    private function getServiceUrl()
    {
        $store = $this->storeRepository->getById($this->storeResolver->getCurrentStoreId());
        return $this->url->getUrl(
            $this->service . "/" . $store->getCode() . "/" . $this->version
        );
    }

    /**
     * {@inheritdoc}
     * @return string
     * @throws \InvalidArgumentException
     */
    public function evaluate(array $data)
    {
        if (!isset($data['path']) || empty($data['path'])) {
            throw new \InvalidArgumentException('URL path is missing.');
        }

        if (isset($data['service'])) {
            $this->service = "rest";
        }

        if (isset($data["version"])) {
            $this->version = $data["version"];
        }

        return $this->getServiceUrl() . ltrim($data["path"], "/");
    }
}
