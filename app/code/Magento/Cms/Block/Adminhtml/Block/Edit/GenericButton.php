<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Block\Adminhtml\Block\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Cms\Api\BlockRepositoryInterface;
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
     * @var BlockRepositoryInterface
     * @since 2.1.0
     */
    protected $blockRepository;

    /**
     * @param Context $context
     * @param BlockRepositoryInterface $blockRepository
     * @since 2.1.0
     */
    public function __construct(
        Context $context,
        BlockRepositoryInterface $blockRepository
    ) {
        $this->context = $context;
        $this->blockRepository = $blockRepository;
    }

    /**
     * Return CMS block ID
     *
     * @return int|null
     * @since 2.1.0
     */
    public function getBlockId()
    {
        try {
            return $this->blockRepository->getById(
                $this->context->getRequest()->getParam('block_id')
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
