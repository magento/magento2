<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Module\Manager;
use Magento\Framework\Stdlib\DateTime;
use Magento\Security\Model\PasswordResetRequestEvent;
use Magento\Security\Model\PasswordResetRequestEventFactory;
use Magento\Security\Model\ResourceModel\PasswordResetRequestEvent as PasswordResetRequestEventResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

$objectManager = Bootstrap::getObjectManager();
/** @var Manager $moduleManager */
$moduleManager = $objectManager->get(Manager::class);
//This check is needed because Magento_Security independent of Magento_Customer
if ($moduleManager->isEnabled('Magento_Customer')) {
    Resolver::getInstance()->requireDataFixture('Magento/Customer/_files/customer.php');

    /** @var PasswordResetRequestEventFactory $passwordResetRequestEventFactory */
    $passwordResetRequestEventFactory = $objectManager->get(PasswordResetRequestEventFactory::class);
    /** @var PasswordResetRequestEventResource $passwordResetRequestEventResource */
    $passwordResetRequestEventResource = $objectManager->get(PasswordResetRequestEventResource::class);

    $dateTime = new DateTimeImmutable();
    $passwordResetRequestEvent = $passwordResetRequestEventFactory->create();
    $passwordResetRequestEvent->setRequestType(PasswordResetRequestEvent::CUSTOMER_PASSWORD_RESET_REQUEST)
        ->setAccountReference('customer@example.com')
        ->setIp(ip2long('127.0.0.1'))
        ->setCreatedAt($dateTime->modify('-5 minutes')->format(DateTime::DATETIME_PHP_FORMAT))->save();
    $passwordResetRequestEventResource->save($passwordResetRequestEvent);
}
