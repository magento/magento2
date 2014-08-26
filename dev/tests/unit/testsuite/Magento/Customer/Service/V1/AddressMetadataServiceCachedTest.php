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

namespace Magento\Customer\Service\V1;

use Magento\Framework\Exception\NoSuchEntityException;

class AddressMetadataServiceCachedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Customer\Service\V1\AddressMetadataService
     */
    private $customerMetadataServiceMock;

    /**
     * @var \Magento\Customer\Service\V1\AddressMetadataServiceCached
     */
    private $cachedMetadataService;

    public function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->customerMetadataServiceMock = $this->getMockBuilder(
            'Magento\Customer\Service\V1\AddressMetadataService'
        )->setMethods([
            'getAttributes',
            'getAttributeMetadata',
            'getAllAttributesMetadata',
            'getCustomAttributesMetadata'
        ])->disableOriginalConstructor()->getMock();

        $this->cachedMetadataService = $this->objectManager->getObject(
            'Magento\Customer\Service\V1\AddressMetadataServiceCached',
            ['metadataService' => $this->customerMetadataServiceMock]
        );
    }

    public function testGetAttributes()
    {
        $formCode = 'f';
        $value = 'v';

        $this->customerMetadataServiceMock->expects($this->once())
            ->method('getAttributes')
            ->with($formCode)
            ->will($this->returnValue($value));

        for ($c = 0; $c < 10; $c++) {
            $actualValue = $this->cachedMetadataService->getAttributes($formCode);
            $this->assertEquals($value, $actualValue);
        }
    }

    public function testGetAttributeMetadata()
    {
        $attributeCode = 'a';
        $value = 'v';

        $this->customerMetadataServiceMock->expects($this->once())
            ->method('getAttributeMetadata')
            ->with($attributeCode)
            ->will($this->returnValue($value));

        for ($c = 0; $c < 10; $c++) {
            $actualValue = $this->cachedMetadataService->getAttributeMetadata($attributeCode);
            $this->assertEquals($value, $actualValue);
        }
    }

    public function testGetAttributeMetadataWithException()
    {
        $attributeCode = 'a';

        $this->customerMetadataServiceMock->expects($this->exactly(10))
            ->method('getAttributeMetadata')
            ->with($attributeCode)
            ->will($this->throwException(new \Magento\Framework\Exception\NoSuchEntityException()));

        for ($c = 0; $c < 10; $c++) {
            $exceptionThrown = false;
            try {
                $this->cachedMetadataService->getAttributeMetadata($attributeCode);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $exceptionThrown = true;
            }
            $this->assertTrue($exceptionThrown);
        }
    }

    public function testGetAllAttributesMetadata()
    {
        $value = 'v';

        $this->customerMetadataServiceMock->expects($this->once())
            ->method('getAllAttributesMetadata')
            ->will($this->returnValue($value));

        for ($c = 0; $c < 10; $c++) {
            $actualValue = $this->cachedMetadataService->getAllAttributesMetadata();
            $this->assertEquals($value, $actualValue);
        }
    }

    public function testGetCustomAttributesMetadata()
    {
        $value = 'v';

        $this->customerMetadataServiceMock->expects($this->once())
            ->method('getCustomAttributesMetadata')
            ->will($this->returnValue($value));

        for ($c = 0; $c < 10; $c++) {
            $actualValue = $this->cachedMetadataService->getCustomAttributesMetadata();
            $this->assertEquals($value, $actualValue);
        }
    }
}
