<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Email\Model\ResourceModel\Template as TemplateResource;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var TemplateResource $templateResource */
$templateResource = $objectManager->get(TemplateResource::class);
/** @var CollectionFactory $templateCollectionFactory */
$templateCollectionFactory = $objectManager->get(CollectionFactory::class);

$collection = $templateCollectionFactory->create();
$template = $collection->addFieldToFilter('template_code', 'customer_password_email_template')->getFirstItem();

if ($template->getId()) {
    $templateResource->delete($template);
}
