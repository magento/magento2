<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class PathPatternTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Helper\PathPattern
     */
    protected $pathPatternHelper;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->pathPatternHelper = $this->objectManagerHelper->getObject(
            \Magento\Framework\View\Helper\PathPattern::class
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
    public function translatePatternFromGlobDataProvider()
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
