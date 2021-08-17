<?php
/**
 * @see       https://github.com/laminas/laminas-soap for the canonical source repository
 * @copyright https://github.com/laminas/laminas-soap/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-soap/blob/master/LICENSE.md New BSD License
 */

namespace Magento\Webapi\Test\Unit\Model\Laminas\Soap\ComplexTypeStrategy;

use Magento\Webapi\Api\Data\DocumentationStrategyInterface;
use Magento\Webapi\Model\Laminas\Soap\ComplexTypeStrategy\DefaultComplexType;
use Magento\Webapi\Test\Unit\Model\Laminas\Soap\TestAsset;
use Magento\Webapi\Test\Unit\Model\Laminas\Soap\TestAsset\PublicPrivateProtected;
use Magento\Webapi\Test\Unit\Model\Laminas\Soap\TestAsset\WsdlTestClass;
use Magento\Webapi\Test\Unit\Model\Laminas\Soap\WsdlTestHelper;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;
use ReflectionProperty;

/**
 * @covers \Magento\Webapi\Model\Laminas\Soap\ComplexTypeStrategy\DefaultComplexType
 *
 */
class DefaultComplexTypeTest extends WsdlTestHelper
{
    use ProphecyTrait;

    /**
     * @var DefaultComplexType
     */
    protected $strategy;

    public function setUp(): void
    {
        $this->strategy = new DefaultComplexType();

        parent::setUp();
    }

    /**
     * @group Laminas-5944
     */
    public function testOnlyPublicPropertiesAreDiscoveredByStrategy()
    {
        $this->strategy->addComplexType(TestAsset\PublicPrivateProtected::class);

        $nodes = $this->xpath->query('//xsd:element[@name="'.(PublicPrivateProtected::PROTECTED_VAR_NAME).'"]');
        $this->assertEquals(0, $nodes->length, 'Document should not contain protected fields');

        $nodes = $this->xpath->query('//xsd:element[@name="'.(PublicPrivateProtected::PRIVATE_VAR_NAME).'"]');
        $this->assertEquals(0, $nodes->length, 'Document should not contain private fields');

        $this->documentNodesTest();
    }

    public function testDoubleClassesAreDiscoveredByStrategy()
    {
        $this->strategy->addComplexType(TestAsset\WsdlTestClass::class);
        $this->strategy->addComplexType(TestAsset\WsdlTestClass::class);

        $nodes = $this->xpath->query('//xsd:complexType[@name="WsdlTestClass"]');
        $this->assertEquals(1, $nodes->length);

        $this->documentNodesTest();
    }

    public function testDocumentationStrategyCalled()
    {
        $documentation = $this->prophesize(DocumentationStrategyInterface::class);
        $documentation->getPropertyDocumentation(Argument::type(ReflectionProperty::class))
            ->shouldBeCalledTimes(2)
            ->willReturn('Property');
        $documentation->getComplexTypeDocumentation(Argument::type(ReflectionClass::class))
            ->shouldBeCalledTimes(1)
            ->willReturn('Complex type');
        $this->strategy->setDocumentationStrategy($documentation->reveal());
        $this->strategy->addComplexType(WsdlTestClass::class);
    }
}
