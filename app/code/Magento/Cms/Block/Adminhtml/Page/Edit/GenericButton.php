<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Block\Adminhtml\Page\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class GenericButton
 * @since 2.1.0
 */
class GenericButton
{
    /**
     * @var Context
     * @since 2.1.0
     */
    protected $context;

    /**
     * @var PageRepositoryInterface
     * @since 2.1.0
     */
    protected $pageRepository;

    /**
     * @param Context $context
     * @param PageRepositoryInterface $pageRepository
     * @since 2.1.0
     */
    public function __construct(
        Context $context,
        PageRepositoryInterface $pageRepository
    ) {
        $this->context = $context;
        $this->pageRepository = $pageRepository;
    }

    /**
     * Return CMS page ID
     *
     * @return int|null
     * @since 2.1.0
     */
    public function getPageId()
    {
        try {
            return $this->pageRepository->getById(
                $this->context->getRequest()->getParam('page_id')
            )->getId();
        } catch (NoSuchEntityException $e) {
        }
        return null;
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     * @since 2.1.0
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
