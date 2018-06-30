<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CmsGraphQl\Model\Resolver\Cms;

use Magento\Cms\Api\Data\PageInterface as CmsPageInterface;
use Magento\Cms\Api\PageRepositoryInterface as CmsPageRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Webapi\ServiceOutputProcessor;

/**
 * Cms field data provider, used for GraphQL request processing.
 */
class CmsPageDataProvider
{
    /**
     * @var ServiceOutputProcessor
     */
    private $serviceOutputProcessor;

    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;

    /**
     * @var CmsPageRepositoryInterface
     */
    private $cmsPageRepository;

    /**
     * @param CmsPageRepositoryInterface $cmsPageRepository
     * @param ServiceOutputProcessor $serviceOutputProcessor
     * @param SerializerInterface $jsonSerializer
     */
    public function __construct(
        CmsPageRepositoryInterface $cmsPageRepository,
        ServiceOutputProcessor $serviceOutputProcessor,
        SerializerInterface $jsonSerializer
    ) {
        $this->serviceOutputProcessor = $serviceOutputProcessor;
        $this->jsonSerializer = $jsonSerializer;
        $this->cmsPageRepository = $cmsPageRepository;
    }

    /**
     * Get CMS page data by Id
     *
     * @param int $cmsPageId
     * @return array
     * @throws NoSuchEntityException|LocalizedException
     */
    public function getCmsPageById(int $cmsPageId) : array
    {
        try {
            $cmsPageModel = $this->cmsPageRepository->getById($cmsPageId);

            if (!$cmsPageModel->isActive()) {
                throw new NoSuchEntityException();
            }

        } catch (NoSuchEntityException $e) {
            // No error should be thrown, null result should be returned
            return [];
        }

        return $this->processCmsPage($cmsPageModel);
    }

    /**
     * Transform single CMS page data from object to in array format
     *
     * @param CmsPageInterface $cmsPageModel
     * @return array
     */
    private function processCmsPage(CmsPageInterface $cmsPageModel) : array
    {
        $cmsPageData = [
            'url_key' => $cmsPageModel->getIdentifier(),
            'page_title' => $cmsPageModel->getTitle(),
            'page_content' => $cmsPageModel->getContent(),
            'content_heading' => $cmsPageModel->getContentHeading(),
            'layout' => $cmsPageModel->getPageLayout(),
            'mate_title' => $cmsPageModel->getMetaTitle(),
            'mate_description' => $cmsPageModel->getMetaDescription(),
            'mate_keywords' => $cmsPageModel->getMetaKeywords(),
        ];

        if (isset($cmsPageData['extension_attributes'])) {
            $cmsPageData = array_merge($cmsPageData, $cmsPageData['extension_attributes']);
        }

        if (isset($cmsPageData['custom_attributes'])) {
            $cmsPageData = array_merge($cmsPageData, $this->processCustomAttributes($cmsPageData['custom_attributes']));
        }

        return $cmsPageData;
    }

    /**
     * @param array $customAttributes
     *
     * @return array
     */
    private function processCustomAttributes(array $customAttributes) : array
    {
        $processedCustomAttributes = [];

        foreach ($customAttributes as $customAttribute) {
            $isArray = false;
            $customAttributeCode = $customAttribute['attribute_code'];
            $customAttributeValue = $customAttribute['attribute_code'];

            if (is_array($customAttributeValue)) {
                $isArray = true;

                foreach ($customAttributeValue as $attributeValue) {
                    if (is_array($attributeValue)) {
                        $processedCustomAttributes[$customAttributeCode] = $this->jsonSerializer->serialize(
                            $customAttributeValue
                        );
                        continue;
                    }
                    $processedCustomAttributes[$customAttributeCode] = implode(',', $customAttributeValue);
                    continue;
                }
            }

            if ($isArray) {
                continue;
            }

            $processedCustomAttributes[$customAttributeCode] = $customAttributeValue;
        }

        return $processedCustomAttributes;
    }

}
