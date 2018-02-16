<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Metadata\Form;

/** Test Magento\Customer\Model\Metadata\Form\Multiline */
abstract class AbstractFormTestCase extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Stdlib\DateTime\TimezoneInterface */
    protected $localeMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Locale\ResolverInterface */
    protected $localeResolverMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Psr\Log\LoggerInterface */
    protected $loggerMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Api\Data\AttributeMetadataInterface */
    protected $attributeMetadataMock;

    protected function setUp()
    {
        $this->localeMock = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime\TimezoneInterface')->getMock();
        $this->localeResolverMock = $this->getMockBuilder('Magento\Framework\Locale\ResolverInterface')->getMock();
        $this->loggerMock = $this->getMockBuilder('Psr\Log\LoggerInterface')->getMock();
        $this->attributeMetadataMock = $this->getMock('Magento\Customer\Api\Data\AttributeMetadataInterface');
    }
}
