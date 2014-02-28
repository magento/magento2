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

namespace Magento\Customer\Service\V1\Dto;

class CustomerBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Customer\Service\V1\Dto\CustomerBuilder */
    protected $_customerBuilder;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_customerBuilder = $objectManager->getObject('Magento\Customer\Service\V1\Dto\CustomerBuilder');
        parent::setUp();
    }

    public function testMergeDtos()
    {
        $firstname1 = 'Firstname1';
        $lastnam1 = 'Lastname1';
        $email1 = 'email1@example.com';
        $firstDto = $this->_customerBuilder
            ->setFirstname($firstname1)
            ->setLastname($lastnam1)
            ->setEmail($email1)
            ->create();

        $lastname2 = 'Lastname2';
        $middlename2 = 'Middlename2';
        $secondDto = $this->_customerBuilder
            ->setLastname($lastname2)
            ->setMiddlename($middlename2)
            ->create();

        $mergedDto = $this->_customerBuilder->mergeDtos($firstDto, $secondDto);
        $this->assertNotSame($firstDto, $mergedDto, 'A new object must be created for merged DTO.');
        $this->assertNotSame($secondDto, $mergedDto, 'A new object must be created for merged DTO.');
        $expectedDtoData = [
            'firstname' => $firstname1,
            'lastname' => $lastname2,
            'middlename' => $middlename2,
            'email' => $email1
        ];
        $this->assertEquals($expectedDtoData, $mergedDto->__toArray(), 'DTOs were merged incorrectly.');
    }

    public function testMergeDtoWitArray()
    {
        $firstname1 = 'Firstname1';
        $lastnam1 = 'Lastname1';
        $email1 = 'email1@example.com';
        $firstDto = $this->_customerBuilder
            ->setFirstname($firstname1)
            ->setLastname($lastnam1)
            ->setEmail($email1)
            ->create();

        $lastname2 = 'Lastname2';
        $middlename2 = 'Middlename2';
        $dataForMerge = ['lastname' => $lastname2, 'middlename' => $middlename2];

        $mergedDto = $this->_customerBuilder->mergeDtoWithArray($firstDto, $dataForMerge);
        $this->assertNotSame($firstDto, $mergedDto, 'A new object must be created for merged DTO.');
        $expectedDtoData = [
            'firstname' => $firstname1,
            'lastname' => $lastname2,
            'middlename' => $middlename2,
            'email' => $email1
        ];
        $this->assertEquals($expectedDtoData, $mergedDto->__toArray(), 'DTO with array were merged incorrectly.');
    }
}
