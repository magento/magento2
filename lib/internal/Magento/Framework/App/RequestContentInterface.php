<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

/**
 * Interface provides access to parsed request data as well as to the request textual representation.
 * This interface exists to provide backward compatibility.
 * Direct usage of RequestInterface and PlainTextRequestInterface is preferable.
 *
 * @api
 */
interface RequestContentInterface extends RequestInterface, PlainTextRequestInterface
{

}
