<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Setup\Model\FixtureGenerator;

/**
 * Generate entity template which is used for entity generation
 */
interface TemplateEntityGeneratorInterface
{
    /**
     * @return \Magento\Framework\Model\AbstractModel
     */
    public function generateEntity();
}
