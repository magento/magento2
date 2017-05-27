<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace  Magento\Inventory\Block\Adminhtml\Edit\Button;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryApi\Api\SourceRepositoryInterface;

/**
 * Class AbstractButton
 */
abstract class AbstractButton
{

    /**
     * @var Context
     */
    private $context;

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * AbstractButton constructor.
     *
     * @param Context $context
     * @param SourceRepositoryInterface $sourceRepository
     */
    public function __construct(Context $context, SourceRepositoryInterface $sourceRepository)
    {
        $this->context = $context;
        $this->sourceRepository = $sourceRepository;
    }

    /**
     *
     * @return bool
     */
    protected function isEditMode()
    {
        return (bool)$this->getSourceId();
    }

    /**
     * Returns the Source Id.
     *
     * @return null | int
     */
    protected function getSourceId()
    {
        try {
            $sourceId = $this->context->getRequest()->getParam('id');
            return $this->sourceRepository->getById($sourceId)->getId();
        } catch (NoSuchEntityException $exception) {
        }
        return null;
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
