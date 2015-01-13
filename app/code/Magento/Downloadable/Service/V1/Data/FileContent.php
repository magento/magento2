<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Service\V1\Data;

use Magento\Framework\Api\AbstractExtensibleObject;

/**
 * @codeCoverageIgnore
 */
class FileContent extends AbstractExtensibleObject
{
    const DATA = 'data';
    const NAME = 'name';

    /**
     * Retrieve data (base64 encoded content)
     *
     * @return string
     */
    public function getData()
    {
        return $this->_get(self::DATA);
    }

    /**
     * Retrieve file name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_get(self::NAME);
    }
}
