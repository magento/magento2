<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Model;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\PageCache\Model\Config
     */
    private $config;

    protected function setUp()
    {
        $readFactoryMock = $this->getMock(
            \Magento\Framework\Filesystem\Directory\ReadFactory::class,
            [],
            [],
            '',
            false
        );
        $modulesDirectoryMock = $this->getMock(
            \Magento\Framework\Filesystem\Directory\Write::class,
            [],
            [],
            '',
            false
        );
        $readFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($modulesDirectoryMock)
        );
        $modulesDirectoryMock->expects(
            $this->any()
        )->method(
            'readFile'
        )->will(
            $this->returnValue(file_get_contents(__DIR__ . '/_files/test.vcl'))
        );

        /** @var \PHPUnit_Framework_MockObject_MockObject $vclTemplateLocator */
        $vclTemplateLocator = $this->getMockBuilder(\Magento\PageCache\Model\Varnish\VclTemplateLocator::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTemplate'])
            ->getMock();
        $vclTemplateLocator->expects($this->any())
            ->method('getTemplate')
            ->will($this->returnValue(file_get_contents(__DIR__ . '/_files/test.vcl')));

        /** @var \PHPUnit_Framework_MockObject_MockObject $vclTemplateLocator */
        $vclGeneratorFactory = $this->getMockBuilder(\Magento\PageCache\Model\Varnish\VclGeneratorFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $expectedParams = [
            'backendHost' => 'example.com',
            'backendPort' => '8080',
            'accessList' =>  explode(',', '127.0.0.1,192.168.0.1,127.0.0.2'),
            'designExceptions' => json_decode('{"_":{"regexp":"\/firefox\/i","value":"Magento\/blank"}}', true),
            'sslOffloadedHeader' => 'X-Forwarded-Proto',
            'gracePeriod' => null
        ];
        $vclGeneratorFactory->expects($this->any())
            ->method('create')
            ->with($expectedParams)
            ->will($this->returnValue(new \Magento\PageCache\Model\Varnish\VclGenerator(
                $vclTemplateLocator,
                'example.com',
                '8080',
                explode(',', '127.0.0.1,192.168.0.1,127.0.0.2'),
                null,
                'X-Forwarded-Proto',
                json_decode('{"_":{"regexp":"\/firefox\/i","value":"Magento\/blank"}}', true)
            )));
        $this->config = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\PageCache\Model\Config::class,
            [
                'vclGeneratorFactory' => $vclGeneratorFactory
            ]
        );
    }

    // @codingStandardsIgnoreStart
    /**
     * @magentoConfigFixture default/system/full_page_cache/varnish/backend_host example.com
     * @magentoConfigFixture default/system/full_page_cache/varnish/backend_port 8080
     * @magentoConfigFixture default/system/full_page_cache/varnish/access_list 127.0.0.1,192.168.0.1,127.0.0.2
     * @magentoConfigFixture current_store design/theme/ua_regexp {"_":{"regexp":"\/firefox\/i","value":"Magento\/blank"}}
     * @magentoAppIsolation enabled
     */
    // @codingStandardsIgnoreEnd
    public function testGetVclFile()
    {
        $result = $this->config->getVclFile(Config::VARNISH_5_CONFIGURATION_PATH);
        $this->assertEquals(file_get_contents(__DIR__ . '/_files/result.vcl'), $result);
    }
}
