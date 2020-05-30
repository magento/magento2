<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Controller\Adminhtml\Product;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Review\Controller\Adminhtml\Product as ProductController;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Review\Model\RatingFactory;
use Magento\Review\Model\Review;
use Magento\Review\Model\ResourceModel\Review\Collection;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory;
use Magento\Review\Model\ReviewFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Mass Delete action.
 */
class MassDelete extends ProductController implements HttpPostActionInterface
{
    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param ReviewFactory $reviewFactory
     * @param RatingFactory $ratingFactory
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        ReviewFactory $reviewFactory,
        RatingFactory $ratingFactory,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct($context, $coreRegistry, $reviewFactory, $ratingFactory);
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Execute action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $reviewsIds = $this->getRequest()->getParam('reviews');
        if (!is_array($reviewsIds)) {
            $this->messageManager->addErrorMessage(__('Please select review(s).'));
        } else {
            try {
                foreach ($this->getCollection() as $model) {
                    $model->delete();
                }
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 record(s) have been deleted.', count($reviewsIds))
                );
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while deleting these records.'));
            }
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('review/*/' . $this->getRequest()->getParam('ret', 'index'));
        return $resultRedirect;
    }

    /**
     * @inheritdoc
     */
    protected function _isAllowed()
    {
        if (parent::_isAllowed()) {
            return true;
        }

        if (!$this->_authorization->isAllowed('Magento_Review::pending')) {
            return false;
        }

        foreach ($this->getCollection() as $model) {
            if ($model->getStatusId() != Review::STATUS_PENDING) {
                $this->messageManager->addErrorMessage(
                    __(
                        'You don’t have permission to perform this operation.'
                        . ' Selected reviews must be in Pending Status only.'
                    )
                );

                return false;
            }
        }

        return true;
    }

    /**
     * Returns requested collection.
     *
     * @return Collection
     */
    private function getCollection(): Collection
    {
        if ($this->collection === null) {
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter(
                'main_table.' . $collection->getResource()
                    ->getIdFieldName(),
                $this->getRequest()->getParam('reviews')
            );

            $this->collection = $collection;
        }

        return $this->collection;
    }
}
