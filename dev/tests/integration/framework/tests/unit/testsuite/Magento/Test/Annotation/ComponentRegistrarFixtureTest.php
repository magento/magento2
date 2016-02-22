<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Test\Annotation;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\TestFramework\Annotation\ComponentRegistrarFixture;

class ComponentRegistrarFixtureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ComponentRegistrar
     */
    private $componentRegistrar;

    const LIBRARY_NAME = 'magento/library';
    const MODULE_NAME = 'Magento_ModuleOne';
    const THEME_NAME = 'frontend/Magento/theme';
    const LANGUAGE_NAME = 'magento_language';

    protected function setUp()
    {
        $this->componentRegistrar = new ComponentRegistrar();
    }

    /**
     * @magentoComponentsDir components
     */
    public function testStartEndTest()
    {
        $this->assertFixturesNotRegistered();
        $object = new ComponentRegistrarFixture(__DIR__ . '/_files');
        $object->startTest($this);
        $this->assertFixturesRegistered();
        $object->endTest($this);
        $this->assertFixturesNotRegistered();
    }

    private function assertFixturesNotRegistered()
    {
        $this->assertEmpty($this->componentRegistrar->getPath(ComponentRegistrar::LIBRARY, self::LIBRARY_NAME));
        $this->assertEmpty($this->componentRegistrar->getPath(ComponentRegistrar::MODULE, self::MODULE_NAME));
        $this->assertEmpty($this->componentRegistrar->getPath(ComponentRegistrar::THEME, self::THEME_NAME));
        $this->assertEmpty($this->componentRegistrar->getPath(ComponentRegistrar::LANGUAGE, self::LANGUAGE_NAME));
    }

    private function assertFixturesRegistered()
    {
        $this->assertSame(
            __DIR__ . '/_files/components/b',
            $this->componentRegistrar->getPath(ComponentRegistrar::LIBRARY, self::LIBRARY_NAME)
        );
        $this->assertSame(
            __DIR__ . '/_files/components',
            $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, self::MODULE_NAME)
        );
        $this->assertSame(
            __DIR__ . '/_files/components/a/aa/aaa',
            $this->componentRegistrar->getPath(ComponentRegistrar::THEME, self::THEME_NAME)
        );
        $this->assertSame(
            __DIR__ . '/_files/components/a/aa',
            $this->componentRegistrar->getPath(ComponentRegistrar::LANGUAGE, self::LANGUAGE_NAME)
        );
    }
}
