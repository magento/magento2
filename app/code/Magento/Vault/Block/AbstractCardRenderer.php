<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Block;

use Magento\Framework\View\Element\Template;
use Magento\Payment\Model\CcConfigProvider;

/**
 * Class AbstractCardRenderer
 * @api
 * @since 2.1.0
 */
abstract class AbstractCardRenderer extends AbstractTokenRenderer implements CardRendererInterface
{
    /**
     * @var CcConfigProvider
     * @since 2.1.0
     */
    private $iconsProvider;

    /**
     * Constructor
     *
     * @param Template\Context $context
     * @param CcConfigProvider $iconsProvider
     * @param array $data
     * @since 2.1.0
     */
    public function __construct(
        Template\Context $context,
        CcConfigProvider $iconsProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->iconsProvider = $iconsProvider;
    }

    /**
     * @param string $type
     * @return array
     * @since 2.1.0
     */
    protected function getIconForType($type)
    {
        if (isset($this->iconsProvider->getIcons()[$type])) {
            return $this->iconsProvider->getIcons()[$type];
        }

        return [
            'url' => '',
            'width' => 0,
            'height' => 0
        ];
    }
}
