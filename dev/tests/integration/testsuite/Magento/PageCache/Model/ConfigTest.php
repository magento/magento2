<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
        $this->config = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\PageCache\Model\Config::class,
            [
                'readFactory' => $readFactoryMock
            ]
        );
    }

    // @codingStandardsIgnoreStart
    /**
     * @magentoConfigFixture current_store system/full_page_cache/varnish/backend_host example.com
     * @magentoConfigFixture current_store system/full_page_cache/varnish/backend_port 8080
     * @magentoConfigFixture current_store system/full_page_cache/varnish/access_list 127.0.0.1,192.168.0.1,127.0.0.2
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
