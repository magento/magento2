<?php

$date = new \DateTime();
$startDate = $date->sub(new \DateInterval('P' . 2 . 'D'))->format('Y-m-d H:i:s');

foreach ($skus as $sku) {
    /** @var \Magento\Catalog\Model\ResourceModel\Product $productResource */
    $productResource = $this->objectManager->create('Magento\Catalog\Model\ResourceModel\Product');

    $product = $this->objectManager->create('Magento\Catalog\Model\Product');

    $productId = $productResource->getIdBySku($sku);

    $product->load($productId);

    $stagingData = [
        'mode' => 'save',
        'update_id' => null,
        'name' => 'New update ' . $startDate,
        'description' => 'New update',
        'start_time' => $startDate,
        //'start_time' => '2016-02-22 23:56:48',
        'end_time' => null,
        'select_id' => null
    ];

    /** @var \Magento\Staging\Model\UpdateFactory $updateFactory */
    $updateFactory = $this->objectManager->get('Magento\Staging\Model\UpdateFactory');
    /** @var \Magento\Framework\Model\Entity\MetadataPool $metadataPool */
    $metadataPool = $this->objectManager->get('Magento\Framework\Model\Entity\MetadataPool');

    /** @var \Magento\Staging\Model\Update $update */
    $update = $updateFactory->create();
    /** @var \Magento\Framework\Model\Entity\EntityHydrator $hydrator */
    $hydrator = $metadataPool->getHydrator(UpdateInterface::class);
    $hydrator->hydrate($update, $stagingData);
    $update->setIsCampaign(false);
    $update->setId(strtotime($update->getStartTime()));
    $update->isObjectNew(true);

    /** @var \Magento\Staging\Model\ResourceModel\Update $resourceUpdate */
    $resourceUpdate = $this->objectManager->get('Magento\Staging\Model\ResourceModel\Update');
    $resourceUpdate->save($update);

    /** @var \Magento\Staging\Model\VersionManager $versionManager */
    $versionManager = $this->objectManager->get('Magento\Staging\Model\VersionManager');
    $oldVersion = $versionManager->getCurrentVersion();
    $versionManager->setCurrentVersionId($update->getId());

    $product->unsRowId();
    $product->setName('My Product ' . $startDate);

    /** @var \Magento\Framework\Model\EntityManager $entityManager */
    $entityManager = $this->objectManager->get('Magento\Framework\Model\EntityManager');
    $entityManager->save(\Magento\Catalog\Api\Data\ProductInterface::class, $product);

    $versionManager->setCurrentVersionId($oldVersion->getId());
}
