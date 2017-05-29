<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Modular;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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
        $this->_argParser = $objectManager->get(\Magento\Framework\View\Layout\Argument\Parser::class);
        $this->_argInterpreter = $objectManager->get('layoutArgumentGeneratorInterpreter');
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
        $areas = ['adminhtml', 'frontend', 'email'];
        $data = [];
        foreach ($areas as $area) {
            $layoutFiles = \Magento\Framework\App\Utility\Files::init()->getLayoutFiles(['area' => $area], false);
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
        $typeAttr = \Magento\Framework\View\Model\Layout\Merge::TYPE_ATTRIBUTE;
        $prCollection =
            \Magento\GroupedProduct\Model\ResourceModel\Product\Type\Grouped\AssociatedProductsCollection::class;
        $ignoredArguments = [
            [
                $typeAttr => 'object',
                'value' => $prCollection,
            ],
            [$typeAttr => 'object', 'value' => \Magento\Solr\Model\ResourceModel\Search\Grid\Collection::class],
            [$typeAttr => 'object', 'value' => \Magento\Wishlist\Model\ResourceModel\Item\Collection\Grid::class],
            [
                $typeAttr => 'object',
                'value' => \Magento\CustomerSegment\Model\ResourceModel\Segment\Report\Detail\Collection::class
            ],
            [$typeAttr => 'options', 'model' => \Magento\Solr\Model\Adminhtml\Search\Grid\Options::class],
            [$typeAttr => 'options', 'model' => \Magento\Logging\Model\ResourceModel\Grid\ActionsGroup::class],
            [$typeAttr => 'options', 'model' => \Magento\Logging\Model\ResourceModel\Grid\Actions::class],
        ];
        $isIgnoredArgument = in_array($argumentData, $ignoredArguments, true);

        unset($argumentData[$typeAttr]);
        $hasValue = !empty($argumentData);

        return $isIgnoredArgument || $isUpdater && !$hasValue;
    }
}
