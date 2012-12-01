<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Tools
 * @package     unit_tests
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once realpath(dirname(__FILE__) . '/../../../../../../') . '/tools/migration/Acl/Generator.php';
require_once realpath(dirname(__FILE__) . '/../../../../../../') . '/tools/migration/Acl/FileManager.php';
require_once realpath(dirname(__FILE__) . '/../../../../../../') . '/tools/migration/Acl/Formatter.php';

/**
 * Tools_Migration_Acl_Generator test case
 */
class Tools_Migration_Acl_GeneratorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var $model Tools_Migration_Acl_Generator
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
    protected $_adminhtmlFiles = array();

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_xmlFormatterMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_fileManagerMock;

    public function setUp()
    {
        $this->_xmlFormatterMock = $this->getMock('Tools_Migration_Acl_Formatter');
        $this->_fileManagerMock = $this->getMock('Tools_Migration_Acl_FileManager');
        $this->_model = new Tools_Migration_Acl_Generator($this->_xmlFormatterMock, $this->_fileManagerMock);

        $this->_fixturePath = realpath(__DIR__) . DIRECTORY_SEPARATOR . '_files';

        $prefix = $this->_fixturePath . DIRECTORY_SEPARATOR
            . 'app' . DIRECTORY_SEPARATOR
            . 'code' . DIRECTORY_SEPARATOR;
        $suffix = DIRECTORY_SEPARATOR . 'etc' . DIRECTORY_SEPARATOR . 'adminhtml.xml';

        $this->_adminhtmlFiles = array(
            $prefix . 'local' . DIRECTORY_SEPARATOR . 'Namespace' . DIRECTORY_SEPARATOR . 'Module' . $suffix,
            $prefix . 'community' . DIRECTORY_SEPARATOR . 'Namespace' . DIRECTORY_SEPARATOR . 'Module' . $suffix,
            $prefix . 'core' . DIRECTORY_SEPARATOR . 'ANamespace' . DIRECTORY_SEPARATOR . 'Module' . $suffix,
            $prefix . 'core' . DIRECTORY_SEPARATOR . 'BNamespace' . DIRECTORY_SEPARATOR . 'Module' . $suffix,
        );

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
        return array(
            array(
                'filePath' => DIRECTORY_SEPARATOR
                    . 'app' . DIRECTORY_SEPARATOR
                    . 'code' . DIRECTORY_SEPARATOR
                    . 'core' . DIRECTORY_SEPARATOR
                    . 'ANamespace' . DIRECTORY_SEPARATOR
                    . 'ModuleOne' . DIRECTORY_SEPARATOR
                    . 'etc' . DIRECTORY_SEPARATOR
                    . 'adminhtml.xml',
                'moduleName' => 'ANamespace_ModuleOne',
            ),
            array(
                'filePath' => DIRECTORY_SEPARATOR
                    . 'app' . DIRECTORY_SEPARATOR
                    . 'code' . DIRECTORY_SEPARATOR
                    . 'core' . DIRECTORY_SEPARATOR
                    . 'BNamespace' . DIRECTORY_SEPARATOR
                    . 'ModuleOne' . DIRECTORY_SEPARATOR
                    . 'etc' . DIRECTORY_SEPARATOR
                    . 'adminhtml.xml',
                'moduleName' => 'BNamespace_ModuleOne',
            ),
        );
    }

    public function testIsForwardedNode()
    {
        $this->assertTrue($this->_model->isForwardNode('children'));
        $this->assertFalse($this->_model->isForwardNode('admin'));
    }

    public function testIsMetaNode()
    {
        $metaNodes = array(
            'meta_one' => 'MetaOne',
            'meta_two' => 'MetaTwo',
        );
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
        return array(
            array(
                'expectedPath' => DIRECTORY_SEPARATOR
                    . 'app' . DIRECTORY_SEPARATOR
                    . 'code' . DIRECTORY_SEPARATOR
                    . '*' . DIRECTORY_SEPARATOR
                    . '*' . DIRECTORY_SEPARATOR
                    . '*' . DIRECTORY_SEPARATOR
                    . 'etc' . DIRECTORY_SEPARATOR,
                'codePool' => '*',
                'namespace' => '*',
            ),
            array(
                'expectedPath' => DIRECTORY_SEPARATOR
                    . 'app' . DIRECTORY_SEPARATOR
                    . 'code' . DIRECTORY_SEPARATOR
                    . 'core' . DIRECTORY_SEPARATOR
                    . 'Mage' . DIRECTORY_SEPARATOR
                    . '*' . DIRECTORY_SEPARATOR
                    . 'etc' . DIRECTORY_SEPARATOR,
                'codePool' => 'core',
                'namespace' => 'Mage',
            ),
        );
    }

    public function testCreateNode()
    {
        $dom = new DOMDocument();
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
        $metaNodeName = array(
            'sort_order' => 'test_SortOrder',
            'title' => 'test_Title',
        );
        $this->_model->setMetaNodeNames($metaNodeName);

        $dom = new DOMDocument();
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
        $maps = array('root' => 'Module_Name::root_id');
        $this->assertEquals($maps, $this->_model->getAclResourceMaps()); //test setting of id maps
    }

    public function testGetAdminhtmlFiles()
    {
        $this->_model->setAdminhtmlFiles(null);
        $this->assertEquals($this->_adminhtmlFiles,
            $this->_model->getAdminhtmlFiles(),
            'Incorrect file adminhtml file searching'
        );
    }

    /**
     * @covers Tools_Migration_Acl_Generator::parseNode
     * @covers Tools_Migration_Acl_Generator::generateId
     */
    public function testParseNode()
    {
        $dom = new DOMDocument();
        $dom->formatOutput = true;
        $parentNode = $dom->createElement('root');
        $dom->appendChild($parentNode);
        $moduleName = 'Module_Name';

        $sourceDom = new DOMDocument();
        $sourceDom->load($this->_fixturePath . DIRECTORY_SEPARATOR . 'parse_node_source.xml');
        $nodeList = $sourceDom->getElementsByTagName('resources');
        $this->_model->parseNode($nodeList->item(0), $dom, $parentNode, $moduleName);
        $expectedDom = new DOMDocument();
        $expectedDom->load($this->_fixturePath . DIRECTORY_SEPARATOR . 'parse_node_result.xml');
        $this->assertEquals($expectedDom->saveXML($expectedDom->documentElement), $dom->saveXML($dom->documentElement));
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
        $expectedDom = new DOMDocument();
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
     * @covers Tools_Migration_Acl_Generator::updateAclResourceIds()
     * @covers Tools_Migration_Acl_Generator::updateChildAclNodes() (removing of xpath attribute)
     */
    public function testUpdateAclResourceIds()
    {
        $this->_model->parseAdminhtmlFiles();

        $domList = $this->_model->getParsedDomList();

        /** @var $dom DOMDocument **/
        foreach ($domList as $dom) {
            $xpath = new DOMXPath($dom);
            $resources = $xpath->query('//resources[@xpath]');
            $this->assertEquals(1, $resources->length);
        }
        $this->_model->updateAclResourceIds();
        /**
         * check that all xpath attributes are removed
         */
        /** @var $dom DOMDocument **/
        foreach ($domList as $dom) {
            $xpath = new DOMXPath($dom);
            $resources = $xpath->query('//*[@xpath]');
            $this->assertEquals(0, $resources->length);
        }
    }

    public function testUpdateChildAclNodes()
    {
        $dom = new DOMDocument();
        $fileActual = $this->_fixturePath . DIRECTORY_SEPARATOR . 'update_child_acl_nodes_source.xml';
        $fileExpected = $this->_fixturePath . DIRECTORY_SEPARATOR . 'update_child_acl_nodes_result.xml';
        $dom->load($fileActual);
        $rootNode = $dom->getElementsByTagName('resources')->item(0);

        $aclResourcesMaps = array(
            '/admin' => 'Map_Module::admin',
            '/admin/customer/manage' => 'Map_Module::manage',
            '/admin/system' => 'Map_Module::system',
            '/admin/system/config' => 'Map_Module::config',
        );

        $this->_model->setAclResourceMaps($aclResourcesMaps);
        $this->_model->updateChildAclNodes($rootNode);

        $expectedDom = new DOMDocument();
        $expectedDom->load($fileExpected);
        $expectedRootNode = $expectedDom->getElementsByTagName('resources')->item(0);

        $this->assertEquals($expectedDom->saveXML($expectedRootNode), $dom->saveXML($rootNode));
    }

    public function testIsNodeEmpty()
    {
        $dom = new DOMDocument();
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
