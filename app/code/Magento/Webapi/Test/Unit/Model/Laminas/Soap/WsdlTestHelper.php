<?php
/**
 * @see       https://github.com/laminas/laminas-soap for the canonical source repository
 * @copyright https://github.com/laminas/laminas-soap/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-soap/blob/master/LICENSE.md New BSD License
 */

namespace Magento\Webapi\Test\Unit\Model\Laminas\Soap;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Magento\Webapi\Model\Laminas\Soap\Wsdl;
use Magento\Webapi\Api\Data\ComplexTypeStrategyInterface;
use Magento\Webapi\Model\Laminas\Soap\ComplexTypeStrategy\DefaultComplexType;
use PHPUnit\Framework\TestCase;

/**
 * Laminas_Soap_Server
 *
 * @group      Laminas_Soap
 * @group      Laminas_Soap_Wsdl
 **/
class WsdlTestHelper extends TestCase
{
    /**
     * @var Wsdl
     */
    protected $wsdl;
    /**
     * @var DOMDocument
     */
    protected $dom;
    /**
     * @var DOMXPath
     */
    protected $xpath;

    /**
     * @var ComplexTypeStrategyInterface
     */
    protected $strategy;

    /**
     * @var string
     */
    protected $defaultServiceName = 'MyService';

    /**
     * @var string
     */
    protected $defaultServiceUri = 'http://localhost/MyService.php';

    public function setUp(): void
    {
        if (empty($this->strategy) or ! ($this->strategy instanceof ComplexTypeStrategyInterface)) {
            $this->strategy = new DefaultComplexType();
        }

        $this->wsdl = new Wsdl($this->defaultServiceName, $this->defaultServiceUri, $this->strategy);

        if ($this->strategy instanceof ComplexTypeStrategyInterface) {
            $this->strategy->setContext($this->wsdl);
        }

        $this->dom = $this->wsdl->toDomDocument();
        $this->dom = $this->registerNamespaces($this->dom);
    }

    /**
     * @param DOMDocument $obj
     * @param string $documentNamespace
     * @return DOMDocument
     */
    public function registerNamespaces($obj, $documentNamespace = null)
    {
        if (empty($documentNamespace)) {
            $documentNamespace = $this->defaultServiceUri;
        }

        $this->xpath = new DOMXPath($obj);
        $this->xpath->registerNamespace('unittest', Wsdl::WSDL_NS_URI);

        $this->xpath->registerNamespace('tns', $documentNamespace);
        $this->xpath->registerNamespace('soap', Wsdl::SOAP_11_NS_URI);
        $this->xpath->registerNamespace('soap12', Wsdl::SOAP_12_NS_URI);
        $this->xpath->registerNamespace('xsd', Wsdl::XSD_NS_URI);
        $this->xpath->registerNamespace('soap-enc', Wsdl::SOAP_ENC_URI);
        $this->xpath->registerNamespace('wsdl', Wsdl::WSDL_NS_URI);

        return $obj;
    }

    /**
     * @param DOMElement $element
     */
    public function documentNodesTest($element = null)
    {
        if (! ($this->wsdl instanceof Wsdl)) {
            return;
        }

        if (null === $element) {
            $element = $this->wsdl->toDomDocument()->documentElement;
        }

        foreach ($element->childNodes as $node) {
            if (in_array($node->nodeType, [XML_ELEMENT_NODE])) {
                $this->assertNotEmpty(
                    $node->namespaceURI,
                    'Document element: ' . $node->nodeName . ' has no valid namespace. Line: ' . $node->getLineNo()
                );
                $this->documentNodesTest($node);
            }
        }
    }
}
