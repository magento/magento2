<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
