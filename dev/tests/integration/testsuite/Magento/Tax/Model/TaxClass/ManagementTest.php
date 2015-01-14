<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\TaxClass;

use Magento\Tax\Api\Data\TaxClassDataBuilder;
use Magento\Tax\Api\Data\TaxClassKeyInterface;
use Magento\Tax\Api\TaxClassManagementInterface;
use Magento\TestFramework\Helper\Bootstrap;

class ManagementTest extends \PHPUnit_Framework_TestCase
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
     * @var TaxClassDataBuilder
     */
    private $taxClassBuilder;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->taxClassRepository = $this->objectManager->create('Magento\Tax\Api\TaxClassRepositoryInterface');
        $this->taxClassManagement = $this->objectManager->create('Magento\Tax\Api\TaxClassManagementInterface');
        $this->taxClassBuilder = $this->objectManager->create('Magento\Tax\Api\Data\TaxClassDataBuilder');
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testGetTaxClassId()
    {
        $taxClassName = 'Get Me';
        $taxClassDataObject = $this->taxClassBuilder
            ->setClassName($taxClassName)
            ->setClassType(TaxClassManagementInterface::TYPE_CUSTOMER)
            ->create();
        $taxClassId = $this->taxClassRepository->save($taxClassDataObject);
        /** @var \Magento\Tax\Api\Data\TaxClassKeyDataBuilder $taxClassKeyBuilder */
        $taxClassKeyBuilder = $this->objectManager->create('Magento\Tax\Api\Data\TaxClassKeyDataBuilder');
        $taxClassKeyTypeId = $taxClassKeyBuilder->populateWithArray(
            [
                TaxClassKeyInterface::KEY_TYPE => TaxClassKeyInterface::TYPE_ID,
                TaxClassKeyInterface::KEY_VALUE => $taxClassId,
            ]
        )->create();
        $this->assertEquals(
            $taxClassId,
            $this->taxClassManagement->getTaxClassId($taxClassKeyTypeId, TaxClassManagementInterface::TYPE_CUSTOMER)
        );
        $taxClassKeyTypeName = $taxClassKeyBuilder->populateWithArray(
            [
                TaxClassKeyInterface::KEY_TYPE => TaxClassKeyInterface::TYPE_NAME,
                TaxClassKeyInterface::KEY_VALUE => $taxClassName,
            ]
        )->create();
        $this->assertEquals(
            $taxClassId,
            $this->taxClassManagement->getTaxClassId($taxClassKeyTypeId, TaxClassManagementInterface::TYPE_CUSTOMER)
        );
        $this->assertNull($this->taxClassManagement->getTaxClassId(null));
        $this->assertEquals(
            null,
            $this->taxClassManagement->getTaxClassId($taxClassKeyTypeName, TaxClassManagementInterface::TYPE_PRODUCT)
        );
    }
}
