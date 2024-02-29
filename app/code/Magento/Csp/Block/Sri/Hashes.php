<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Csp\Block\Sri;

use Magento\Framework\UrlInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Csp\Model\SubresourceIntegrityRepositoryPool;

/**
 * Block for Subresource Integrity hashes rendering.
 *
 * @api
 */
class Hashes extends Template
{
    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @var SubresourceIntegrityRepositoryPool
     */
    private SubresourceIntegrityRepositoryPool $integrityRepositoryPool;

    /**
     * @param Context $context
     * @param array $data
     * @param SubresourceIntegrityRepositoryPool|null $integrityRepositoryPool
     * @param SerializerInterface|null $serializer
     */
    public function __construct(
        Context $context,
        array $data = [],
        ?SubresourceIntegrityRepositoryPool $integrityRepositoryPool = null,
        ?SerializerInterface $serializer = null
    ) {
        parent::__construct($context, $data);

        $this->integrityRepositoryPool = $integrityRepositoryPool ?: ObjectManager::getInstance()
            ->get(SubresourceIntegrityRepositoryPool::class);

        $this->serializer = $serializer ?: ObjectManager::getInstance()
            ->get(SerializerInterface::class);
    }

    /**
     * Retrieves integrity hashes in serialized format.
     *
     * @throws LocalizedException
     *
     * @return string
     */
    public function getSerialized(): string
    {
        $result = [];

        $integrityRepository = $this->integrityRepositoryPool->get(
            $this->_appState->getAreaCode()
        );

        $baseUrl = $this->_urlBuilder->getBaseUrl(
            ["_type" => UrlInterface::URL_TYPE_STATIC]
        );

        foreach ($integrityRepository->getAll() as $integrity) {
            $url = $baseUrl . $integrity->getPath();

            $result[$url] = $integrity->getHash();
        }

        return $this->serializer->serialize($result);
    }
}
