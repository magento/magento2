<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Config\Reader\Source\Deployed;

use Magento\Config\Model\Config\Reader;
use Magento\Framework\App\Config;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Config\Model\Config\Reader\Source\Deployed\DocumentRoot;

/**
 * Test class for checking settings that defined in config file
 */
class DocumentRootTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var DocumentRoot
     */
    private $documentRoot;

    public function setUp()
    {
        $this->configMock = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->documentRoot = new DocumentRoot($this->configMock);
    }

    /**
     * Ensures that the path returned matches the pub/ path.
     */
    public function testGetPath()
    {
        $this->configMockSetForDocumentRootIsPub();

        $this->assertSame(DirectoryList::PUB, $this->documentRoot->getPath());
    }

    /**
     * Ensures that the deployment configuration returns the mocked value for
     * the pub/ folder.
     */
    public function testIsPub()
    {
        $this->configMockSetForDocumentRootIsPub();

        $this->assertSame(true, $this->documentRoot->isPub());
    }

    private function configMockSetForDocumentRootIsPub()
    {
        $this->configMock->expects($this->any())
            ->method('get')
            ->willReturnMap([
                [
                    ConfigOptionsListConstants::CONFIG_PATH_DOCUMENT_ROOT_IS_PUB,
                    null,
                    true
                ],
            ]);
    }
}
