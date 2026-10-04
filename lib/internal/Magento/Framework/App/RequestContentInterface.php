<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App;

/**
 * Interface provides access to parsed request data as well as to the request textual representation.
 * This interface exists to provide backward compatibility.
 * Direct usage of RequestInterface and PlainTextRequestInterface is preferable.
 *
 * @api
 * @since 101.0.0
 */
interface RequestContentInterface extends RequestInterface, PlainTextRequestInterface
{

}
