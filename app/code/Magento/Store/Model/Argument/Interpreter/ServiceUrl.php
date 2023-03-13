<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Argument\Interpreter;

use InvalidArgumentException;
use Magento\Framework\Data\Argument\InterpreterInterface;
use Magento\Framework\Url;
use Magento\Store\Model\StoreRepository;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Interpreter that builds Service URL by input path and optional parameters
 */
class ServiceUrl implements InterpreterInterface
{
    /**
     * @param Url $url
     * @param StoreManagerInterface $storeManager
     * @param StoreRepository $storeRepository
     * @param string $service
     * @param string $version
     */
    public function __construct(
        private readonly Url $url,
        private readonly StoreManagerInterface $storeManager,
        private readonly StoreRepository $storeRepository,
        private $service = "rest",
        private $version = "V1"
    ) {
    }

    /**
     * Prepare rest suffix for url. For example rest/default/V1
     *
     * @return string
     */
    private function getServiceUrl()
    {
        $store = $this->storeRepository->getById($this->storeManager->getStore()->getId());
        return $this->url->getUrl(
            $this->service . "/" . $store->getCode() . "/" . $this->version
        );
    }

    /**
     * Compute and return effective value of an argument
     *
     * @param array $data
     * @return string
     * @throws InvalidArgumentException
     */
    public function evaluate(array $data)
    {
        if (!isset($data['path']) || empty($data['path'])) {
            throw new InvalidArgumentException('URL path is missing.');
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
