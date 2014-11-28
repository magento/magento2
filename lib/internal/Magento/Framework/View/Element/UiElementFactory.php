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
