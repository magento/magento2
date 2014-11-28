<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Tax\Model\TaxClass;

use Magento\Tax\Api\Data\TaxClassDataBuilder;
use Magento\Tax\Api\Data\TaxClassKeyInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Tax\Api\TaxClassManagementInterface;

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
