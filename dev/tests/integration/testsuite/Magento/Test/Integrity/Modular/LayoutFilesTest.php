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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Test\Integrity\Modular;

class LayoutFilesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Layout\Argument\HandlerFactory
     */
    protected $_handlerFactory;

    /**
     * @var array
     */
    protected $_types;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_handlerFactory = $objectManager->get('Magento\Core\Model\Layout\Argument\HandlerFactory');
        $this->_types = $this->_handlerFactory->getTypes();
    }

    /**
     * @dataProvider layoutTypesDataProvider
     */
    public function testLayoutTypes($layout)
    {
        $layout = simplexml_load_file(
            $layout,
            'Magento\View\Layout\Element'
        );
        foreach ($layout->xpath('//*[@xsi:type]') as $argument) {
            $type = (string)$argument->attributes('xsi', true)->type;
            if (!in_array($type, $this->_types)) {
                continue;
            }
            try {
                /* @var $handler \Magento\Core\Model\Layout\Argument\HandlerInterface */
                $handler = $this->_handlerFactory->getArgumentHandlerByType($type);
                $argument = $handler->parse($argument);
                if ($this->_isIgnored($argument)) {
                    continue;
                }
                $handler->process($argument);
            } catch (\InvalidArgumentException $e) {
                $this->fail($e->getMessage());
            }
        }
    }

    /**
     * @return array
     */
    public function layoutTypesDataProvider()
    {
        return \Magento\TestFramework\Utility\Files::init()->getLayoutFiles();
    }

    /**
     * @param $argument
     * @return bool
     */
    protected function _isIgnored($argument)
    {
        return
            // we can't process updaters without value
            !isset($argument['value']) && isset($argument['updaters'])

            // ignored objects
            || isset($argument['value']['object'])
                && in_array($argument['value']['object'], array(
                    'Magento\Catalog\Model\Resource\Product\Type\Grouped\AssociatedProductsCollection',
                    'Magento\Catalog\Model\Resource\Product\Collection\AssociatedProduct',
                    'Magento\Search\Model\Resource\Search\Grid\Collection',
                    'Magento\Wishlist\Model\Resource\Item\Collection\Grid',
                    'Magento\CustomerSegment\Model\Resource\Segment\Report\Detail\Collection',
                ))

            // ignored helpers
            || isset($argument['value']['helperClass']) &&
                in_array($argument['value']['helperClass'] . '::' . $argument['value']['helperMethod'], array(
                    'Magento\Pbridge\Helper\Data::getReviewButtonTemplate'
                ))

            // ignored options
            || isset($argument['value']['model'])
                && in_array($argument['value']['model'], array(
                    'Magento\Search\Model\Adminhtml\Search\Grid\Options',
                    'Magento\Logging\Model\Resource\Grid\ActionsGroup',
                    'Magento\Logging\Model\Resource\Grid\Actions',
                ));
    }
}
