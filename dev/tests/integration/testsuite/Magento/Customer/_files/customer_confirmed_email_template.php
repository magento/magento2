<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Email\Model\ResourceModel\Template as TemplateResource;
use Magento\Framework\Mail\TemplateInterface;
use Magento\Framework\Mail\TemplateInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var TemplateResource $templateResource */
$templateResource = $objectManager->get(TemplateResource::class);
/** @var TemplateInterfaceFactory $templateFactory */
$templateFactory = $objectManager->get(TemplateInterfaceFactory::class);
/** @var TemplateInterface $template */
$template = $templateFactory->create();

$content = <<<HTML
{{template config_path="design/email/header_template"}}
<p>{{trans "Customer create account email confirmed template"}}</p>
{{template config_path="design/email/footer_template"}}
HTML;

$template->setTemplateCode('customer_create_account_email_confirmed_template')
    ->setTemplateText($content)
    ->setTemplateType(TemplateInterface::TYPE_HTML);
$templateResource->save($template);
