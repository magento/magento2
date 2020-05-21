<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Model\Config\Reader\Source\Deployed;

use Magento\Config\Model\Config\Reader\Source\Deployed\DocumentRoot;
use Magento\Framework\App\Config;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Config\ConfigOptionsListConstants;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for checking settings that defined in config file
 */
class DocumentRootTest extends TestCase
{
    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var DocumentRoot
     */
    private $documentRoot;

    protected function setUp(): void
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

        $this->assertTrue($this->documentRoot->isPub());
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
