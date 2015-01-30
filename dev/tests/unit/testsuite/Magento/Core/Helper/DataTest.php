<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    public function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
    }

    public function testGetCacheTypes()
    {
        $cachedTypes = [
            'type1' => ['label' => 'node1', 'other' => 'other1'],
            'type2' => ['label' => 'node2', 'other' => 'other2'],
            'type3' => ['other' => 'other3'],
        ];
        $types = [
            'type1' => 'node1',
            'type2' => 'node2',
        ];
        $cacheConfigMock = $this->getMockBuilder('Magento\Framework\Cache\ConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $cacheConfigMock->expects($this->once())
            ->method('getTypes')
            ->will($this->returnValue($cachedTypes));
        $context = $this->objectManager->getObject(
            'Magento\Framework\App\Helper\Context',
            [
                'cacheConfig' => $cacheConfigMock,
            ]
        );
        $helper = $this->getHelper(
            [
                'context' => $context,
            ]
        );

        $this->assertEquals($types, $helper->getCacheTypes());
    }

    public function testJsonEncode()
    {
        $valueToEncode = 'valueToEncode';
        $translateInlineMock = $this->getMockBuilder('Magento\Framework\Translate\InlineInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $translateInlineMock->expects($this->once())
            ->method('processResponseBody');
        $context = $this->objectManager->getObject(
            'Magento\Framework\App\Helper\Context',
            [
                'translateInline' => $translateInlineMock,
            ]
        );
        $helper = $this->getHelper(
            [
                'context' => $context,
            ]
        );

        $this->assertEquals('"valueToEncode"', $helper->jsonEncode($valueToEncode));
    }

    public function testJsonDecode()
    {
        $helper = $this->getHelper([]);
        $this->assertEquals('"valueToDecode"', $helper->jsonEncode('valueToDecode'));
    }

    public function testGetDefaultCountry()
    {
        $storeId = 'storeId';
        $country = 'country';

        $scopeConfigMock = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                Data::XML_PATH_DEFAULT_COUNTRY,
                \Magento\Framework\Store\ScopeInterface::SCOPE_STORE,
                $storeId
            )->will($this->returnValue($country));

        $helper = $this->getHelper(
            [
                'scopeConfig' => $scopeConfigMock,
            ]
        );
        $this->assertEquals($country, $helper->getDefaultCountry($storeId));
    }

    /**
     * Get helper instance
     *
     * @param array $arguments
     * @return Data
     */
    private function getHelper($arguments)
    {
        return $this->objectManager->getObject('Magento\Core\Helper\Data', $arguments);
    }
}
