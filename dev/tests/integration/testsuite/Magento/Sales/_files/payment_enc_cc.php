<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteria;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\Payment\EncryptionUpdateTest;
use Magento\Framework\App\DeploymentConfig;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/order.php');

$objectManager = Bootstrap::getObjectManager();
/** @var OrderRepositoryInterface $orderRepository */
$orderRepository = $objectManager->create(OrderRepositoryInterface::class);
/** @var DeploymentConfig $deployConfig */
$deployConfig = $objectManager->get(DeploymentConfig::class);

/**
 * Creates an encrypted card number with the current crypt key using
 * a legacy cipher.
 */
// @codingStandardsIgnoreStart
$handle = @mcrypt_module_open(MCRYPT_RIJNDAEL_256, '', MCRYPT_MODE_CBC, '');
$initVectorSize = @mcrypt_enc_get_iv_size($handle);
$initVector = str_repeat("\0", $initVectorSize);
@mcrypt_generic_init($handle, $deployConfig->get('crypt/key'), $initVector);

$encCcNumber = @mcrypt_generic($handle, EncryptionUpdateTest::TEST_CC_NUMBER);

@mcrypt_generic_deinit($handle);
@mcrypt_module_close($handle);
// @codingStandardsIgnoreEnd

/** @var SearchCriteria $searchCriteria */
$searchCriteria = $objectManager->get(SearchCriteriaBuilder::class)
    ->addFilter('increment_id', '100000001')
    ->create();

$orders = $orderRepository->getList($searchCriteria)->getItems();
$order = array_pop($orders);

/** @var \Magento\Sales\Model\ResourceModel\Order\Payment $resource */
$resource = $objectManager->create(\Magento\Sales\Model\ResourceModel\Order\Payment::class);
$resource->getConnection()->insert(
    $resource->getMainTable(),
    [
        'parent_id' => $order->getId(),
        'cc_number_enc' => '0:2:' . base64_encode($encCcNumber),
    ]
);
