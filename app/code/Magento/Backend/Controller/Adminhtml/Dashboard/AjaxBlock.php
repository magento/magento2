<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Controller\Adminhtml\Dashboard;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Block\Dashboard\Totals;
use Magento\Backend\Controller\Adminhtml\Dashboard;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\LayoutFactory;

/**
 * Class used to retrieve content of dashboard totals block via ajax
 */
class AjaxBlock extends Dashboard implements HttpPostActionInterface
{
    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @param Context $context
     * @param RawFactory $resultRawFactory
     * @param LayoutFactory $layoutFactory
     */
    public function __construct(
        Context $context,
        RawFactory $resultRawFactory,
        LayoutFactory $layoutFactory
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory = $layoutFactory;
    }

    /**
     * Retrieve block content via ajax
     *
     * @return Raw
     */
    public function execute()
    {
        $output = '';
        $blockTab = $this->getRequest()->getParam('block');

        if ($blockTab === 'totals') {
            $output = $this->layoutFactory->create()
                ->createBlock(Totals::class)
                ->toHtml();
        }

        /** @var Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        return $resultRaw->setContents($output);
    }
}
