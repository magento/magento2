<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

$template = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Newsletter\Model\Template');
$template->setTemplateCode(
    'fixture_tpl'
)->setTemplateText(
    '<p>Follow this link to unsubscribe</p>
<!-- This tag is for unsubscribe link  -->
<p><a href="{{var subscriber.getUnsubscriptionLink()}}">{{var subscriber.getUnsubscriptionLink()}}</a></p>'
)->setTemplateType(
    2
)->setTemplateSubject(
    'Subject'
)->setTemplateSenderName(
    'CustomerSupport'
)->setTemplateSenderEmail(
    'support@example.com'
)->setTemplateActual(
    1
)->save();
