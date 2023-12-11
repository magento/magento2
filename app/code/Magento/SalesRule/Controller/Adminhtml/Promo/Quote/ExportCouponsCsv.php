<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SalesRule\Controller\Adminhtml\Promo\Quote;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;
use Magento\SalesRule\Controller\Adminhtml\Promo\Quote;
use Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons\Grid;
use Magento\Framework\View\Result\Layout;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Export Coupons to csv file
 *
 * Class \Magento\SalesRule\Controller\Adminhtml\Promo\Quote\ExportCouponsCsv
 */
class ExportCouponsCsv extends Quote implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * Export coupon codes as CSV file
     *
     * @return ResponseInterface|null
     */
    public function execute()
    {
        $this->_initRule();
        $fileName = 'coupon_codes.csv';
        /** @var Layout $resultLayout */
        $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
        $content = $resultLayout->getLayout()->createBlock(Grid::class)->getCsvFile();
        return $this->_fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    }
}
