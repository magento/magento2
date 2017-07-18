<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
