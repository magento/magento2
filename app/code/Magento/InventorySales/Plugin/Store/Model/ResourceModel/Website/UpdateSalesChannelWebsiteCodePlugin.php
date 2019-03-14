<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Plugin\Store\Model\ResourceModel\Website;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Validation\ValidationException;
use Magento\InventorySales\Model\ResourceModel\GetWebsiteCodeByWebsiteId;
use Magento\InventorySales\Model\ResourceModel\UpdateSalesChannelWebsiteCode;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\ResourceModel\Website as WebsiteResourceModel;
use Magento\Store\Model\Website;

class UpdateSalesChannelWebsiteCodePlugin
{
    /**
     * @var UpdateSalesChannelWebsiteCode
     */
    private $updateSalesChannelWebsiteCode;

    /**
     * @var GetWebsiteCodeByWebsiteId
     */
    private $getWebsiteCodeByWebsiteId;

    /**
     * @param UpdateSalesChannelWebsiteCode $updateSalesChannelWebsiteCode
     * @param GetWebsiteCodeByWebsiteId $getWebsiteCodeByWebsiteId
     */
    public function __construct(
        UpdateSalesChannelWebsiteCode $updateSalesChannelWebsiteCode,
        GetWebsiteCodeByWebsiteId $getWebsiteCodeByWebsiteId
    ) {
        $this->updateSalesChannelWebsiteCode = $updateSalesChannelWebsiteCode;
        $this->getWebsiteCodeByWebsiteId = $getWebsiteCodeByWebsiteId;
    }

    /**
     * @param WebsiteResourceModel $subject
     * @param callable $proceed
     * @param Website|AbstractModel $website
     * @return WebsiteResourceModel
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     * @throws ValidationException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(
        WebsiteResourceModel $subject,
        callable $proceed,
        AbstractModel $website
    ) {
        $newCode = $website->getCode();
        $oldCode = null;

        if (null !== $website->getId()) {
            $oldCode = $this->getWebsiteCodeByWebsiteId->execute((int)$website->getId());
        }

        $result = $proceed($website);

        if (($oldCode !== null) && ($oldCode !== WebsiteInterface::ADMIN_CODE) && ($oldCode !== $newCode)) {
            $this->updateSalesChannelWebsiteCode->execute($oldCode, $newCode);
        }
        return $result;
    }
}
