<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\FixtureGenerator;

/**
 * Generate entity template which is used for entity generation
 * @since 2.2.0
 */
interface TemplateEntityGeneratorInterface
{
    /**
     * @return \Magento\Framework\Model\AbstractModel
     * @since 2.2.0
     */
    public function generateEntity();
}
