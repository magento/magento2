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
 * @category    Magento
 * @package     Magento_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Cms\Model;

class PageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Cms\Model\Page
     */
    protected $model;

    /**
     * @magentoDbIsolation enabled
     * @dataProvider generateIdentifierFromTitleDataProvider
     */
    public function testGenerateIdentifierFromTitle($data, $expectedIdentifier)
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Cms\Model\Page $page */
        $page = $objectManager->create('Magento\Cms\Model\Page');
        $page->setData($data);
        $page->save();
        $this->assertEquals($expectedIdentifier, $page->getIdentifier());
    }

    public function generateIdentifierFromTitleDataProvider()
    {
        return array(
            array('data' => array('title' => 'Test title'), 'expectedIdentifier' => 'test-title'),
            array(
                'data' => array('title' => 'Кирилический заголовок'),
                'expectedIdentifier' => 'kirilicheskij-zagolovok'
            ),
            array(
                'data' => array('title' => 'Test title', 'identifier' => 'custom-identifier'),
                'expectedIdentifier' => 'custom-identifier'
            )
        );
    }
}
