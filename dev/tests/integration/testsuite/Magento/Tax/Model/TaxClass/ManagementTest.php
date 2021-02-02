<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\TaxClass;

use Magento\Tax\Api\Data\TaxClassInterfaceFactory;
use Magento\Tax\Api\Data\TaxClassKeyInterface;
use Magento\Tax\Api\TaxClassManagementInterface;
use Magento\Tax\Model\TaxClass\Key;
use Magento\TestFramework\Helper\Bootstrap;

class ManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Repository
     */
    private $taxClassRepository;

    /**
     * @var Management
     */
    private $taxClassManagement;

    /**
     * @var TaxClassInterfaceFactory
     */
    private $taxClassFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $dataObjectHelper;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->taxClassRepository = $this->objectManager->create(\Magento\Tax\Api\TaxClassRepositoryInterface::class);
        $this->taxClassManagement = $this->objectManager->create(\Magento\Tax\Api\TaxClassManagementInterface::class);
        $this->taxClassFactory = $this->objectManager->create(\Magento\Tax\Api\Data\TaxClassInterfaceFactory::class);
        $this->dataObjectHelper = $this->objectManager->create(\Magento\Framework\Api\DataObjectHelper::class);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testGetTaxClassId()
    {
        $taxClassName = 'Get Me';
        $taxClassDataObject = $this->taxClassFactory->create();
        $taxClassDataObject->setClassName($taxClassName)
            ->setClassType(TaxClassManagementInterface::TYPE_CUSTOMER);

        $taxClassId = $this->taxClassRepository->save($taxClassDataObject);
        /** @var \Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory $taxClassKeyFactory */
        $taxClassKeyFactory = $this->objectManager->create(\Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory::class);
        $taxClassKeyTypeId = $taxClassKeyFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $taxClassKeyTypeId,
            [
                Key::KEY_TYPE => TaxClassKeyInterface::TYPE_ID,
                Key::KEY_VALUE => $taxClassId,
            ],
            \Magento\Tax\Api\Data\TaxClassKeyInterface::class
        );
        $this->assertEquals(
            $taxClassId,
            $this->taxClassManagement->getTaxClassId($taxClassKeyTypeId, TaxClassManagementInterface::TYPE_CUSTOMER)
        );
        $taxClassKeyTypeName = $taxClassKeyFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $taxClassKeyTypeName,
            [
                Key::KEY_TYPE => TaxClassKeyInterface::TYPE_NAME,
                Key::KEY_VALUE => $taxClassName,
            ],
            \Magento\Tax\Api\Data\TaxClassKeyInterface::class
        );
        $this->assertEquals(
            $taxClassId,
            $this->taxClassManagement->getTaxClassId($taxClassKeyTypeId, TaxClassManagementInterface::TYPE_CUSTOMER)
        );
        $this->assertNull($this->taxClassManagement->getTaxClassId(null));
        $this->assertNull(
            $this->taxClassManagement->getTaxClassId($taxClassKeyTypeName, TaxClassManagementInterface::TYPE_PRODUCT)
        );
    }
}
