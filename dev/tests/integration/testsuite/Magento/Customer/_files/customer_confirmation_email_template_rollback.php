<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Email\Model\ResourceModel\Template as TemplateResource;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory;
use Magento\Email\Model\ResourceModel\Template\Collection;
use Magento\Framework\Mail\TemplateInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var TemplateResource $templateResource */
$templateResource = $objectManager->get(TemplateResource::class);
/** @var Collection $collection */
$collection = $objectManager->get(CollectionFactory::class)->create();
/** @var TemplateInterface $template */
$template = $collection
    ->addFieldToFilter('template_code', 'customer_create_account_email_confirmation_template')
    ->getFirstItem();
if ($template->getId()) {
    $templateResource->delete($template);
}
