<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Email\Model\ResourceModel\Template as TemplateResource;
use Magento\Email\Model\TemplateFactory;
use Magento\Email\Model\Template;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var StoreRepositoryInterface $storeRepository */
$storeRepository = $objectManager->get(StoreRepositoryInterface::class);
$store = $storeRepository->get('default');
/** @var TemplateResource $templateResource */
$templateResource = $objectManager->get(TemplateResource::class);
/** @var TemplateFactory $template */
$templateFactory = $objectManager->create(TemplateFactory::class);

$template = $templateFactory->create();
$template->setTemplateCode('customer_password_email_template')
    ->setTemplateText(file_get_contents(__DIR__ . '/custom_template.html'))
    ->setTemplateType(Template::TYPE_HTML);
$templateResource->save($template);
