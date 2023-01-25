<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\Di\Code\Scanner;

use Magento\Setup\Module\Di\Code\Scanner\CompositeScanner;
use Magento\Setup\Module\Di\Code\Scanner\ScannerInterface;
use PHPUnit\Framework\TestCase;

class CompositeScannerTest extends TestCase
{
    /**
     * @var CompositeScanner
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_model = new CompositeScanner();
    }

    public function testScan()
    {
        $phpFiles = ['one/file/php', 'two/file/php'];
        $configFiles = ['one/file/config', 'two/file/config'];
        $files = ['php' => $phpFiles, 'config' => $configFiles];

        $scannerPhp = $this->getMockForAbstractClass(ScannerInterface::class);
        $scannerXml = $this->getMockForAbstractClass(ScannerInterface::class);

        $scannerPhpExpected = ['Model_OneProxy', 'Model_TwoFactory'];
        $scannerXmlExpected = ['Model_OneProxy', 'Model_ThreeFactory'];
        $scannerPhp->expects(
            $this->once()
        )->method(
            'collectEntities'
        )->with(
            $phpFiles
        )->willReturn(
            $scannerPhpExpected
        );

        $scannerXml->expects(
            $this->once()
        )->method(
            'collectEntities'
        )->with(
            $configFiles
        )->willReturn(
            $scannerXmlExpected
        );

        $this->_model->addChild($scannerPhp, 'php');
        $this->_model->addChild($scannerXml, 'config');

        $actual = $this->_model->collectEntities($files);
        $expected = [$scannerPhpExpected, $scannerXmlExpected];

        $this->assertEquals($expected, array_values($actual));
    }
}
