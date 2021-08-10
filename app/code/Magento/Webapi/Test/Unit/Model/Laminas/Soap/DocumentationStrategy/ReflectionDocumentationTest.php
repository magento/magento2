<?php
/**
 * @see       https://github.com/laminas/laminas-soap for the canonical source repository
 * @copyright https://github.com/laminas/laminas-soap/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-soap/blob/master/LICENSE.md New BSD License
 */

namespace Magento\Webapi\Test\Unit\Model\Laminas\Soap\DocumentationStrategy;

use Magento\Webapi\Model\Laminas\Soap\DocumentationStrategy\ReflectionDocumentation;
use Magento\Webapi\Test\Unit\Model\Laminas\Soap\TestAsset\PropertyDocumentationTestClass;
use Magento\Webapi\Test\Unit\Model\Laminas\Soap\TestAsset\WsdlTestClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ReflectionDocumentationTest extends TestCase
{
    /**
     * @var ReflectionDocumentation
     */
    private $documentation;

    protected function setUp(): void
    {
        $this->documentation = new ReflectionDocumentation();
    }

    public function testGetPropertyDocumentationParsesDocComment()
    {
        $class = new PropertyDocumentationTestClass();
        $reflection = new ReflectionClass($class);
        $actual = $this->documentation->getPropertyDocumentation($reflection->getProperty('withoutType'));
        $this->assertEquals('Property documentation', $actual);
    }

    public function testGetPropertyDocumentationSkipsAnnotations()
    {
        $class = new PropertyDocumentationTestClass();
        $reflection = new ReflectionClass($class);
        $actual = $this->documentation->getPropertyDocumentation($reflection->getProperty('withType'));
        $this->assertEquals('Property documentation', $actual);
    }

    public function testGetPropertyDocumentationReturnsEmptyString()
    {
        $class = new PropertyDocumentationTestClass();
        $reflection = new ReflectionClass($class);
        $actual = $this->documentation->getPropertyDocumentation($reflection->getProperty('noDoc'));
        $this->assertEquals('', $actual);
    }

    public function getGetComplexTypeDocumentationParsesDocComment()
    {
        $reflection = new ReflectionClass(new WsdlTestClass());
        $actual = $this->documentation->getComplexTypeDocumentation($reflection);
        $this->assertEquals('Test class', $actual);
    }
}
