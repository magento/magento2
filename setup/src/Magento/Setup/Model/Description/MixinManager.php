<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Description;

/**
 * Apply mixin to description
 * @since 2.2.0
 */
class MixinManager
{
    /**
     * @var \Magento\Setup\Model\Description\Mixin\MixinFactory
     * @since 2.2.0
     */
    private $mixinFactory;

    /**
     * @param \Magento\Setup\Model\Description\Mixin\MixinFactory $mixinFactory
     * @since 2.2.0
     */
    public function __construct(\Magento\Setup\Model\Description\Mixin\MixinFactory $mixinFactory)
    {
        $this->mixinFactory = $mixinFactory;
    }

    /**
     * Apply list of mixin to description
     *
     * @param string $description
     * @param array $mixinList
     * @return mixed
     * @since 2.2.0
     */
    public function apply($description, array $mixinList)
    {
        foreach ($mixinList as $mixinType) {
            $description = $this->mixinFactory->create($mixinType)->apply($description);
        }

        return $description;
    }
}
