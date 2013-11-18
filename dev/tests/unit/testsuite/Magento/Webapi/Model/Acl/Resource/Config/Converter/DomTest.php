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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webapi\Model\Acl\Resource\Config\Converter;

class DomTest extends \Magento\Acl\Resource\Config\Converter\DomTest
{
    protected function setUp()
    {
        $this->_converter = new \Magento\Webapi\Model\Acl\Resource\Config\Converter\Dom();
    }

    /**
     * @return array
     */
    public function convertWithValidDomDataProvider()
    {
        return array(
            array(
                include __DIR__ . DIRECTORY_SEPARATOR . '_files'
                    . DIRECTORY_SEPARATOR . 'converted_valid_webapi_acl.php',
                file_get_contents(
                    __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'valid_webapi_acl.xml'
                ),
            ),
        );
    }

    /**
     * @return array
     */
    public function convertWithInvalidDomDataProvider()
    {
        return array_merge(
            parent::convertWithInvalidDomDataProvider(),
            array(
                array(
                    'mapping without "id" attribute' => '<?xml version="1.0"?><config><mapping>'
                        . '<resource parent="Custom_Module::parent_id" /></mapping></config>'
                ),
                array(
                    'mapping without "parent" attribute' => '<?xml version="1.0"?><config><mapping>'
                        . '<resource id="Custom_Module::id" /></mapping></config>'
                ),
            )
        );
    }
}
