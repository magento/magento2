<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\HTTP;

/**
 * Support class for MultiPart Mime Messages
 */
class Mime
{
    const TYPE_OCTETSTREAM         = 'application/octet-stream';
    const TYPE_TEXT                = 'text/plain';
    const TYPE_HTML                = 'text/html';
    const ENCODING_7BIT            = '7bit';
    const ENCODING_8BIT            = '8bit';
    const ENCODING_QUOTEDPRINTABLE = 'quoted-printable';
    const ENCODING_BASE64          = 'base64';
    const DISPOSITION_ATTACHMENT   = 'attachment';
    const DISPOSITION_INLINE       = 'inline';
    const LINELENGTH               = 72;
    const LINEEND                  = "\n";
    const MULTIPART_ALTERNATIVE    = 'multipart/alternative';
    const MULTIPART_MIXED          = 'multipart/mixed';
    const MULTIPART_RELATED        = 'multipart/related';
}
