<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Xml;

use \ZendXml\Security as XmlSecurity;

/**
 * Class Security
 */
class Security
{
    /**
     * Security check loaded XML document
     *
     * @param string $xmlContent
     * @return bool
     */
    public function scan($xmlContent)
    {
        try {
            $result = XmlSecurity::scan($xmlContent);

            return is_object($result) || $result === true;
        } catch (\Exception $exception) {
            return false;
        }
    }
}
