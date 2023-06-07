<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryCmsUi\Controller\Adminhtml\Block;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Controller to get selected block for ui-select component
 */
class GetSelected extends Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Cms::block';

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var BlockRepositoryInterface
     */
    private $blockRepository;

    /**
     * @param JsonFactory $resultFactory
     * @param BlockRepositoryInterface $blockRepository
     * @param Context $context
     */
    public function __construct(
        JsonFactory $resultFactory,
        BlockRepositoryInterface $blockRepository,
        Context $context
    ) {
        $this->resultJsonFactory = $resultFactory;
        $this->blockRepository = $blockRepository;
        parent::__construct($context);
    }

    /**
     * Return selected blocks options.
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $options = [];
        $blockIds = $this->getRequest()->getParam('ids');

        if (!is_array($blockIds)) {
            return $this->resultJsonFactory->create()->setData('parameter ids must be type of array');
        }
        foreach ($blockIds as $id) {
            try {
                $block = $this->blockRepository->getById($id);
                $options[] = [
                    'value' => $block->getId(),
                    'label' => $block->getTitle(),
                    'is_active' => $block->isActive(),
                    'optgroup' => false
                ];
            } catch (\Exception $e) {
                continue;
            }
        }

        return $this->resultJsonFactory->create()->setData($options);
    }
}
