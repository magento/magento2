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

/**
 * Export coupons to xml file
 *
 * Class \Magento\SalesRule\Controller\Adminhtml\Promo\Quote\ExportCouponsXml
 */
class ExportCouponsXml extends Quote implements HttpGetActionInterface
{
    /**
     * Export coupon codes as excel xml file
     *
     * @return ResponseInterface|null
     */
    public function execute()
    {
        $this->_initRule();
        $fileName = 'coupon_codes.xml';
        /** @var Layout $resultLayout */
        $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
        $content = $resultLayout->getLayout()->createBlock(Grid::class)->getExcelFile($fileName);
        return $this->_fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    }
}
