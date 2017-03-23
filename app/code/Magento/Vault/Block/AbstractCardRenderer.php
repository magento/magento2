<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Block;

use Magento\Framework\View\Element\Template;
use Magento\Payment\Model\CcConfigProvider;

/**
 * Class AbstractCardRenderer
 * @api
 */
abstract class AbstractCardRenderer extends AbstractTokenRenderer implements CardRendererInterface
{
    /**
     * @var CcConfigProvider
     */
    private $iconsProvider;

    /**
     * Constructor
     *
     * @param Template\Context $context
     * @param CcConfigProvider $iconsProvider
     * @param array $data
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
