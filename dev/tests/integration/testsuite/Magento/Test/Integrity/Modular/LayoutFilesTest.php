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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Test\Integrity\Modular;

class LayoutFilesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Layout\Argument\Parser
     */
    protected $_argParser;

    /**
     * @var \Magento\Framework\Data\Argument\InterpreterInterface
     */
    protected $_argInterpreter;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_argParser = $objectManager->get('Magento\Framework\View\Layout\Argument\Parser');
        $this->_argInterpreter = $objectManager->get('layoutArgumentInterpreter');
    }

    /**
     * @param string $area
     * @param string $layoutFile
     * @dataProvider layoutArgumentsDataProvider
     */
    public function testLayoutArguments($area, $layoutFile)
    {
        \Magento\TestFramework\Helper\Bootstrap::getInstance()->loadArea($area);
        $dom = new \DOMDocument();
        $dom->load($layoutFile);
        $xpath = new \DOMXPath($dom);
        $argumentNodes = $xpath->query('/layout//arguments/argument | /layout//action/argument');
        /** @var \DOMNode $argumentNode */
        foreach ($argumentNodes as $argumentNode) {
            try {
                $argumentData = $this->_argParser->parse($argumentNode);
                if ($this->isSkippedArgument($argumentData)) {
                    continue;
                }
                $this->_argInterpreter->evaluate($argumentData);
            } catch (\Magento\Framework\Data\Argument\MissingOptionalValueException $e) {
                // Argument value is missing in the testing environment, but it's optional, so no big deal
            } catch (\Exception $e) {
                $this->fail($e->getMessage());
            }
        }
    }

    /**
     * @return array
     */
    public function layoutArgumentsDataProvider()
    {
        $areas = ['adminhtml', 'frontend', 'install', 'email'];
        $data = [];
        foreach ($areas as $area) {
            $layoutFiles = \Magento\TestFramework\Utility\Files::init()->getLayoutFiles(['area' => $area], false);
            foreach ($layoutFiles as $layoutFile) {
                $data[substr($layoutFile, strlen(BP))] = [$area, $layoutFile];
            }
        }
        return $data;
    }

    /**
     * Whether an argument should be skipped, because it cannot be evaluated in the testing environment
     *
     * @param array $argumentData
     * @return bool
     */
    protected function isSkippedArgument(array $argumentData)
    {
        // Do not take into account argument name and parameters
        unset($argumentData['name']);
        unset($argumentData['param']);

        $isUpdater = isset($argumentData['updater']);
        unset($argumentData['updater']);

        // Arguments, evaluation of which causes a run-time error, because of unsafe assumptions to the environment
        $typeAttr = \Magento\Core\Model\Layout\Merge::TYPE_ATTRIBUTE;
        $ignoredArguments = array(
            array(
                $typeAttr => 'object',
                'value' => 'Magento\GroupedProduct\Model\Resource\Product\Type\Grouped\AssociatedProductsCollection'
            ),
            array(
                $typeAttr => 'object',
                'value' => 'Magento\ConfigurableProduct\Model\Resource\Product\Collection\AssociatedProduct'
            ),
            array($typeAttr => 'object', 'value' => 'Magento\Search\Model\Resource\Search\Grid\Collection'),
            array($typeAttr => 'object', 'value' => 'Magento\Wishlist\Model\Resource\Item\Collection\Grid'),
            array(
                $typeAttr => 'object',
                'value' => 'Magento\CustomerSegment\Model\Resource\Segment\Report\Detail\Collection'
            ),
            array($typeAttr => 'helper', 'helper' => 'Magento\Pbridge\Helper\Data::getReviewButtonTemplate'),
            array($typeAttr => 'helper', 'helper' => 'Magento\Pbridge\Helper\Data::getContinueButtonTemplate'),
            array($typeAttr => 'options', 'model' => 'Magento\Search\Model\Adminhtml\Search\Grid\Options'),
            array($typeAttr => 'options', 'model' => 'Magento\Logging\Model\Resource\Grid\ActionsGroup'),
            array($typeAttr => 'options', 'model' => 'Magento\Logging\Model\Resource\Grid\Actions')
        );
        $isIgnoredArgument = in_array($argumentData, $ignoredArguments, true);

        unset($argumentData[$typeAttr]);
        $hasValue = !empty($argumentData);

        return $isIgnoredArgument || $isUpdater && !$hasValue;
    }
}
