<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Product\Frontend\Action;

use Magento\Catalog\Model\Product\ProductFrontendAction\Synchronizer;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Synchronizes Product Frontend Actions with database
 */
class Synchronize extends \Magento\Framework\App\Action\Action implements HttpPostActionInterface
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var Synchronizer
     */
    private $synchronizer;

    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @param Context $context
     * @param Synchronizer $synchronizer
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        Synchronizer $synchronizer,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->context = $context;
        $this->synchronizer = $synchronizer;
        $this->jsonFactory = $jsonFactory;
    }

    /**
     * @inheritDoc
     *
     * This is handle for synchronizing between frontend and backend product actions:
     *  - visit product page (recently_viewed)
     *  - compare products (recently_compared)
     *  - etc...
     * It comes in next format: [
     *  'type_id' => 'recently_*',
     *  'ids' => [
     *      'product_id' => "$id",
     *      'added_at' => "JS_TIMESTAMP"
     *  ]
     * ]
     */
    public function execute()
    {
        $resultJson = $this->jsonFactory->create();

        try {
            $productsData = $this->getRequest()->getParam('ids', []);
            $typeId = $this->getRequest()->getParam('type_id', null);
            $this->synchronizer->syncActions($productsData, $typeId);
        } catch (\Exception $e) {
            $resultJson->setStatusHeader(
                \Laminas\Http\Response::STATUS_CODE_400,
                \Laminas\Http\AbstractMessage::VERSION_11,
                'Bad Request'
            );
        }

        return $resultJson->setData([]);
    }
}
