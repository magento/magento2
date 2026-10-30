<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Setup\Model\Description;

/**
 * Apply mixin to description
 */
class MixinManager
{
    /**
     * @var \Magento\Setup\Model\Description\Mixin\MixinFactory
     */
    private $mixinFactory;

    /**
     * @param \Magento\Setup\Model\Description\Mixin\MixinFactory $mixinFactory
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
     */
    public function apply($description, array $mixinList)
    {
        foreach ($mixinList as $mixinType) {
            $description = $this->mixinFactory->create($mixinType)->apply($description);
        }

        return $description;
    }
}
