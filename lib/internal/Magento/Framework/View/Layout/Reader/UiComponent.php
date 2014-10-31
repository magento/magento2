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
namespace Magento\Framework\View\Layout\Reader;

use Magento\Framework\View\Layout;
use Magento\Framework\App;

class UiComponent implements Layout\ReaderInterface
{
    /**#@+
     * Supported types
     */
    const TYPE_UI_COMPONENT = 'ui_component';
    /**#@-*/

    /**
     * List of supported attributes
     *
     * @var array
     */
    protected $attributes = ['group', 'component'];

    /**
     * @var Layout\ScheduledStructure\Helper
     */
    protected $layoutHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\App\ScopeResolverInterface
     */
    protected $scopeResolver;

    /**
     * @var string|null
     */
    protected $scopeType;

    /**
     * Construct
     *
     * @param Layout\ScheduledStructure\Helper $helper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\ScopeResolverInterface $scopeResolver
     * @param string|null $scopeType
     */
    public function __construct(
        Layout\ScheduledStructure\Helper $helper,
        App\Config\ScopeConfigInterface $scopeConfig,
        App\ScopeResolverInterface $scopeResolver,
        $scopeType = null
    ) {
        $this->layoutHelper = $helper;
        $this->scopeConfig = $scopeConfig;
        $this->scopeResolver = $scopeResolver;
        $this->scopeType = $scopeType;
    }

    /**
     * {@inheritdoc}
     *
     * @return string[]
     */
    public function getSupportedNodes()
    {
        return [self::TYPE_UI_COMPONENT];
    }

    /**
     * {@inheritdoc}
     *
     * @param Context $readerContext
     * @param Layout\Element $currentElement
     * @param Layout\Element $parentElement
     * @return $this
     */
    public function process(Context $readerContext, Layout\Element $currentElement, Layout\Element $parentElement)
    {
        $scheduledStructure = $readerContext->getScheduledStructure();
        $referenceName = $this->layoutHelper->scheduleStructure(
            $readerContext->getScheduledStructure(),
            $currentElement,
            $parentElement
        );
        $scheduledStructure->setStructureElementData($referenceName, [
            'attributes' => $this->getAttributes($currentElement)
        ]);
        $configPath = (string)$currentElement->getAttribute('ifconfig');
        if (!empty($configPath)
            && !$this->scopeConfig->isSetFlag($configPath, $this->scopeType, $this->scopeResolver->getScope())
        ) {
            $scheduledStructure->setElementToRemoveList($referenceName);
        }
        return $this;
    }

    /**
     * Get ui component attributes
     *
     * @param Layout\Element $element
     * @return array
     */
    protected function getAttributes(Layout\Element $element)
    {
        $attributes = [];
        foreach ($this->attributes as $attributeName) {
            $attributes[$attributeName] = (string)$element->getAttribute($attributeName);
        }
        return $attributes;
    }
}
