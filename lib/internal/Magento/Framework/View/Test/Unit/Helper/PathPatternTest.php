<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Helper\PathPattern;
use PHPUnit\Framework\TestCase;

class PathPatternTest extends TestCase
{
    /**
     * @var PathPattern
     */
    protected $pathPatternHelper;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->pathPatternHelper = $this->objectManagerHelper->getObject(
            PathPattern::class
        );
    }

    /**
     * @param string $path
     * @param string $expectedPattern
     *
     * @dataProvider translatePatternFromGlobDataProvider
     */
    public function testTranslatePatternFromGlob($path, $expectedPattern)
    {
        $this->assertEquals($expectedPattern, $this->pathPatternHelper->translatePatternFromGlob($path));
    }

    /**
     * @return array
     */
    public static function translatePatternFromGlobDataProvider()
    {
        return [
            [
                'path' => '*.xml',
                'expectedPattern' => '[^/]*\\.xml'
            ],
            [
                'path' => 'd??.*',
                'expectedPattern' => 'd[^/][^/]\\.[^/]*'
            ],
            [
                'path' => '[!0-9]?-[a-fA-F0-9].php',
                'expectedPattern' => '[^0-9][^/]\\-[a-fA-F0-9]\\.php'
            ],
            [
                'path' => 'config.{php,json,xml}',
                'expectedPattern' => 'config\\.(?:php|json|xml)'
            ],
            [
                'path' => 'c?nf[aio]g{-,}[!0-9/]*.{p,}html',
                'expectedPattern' => 'c[^/]nf[aio]g(?:\\-|)[^0-9/][^/]*\\.(?:p|)html'
            ]
        ];
    }
}
