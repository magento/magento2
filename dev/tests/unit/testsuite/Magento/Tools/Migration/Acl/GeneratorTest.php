<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Migration\Acl;

require_once realpath(__DIR__ . '/../../../../../../../') . '/tools/Magento/Tools/Migration/Acl/Generator.php';
require_once realpath(__DIR__ . '/../../../../../../../') . '/tools/Magento/Tools/Migration/Acl/FileManager.php';
require_once realpath(__DIR__ . '/../../../../../../../') . '/tools/Magento/Tools/Migration/Acl/Formatter.php';
/**
 * \Magento\Tools\Migration\Acl\Generator test case
 */
class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var $model \Magento\Tools\Migration\Acl\Generator
     */
    protected $_model;

    /**
     * @var string
     */
    protected $_fixturePath;

    /**
     * Adminhtml file list
     *
     * @var array
     */
    protected $_adminhtmlFiles = [];

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_xmlFormatterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_fileManagerMock;

    protected function setUp()
    {
        $this->_xmlFormatterMock = $this->getMock('Magento\Tools\Migration\Acl\Formatter');
        $this->_fileManagerMock = $this->getMock('Magento\Tools\Migration\Acl\FileManager');
        $this->_model = new \Magento\Tools\Migration\Acl\Generator($this->_xmlFormatterMock, $this->_fileManagerMock);

        $this->_fixturePath = realpath(__DIR__) . '/_files';

        $prefix = $this->_fixturePath . '/app/code/';
        $suffix = '/etc/adminhtml.xml';

        $this->_adminhtmlFiles = [
            $prefix . 'local/Namespace/Module' . $suffix,
            $prefix . 'community/Namespace/Module' . $suffix,
            $prefix . 'core/ANamespace/Module' . $suffix,
            $prefix . 'core/BNamespace/Module' . $suffix,
        ];

        $this->_model->setAdminhtmlFiles($this->_adminhtmlFiles);

        $this->_model->setBasePath($this->_fixturePath);
    }

    /**
     * @param $filePath
     * @param $expectedModuleName
     *
     * @dataProvider getModuleNameDataProvider
     */
    public function testGetModuleName($filePath, $expectedModuleName)
    {
        $this->assertEquals($expectedModuleName, $this->_model->getModuleName($filePath), 'Incorrect Module Name');
    }

    /**
     * @return array
     */
    public function getModuleNameDataProvider()
    {
        return [
            [
                'filePath' => '/app/code/core/ANamespace/ModuleOne/etc/adminhtml.xml',
                'moduleName' => 'ANamespace_ModuleOne',
            ],
            [
                'filePath' => '/app/code/core/BNamespace/ModuleOne/etc/adminhtml.xml',
                'moduleName' => 'BNamespace_ModuleOne'
            ]
        ];
    }

    public function testIsForwardedNode()
    {
        $this->assertTrue($this->_model->isForwardNode('children'));
        $this->assertFalse($this->_model->isForwardNode('admin'));
    }

    public function testIsMetaNode()
    {
        $metaNodes = ['meta_one' => 'MetaOne', 'meta_two' => 'MetaTwo'];
        $this->_model->setMetaNodeNames($metaNodes);
        $this->assertEquals($metaNodes, $this->_model->getMetaNodeNames());

        $this->assertTrue($this->_model->isMetaNode('meta_one'));
        $this->assertTrue($this->_model->isMetaNode('meta_two'));
        $this->assertFalse($this->_model->isMetaNode('meta_three'));
    }

    public function testIsValidNodeType()
    {
        $this->assertFalse($this->_model->isValidNodeType(0));
        $this->assertFalse($this->_model->isValidNodeType(null));
        $this->assertTrue($this->_model->isValidNodeType(1));
    }

    /**
     * @param $expectedPath
     * @param $codePool
     * @param $namespace
     * @dataProvider getEtcPatternDataProvider
     */
    public function testGetEtcPattern($expectedPath, $codePool, $namespace)
    {
        $this->assertStringEndsWith($expectedPath, $this->_model->getEtcDirPattern($codePool, $namespace));
    }

    /**
     * @return array
     */
    public function getEtcPatternDataProvider()
    {
        return [
            ['expectedPath' => '/app/code/*/*/*/etc/', 'codePool' => '*', 'namespace' => '*'],
            ['expectedPath' => '/app/code/core/Magento/*/etc/', 'codePool' => 'core', 'namespace' => 'Magento']
        ];
    }

    public function testCreateNode()
    {
        $dom = new \DOMDocument();
        $parent = $dom->createElement('parent');
        $parent->setAttribute('xpath', 'root');
        $dom->appendChild($parent);
        $nodeName = 'testNode';
        $newNode = $this->_model->createNode($dom, $nodeName, $parent, 'Some_Module');

        $this->assertEquals(1, $parent->childNodes->length);
        $this->assertEquals($newNode, $parent->childNodes->item(0));
        $this->assertEquals($nodeName, $newNode->getAttribute('id'));
        $this->assertEquals('root/testNode', $newNode->getAttribute('xpath'));
    }

    public function testSetMetaInfo()
    {
        $metaNodeName = ['sort_order' => 'test_SortOrder', 'title' => 'test_Title'];
        $this->_model->setMetaNodeNames($metaNodeName);

        $dom = new \DOMDocument();
        $parent = $dom->createElement('parent');
        $parent->setAttribute('xpath', 'root');
        $parent->setAttribute('id', 'root_id');
        $dom->appendChild($parent);

        $dataNodeSortOrder = $dom->createElement('sort_order', '100');
        $dataNodeTitle = $dom->createElement('title', 'TestTitle');

        $this->_model->setMetaInfo($parent, $dataNodeSortOrder, 'Module_Name');
        $this->assertEmpty($this->_model->getAclResourceMaps());
        $this->_model->setMetaInfo($parent, $dataNodeTitle, 'Module_Name');

        $this->assertEquals(100, $parent->getAttribute('test_SortOrder'), 'Incorrect set of sort order');
        $this->assertEquals('TestTitle', $parent->getAttribute('test_Title'), 'Incorrect set of title');
        $maps = ['root' => 'Module_Name::root_id'];
        $this->assertEquals($maps, $this->_model->getAclResourceMaps()); //test setting of id maps
    }

    public function testGetAdminhtmlFiles()
    {
        $this->_model->setAdminhtmlFiles(null);
        $this->assertEquals(
            $this->_adminhtmlFiles,
            $this->_model->getAdminhtmlFiles(),
            'Incorrect file adminhtml file searching'
        );
    }

    /**
     * @covers \Magento\Tools\Migration\Acl\Generator::parseNode
     * @covers \Magento\Tools\Migration\Acl\Generator::generateId
     */
    public function testParseNode()
    {
        $dom = new \DOMDocument();
        $dom->formatOutput = true;
        $parentNode = $dom->createElement('root');
        $dom->appendChild($parentNode);
        $moduleName = 'Module_Name';

        $sourceDom = new \DOMDocument();
        $sourceDom->load($this->_fixturePath . '/parse_node_source.xml');
        $nodeList = $sourceDom->getElementsByTagName('resources');
        $this->_model->parseNode($nodeList->item(0), $dom, $parentNode, $moduleName);
        $expectedDom = new \DOMDocument();
        $expectedDom->load($this->_fixturePath . '/parse_node_result.xml');
        $this->assertEquals(
            $expectedDom->saveXML($expectedDom->documentElement),
            $dom->saveXML($dom->documentElement)
        );
    }

    public function testGetResultDomDocument()
    {
        $expectedDocument = <<<TEMPLATE
<config>
  <acl>
    <resources xpath="config/acl/resources"/>
  </acl>
</config>
TEMPLATE;
        $dom = $this->_model->getResultDomDocument();
        $expectedDom = new \DOMDocument();
        $expectedDom->formatOutput = true;
        $this->assertEquals($expectedDocument, $dom->saveXML($dom->documentElement));
    }

    public function testParseAdminhtmlFiles()
    {
        $this->_model->parseAdminhtmlFiles();
        $this->assertCount(4, $this->_model->getParsedDomList());
        $this->assertCount(4, $this->_model->getAdminhtmlDomList());
    }

    /**
     * @covers \Magento\Tools\Migration\Acl\Generator::updateAclResourceIds()
     * @covers \Magento\Tools\Migration\Acl\Generator::updateChildAclNodes() (removing of xpath attribute)
     */
    public function testUpdateAclResourceIds()
    {
        $this->_model->parseAdminhtmlFiles();

        $domList = $this->_model->getParsedDomList();

        /** @var $dom \DOMDocument **/
        foreach ($domList as $dom) {
            $xpath = new \DOMXPath($dom);
            $resources = $xpath->query('//resources[@xpath]');
            $this->assertEquals(1, $resources->length);
        }
        $this->_model->updateAclResourceIds();
        /**
         * check that all xpath attributes are removed
         */
        /** @var $dom \DOMDocument **/
        foreach ($domList as $dom) {
            $xpath = new \DOMXPath($dom);
            $resources = $xpath->query('//*[@xpath]');
            $this->assertEquals(0, $resources->length);
        }
    }

    public function testUpdateChildAclNodes()
    {
        $dom = new \DOMDocument();
        $fileActual = $this->_fixturePath . '/update_child_acl_nodes_source.xml';
        $fileExpected = $this->_fixturePath . '/update_child_acl_nodes_result.xml';
        $dom->load($fileActual);
        $rootNode = $dom->getElementsByTagName('resources')->item(0);

        $aclResourcesMaps = [
            '/admin' => 'Map_Module::admin',
            '/admin/customer/manage' => 'Map_Module::manage',
            '/admin/system' => 'Map_Module::system',
            '/admin/system/config' => 'Map_Module::config',
        ];

        $this->_model->setAclResourceMaps($aclResourcesMaps);
        $this->_model->updateChildAclNodes($rootNode);

        $expectedDom = new \DOMDocument();
        $expectedDom->load($fileExpected);
        $expectedRootNode = $expectedDom->getElementsByTagName('resources')->item(0);

        $this->assertEquals($expectedDom->saveXML($expectedRootNode), $dom->saveXML($rootNode));
    }

    public function testIsNodeEmpty()
    {
        $dom = new \DOMDocument();
        $node = $dom->createElement('node', 'test');
        $dom->appendChild($node);
        $this->assertTrue($this->_model->isNodeEmpty($node));

        $comment = $dom->createComment('comment');
        $node->appendChild($comment);
        $this->assertTrue($this->_model->isNodeEmpty($node));

        $subNode = $dom->createElement('subnode');
        $node->appendChild($subNode);
        $this->assertFalse($this->_model->isNodeEmpty($node));
    }
}
