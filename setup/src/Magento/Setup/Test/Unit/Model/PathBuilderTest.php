<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model;

use \Magento\Setup\Model\PathBuilder;

class PathBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\FileSystem\Directory\ReadFactory
     */
    private $readFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\FileSystem\Directory\ReadInterface
     */
    private $readerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\PathBuilder
     */
    private $pathBuilder;

    public function setup()
    {
        $this->readFactoryMock = $this->getMock(
            'Magento\Framework\Filesystem\Directory\ReadFactory',
            [],
            [],
            '',
            false
        );
        $this->readerMock = $this->getMockForAbstractClass(
            'Magento\Framework\Filesystem\Directory\ReadInterface',
            [],
            '',
            false
        );
        $this->readFactoryMock->expects($this->once())->method('create')->willReturn($this->readerMock);
        $this->pathBuilder = new PathBuilder($this->readFactoryMock);
    }

    // Error scenario: magento/magento2-base/composer.json not found
    public function testBuildComposerJsonFileNotFound()
    {
        $this->readerMock->expects($this->once())->method('isExist')->willReturn(false);
        $this->readerMock->expects($this->never())->method('isReadable');
        $this->readerMock->expects($this->never())->method('readFile');
        $this->setExpectedException(
            'Magento\Setup\Exception',
            sprintf('Could not locate %s file.', PathBuilder::MAGENTO_BASE_PACKAGE_COMPOSER_JSON_FILE)
        );
        $this->pathBuilder->build();
    }

    // Error scenario: magento/magento2-base/composer.json file could not be read
    public function testBuildComposerJsonFileNotReadable()
    {
        $this->readerMock->expects($this->once())->method('isExist')->willReturn(true);
        $this->readerMock->expects($this->once())->method('isReadable')->willReturn(false);
        $this->readerMock->expects($this->never())->method('readFile');
        $this->setExpectedException(
            'Magento\Setup\Exception',
            sprintf('Could not read %s file.', PathBuilder::MAGENTO_BASE_PACKAGE_COMPOSER_JSON_FILE)
        );
        $this->pathBuilder->build();
    }

    // Scenario: ["extra"]["map"] is absent within magento/magento2-base/composer.json file
    public function testBuildNoExtraMapSectionInComposerJsonFile()
    {
        $this->readerMock->expects($this->once())->method('isExist')->willReturn(true);
        $this->readerMock->expects($this->once())->method('isReadable')->willReturn(true);
        $jsonData = json_encode(
            [
                PathBuilder::COMPOSER_KEY_EXTRA =>
                [
                    __FILE__,
                    __FILE__
                ]
            ]
        );
        $this->readerMock->expects($this->once())->method('readFile')->willReturn($jsonData);
        $expectedList = [];
        $actualList = $this->pathBuilder->build();
        $this->assertEquals($expectedList, $actualList);
    }

    // Success scenario
    public function testBuild()
    {
        $this->readerMock->expects($this->once())->method('isExist')->willReturn(true);
        $this->readerMock->expects($this->once())->method('isReadable')->willReturn(true);
        $jsonData = json_encode(
            [
                PathBuilder::COMPOSER_KEY_EXTRA =>
                [
                    PathBuilder::COMPOSER_KEY_MAP =>
                    [
                        [
                            __FILE__,
                            __FILE__
                        ],
                        [
                            __DIR__,
                            __DIR__
                        ]
                    ]
                ]
            ]
        );
        $this->readerMock->expects($this->once())->method('readFile')->willReturn($jsonData);
        $expectedList = [__FILE__, __DIR__];
        $actualList = $this->pathBuilder->build();
        $this->assertEquals($expectedList, $actualList);
    }
}
