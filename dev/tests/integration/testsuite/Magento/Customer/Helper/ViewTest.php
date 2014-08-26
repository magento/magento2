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
namespace Magento\Customer\Helper;

use Magento\TestFramework\Helper\Bootstrap;

class ViewTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Customer\Helper\View */
    protected $_helper;

    /** @var \Magento\Customer\Service\V1\CustomerMetadataServiceInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_customerMetadataService;

    protected function setUp()
    {
        $this->_customerMetadataService = $this->getMock(
            'Magento\Customer\Service\V1\CustomerMetadataServiceInterface'
        );
        $this->_helper = Bootstrap::getObjectManager()->create(
            'Magento\Customer\Helper\View',
            array('customerMetadataService' => $this->_customerMetadataService)
        );
        parent::setUp();
    }

    /**
     * @param \Magento\Customer\Service\V1\Data\Customer $customerData
     * @param string $expectedCustomerName
     * @param bool $isPrefixAllowed
     * @param bool $isMiddleNameAllowed
     * @param bool $isSuffixAllowed
     * @dataProvider getCustomerNameDataProvider
     */
    public function testGetCustomerName(
        $customerData,
        $expectedCustomerName,
        $isPrefixAllowed = false,
        $isMiddleNameAllowed = false,
        $isSuffixAllowed = false
    ) {

        $visibleAttribute = $this->getMock(
            'Magento\Customer\Service\V1\Data\Eav\AttributeMetadata',
            array(),
            array(),
            '',
            false
        );
        $visibleAttribute->expects($this->any())->method('isVisible')->will($this->returnValue(true));

        $invisibleAttribute = $this->getMock(
            'Magento\Customer\Service\V1\Data\Eav\AttributeMetadata',
            array(),
            array(),
            '',
            false
        );
        $invisibleAttribute->expects($this->any())->method('isVisible')->will($this->returnValue(false));

        $this->_customerMetadataService->expects(
            $this->any()
        )->method(
            'getAttributeMetadata'
        )->will(
            $this->returnValueMap(
                array(
                    array('prefix', $isPrefixAllowed ? $visibleAttribute : $invisibleAttribute),
                    array('middlename', $isMiddleNameAllowed ? $visibleAttribute : $invisibleAttribute),
                    array('suffix', $isSuffixAllowed ? $visibleAttribute : $invisibleAttribute)
                )
            )
        );

        $this->assertEquals(
            $expectedCustomerName,
            $this->_helper->getCustomerName($customerData),
            'Full customer name is invalid'
        );
    }

    public function getCustomerNameDataProvider()
    {
        /** @var \Magento\Customer\Service\V1\Data\CustomerBuilder $customerBuilder */
        $customerBuilder = Bootstrap::getObjectManager()->create('Magento\Customer\Service\V1\Data\CustomerBuilder');
        return array(
            'With disabled prefix, middle name, suffix' => array(
                $customerBuilder->setPrefix(
                    'prefix'
                )->setFirstname(
                    'FirstName'
                )->setMiddlename(
                    'MiddleName'
                )->setLastname(
                    'LastName'
                )->setSuffix(
                    'suffix'
                )->create(),
                'FirstName LastName'
            ),
            'With prefix, middle name, suffix' => array(
                $customerBuilder->setPrefix(
                    'prefix'
                )->setFirstname(
                    'FirstName'
                )->setMiddlename(
                    'MiddleName'
                )->setLastname(
                    'LastName'
                )->setSuffix(
                    'suffix'
                )->create(),
                'prefix FirstName MiddleName LastName suffix',
                true, // $isPrefixAllowed
                true, // $isMiddleNameAllowed
                true //$isSuffixAllowed
            ),
            'Empty prefix, middle name, suffix' => array(
                $customerBuilder->setFirstname('FirstName')->setLastname('LastName')->create(),
                'FirstName LastName',
                true, // $isPrefixAllowed
                true, // $isMiddleNameAllowed
                true //$isSuffixAllowed
            ),
            'Empty prefix and suffix, not empty middle name' => array(
                $customerBuilder->setFirstname(
                    'FirstName'
                )->setMiddlename(
                    'MiddleName'
                )->setLastname(
                    'LastName'
                )->create(),
                'FirstName MiddleName LastName',
                true, // $isPrefixAllowed
                true, // $isMiddleNameAllowed
                true //$isSuffixAllowed
            )
        );
    }
}
