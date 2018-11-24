<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\Sample;

use Magento\Downloadable\Api\SampleRepositoryInterface as SampleRepository;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\App\RequestInterface;
/**
 * Class ReadHandler
 */
class ReadHandler implements ExtensionInterface
{
    /**
     * @var SampleRepository
     */
    protected $sampleRepository;

    /**
     * @param SampleRepository $sampleRepository
     */

    /**
     * @var RequestInterface
     */
    protected $request;

    public function __construct(SampleRepository $sampleRepository,RequestInterface $request)
    {
        $this->sampleRepository = $sampleRepository;
        $this->request = $request;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return \Magento\Catalog\Api\Data\ProductInterface|object
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        /** @var $entity \Magento\Catalog\Api\Data\ProductInterface */
        if ($entity->getTypeId() != \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE) {
            return $entity;
        }
        $entityExtension = $entity->getExtensionAttributes();
        $samples = $this->sampleRepository->getSamplesByProduct($entity);

        $downloadable = $this->request->getPost('downloadable');
        
        if ($samples && isset($downloadable['sample']) && is_array($downloadable['sample'])) {
            $entityExtension->setDownloadableProductSamples($samples);
        }
        $entity->setExtensionAttributes($entityExtension);
        return $entity;
    }
}
