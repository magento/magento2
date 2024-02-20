<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Csp\Block\Sri;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Csp\Model\SubresourceIntegrityRepository;

/**
 * Block for Subresource Integrity hashes rendering.
 * @api
 */
class Hashes extends Template
{
    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @var SubresourceIntegrityRepository
     */
    private SubresourceIntegrityRepository $integrityRepository;

    /**
     * @param Context $context
     * @param array $data
     * @param SubresourceIntegrityRepository|null $integrityRepository
     * @param SerializerInterface|null $serializer
     */
    public function __construct(
        Context $context,
        array $data = [],
        ?SubresourceIntegrityRepository $integrityRepository = null,
        ?SerializerInterface $serializer = null
    ) {
        parent::__construct($context, $data);

        $this->integrityRepository = $integrityRepository ?: ObjectManager::getInstance()
            ->get(SubresourceIntegrityRepository::class);

        $this->serializer = $serializer ?: ObjectManager::getInstance()
            ->get(SerializerInterface::class);
    }

    /**
     * Retrieve serialized integrity hashes.
     *
     * @return string
     *
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function getSerialized(): string
    {
        $result = [];
        $assetIntegrity = $this->integrityRepository->getAll();

        foreach ($assetIntegrity as $integrity) {
            $result[$integrity->getUrl()] = $integrity->getHash();
        }

        return $this->serializer->serialize($result);
    }
}
