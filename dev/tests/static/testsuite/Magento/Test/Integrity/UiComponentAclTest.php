<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity;

use Magento\Framework\App\Utility\Files;

/**
 * Validate aclResource element is present for backend grid/listing UiComponents
 *
 * @security-private
 */
class UiComponentAclTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var array
     */
    private static $uiComponentDataSourceElements = [];

    /**
     * @var string[]
     */
    private static $accessControlList = [];

    public static function setUpBeforeClass()
    {
        $uiComponentXmlFiles = Files::init()->getUiComponentXmlFiles(['area' => 'adminhtml'], false);
        foreach ($uiComponentXmlFiles as $uiComponentXmlFile) {
            $xml = simplexml_load_file($uiComponentXmlFile);
            $dataSourceElement = $xml->xpath('/listing/dataSource');
            if (!empty($dataSourceElement)) {
                static::$uiComponentDataSourceElements[] = [
                    'file' => $uiComponentXmlFile,
                    'dataSourceXml' => array_shift($dataSourceElement)
                ];
            }
        }

        $aclXmlFiles = Files::init()->getConfigFiles('*acl.xml', [], false);
        foreach ($aclXmlFiles as $aclXmlFile) {
            $dom = new \DOMDocument();
            $dom->load($aclXmlFile);
            $aclResourceNodes = $dom->getElementsByTagName('resource');
            /** @var \DOMElement $aclResourceNode */
            foreach ($aclResourceNodes as $aclResourceNode) {
                $aclIdAttribute = $aclResourceNode->getAttribute('id');
                if (!in_array($aclIdAttribute, static::$accessControlList)) {
                    static::$accessControlList[] = $aclIdAttribute;
                }
            }
        }
    }

    public static function tearDownAfterClass()
    {
        static::$uiComponentDataSourceElements = null;
        static::$accessControlList = null;
    }

    public function testThatAclExistsInUiComponentDataSource()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            function ($file, $dataSourceXml) {
                if (!isset($dataSourceXml->aclResource)) {
                    $this->fail("UI Component '$file' contains dataSource element without an aclResource");
                }
            },
            static::$uiComponentDataSourceElements
        );
    }

    public function testThatAclIsValidInUiComponentDataSource()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            function ($file, $dataSourceXml) {
                if (isset($dataSourceXml->aclResource)) {
                    $aclResource = $dataSourceXml->aclResource;
                    if (!in_array($aclResource, static::$accessControlList)) {
                        $this->fail(
                            "UI Component '$file' references an ACL Resource that does not exist: " .
                            $aclResource
                        );
                    }
                }
            },
            static::$uiComponentDataSourceElements
        );
    }
}
