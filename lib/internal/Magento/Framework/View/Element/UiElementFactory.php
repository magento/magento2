<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\View\Element;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\UiComponent\Context as UiContext;

/**
 * Class UiElementFactory
 */
class UiElementFactory
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var UiContext
     */
    protected $context;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param UiContext $context
     */
    public function __construct(ObjectManagerInterface $objectManager, UiContext $context)
    {
        $this->objectManager = $objectManager;
        $this->context = $context;
    }

    /**
     * Create data provider
     *
     * @param string $elementName
     * @param array $data
     * @return bool|BlockInterface
     * @throws \Exception
     */
    public function create($elementName, array $data = [])
    {
        if ('text' == $elementName) {
            $elementName = 'input';
        }
        $block = $this->context->getLayout()->getBlock($elementName);
        if (!$block) {
            throw new \Exception('Can not find block of element ' . $elementName);
        }
        $newBlock = clone $block;
        $newBlock->addData($data);
        return $newBlock;
    }
}
