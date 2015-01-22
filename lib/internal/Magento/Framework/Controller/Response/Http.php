<?php
/**
 * Magento's adapter for Zend Response class. Needed for proper DI functioning.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Controller\Response;

class Http extends \Zend_Controller_Response_Http
{
}
