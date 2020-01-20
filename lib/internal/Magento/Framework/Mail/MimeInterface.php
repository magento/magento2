<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Mail;

/**
 * Interface MimeInterface used providing constants
 *
 * @see \Zend\Mime\Mime
 */
interface MimeInterface
{
    // @codingStandardsIgnoreStart
    public const TYPE_OCTET_STREAM = 'application/octet-stream';
    public const TYPE_TEXT = 'text/plain';
    public const TYPE_HTML = 'text/html';
    public const ENCODING_7BIT = '7bit';
    public const ENCODING_8BIT = '8bit';
    public const ENCODING_QUOTED_PRINTABLE = 'quoted-printable';
    public const ENCODING_BASE64 = 'base64';
    public const DISPOSITION_ATTACHMENT = 'attachment';
    public const DISPOSITION_INLINE = 'inline';
    public const LINE_LENGTH = 72;
    public const LINE_END = "\n";
    public const MULTIPART_ALTERNATIVE = 'multipart/alternative';
    public const MULTIPART_MIXED = 'multipart/mixed';
    public const MULTIPART_RELATED = 'multipart/related';
    public const CHARSET_REGEX = '#=\?(?P<charset>[\x21\x23-\x26\x2a\x2b\x2d\x5e\5f\60\x7b-\x7ea-zA-Z0-9]+)\?(?P<encoding>[\x21\x23-\x26\x2a\x2b\x2d\x5e\5f\60\x7b-\x7ea-zA-Z0-9]+)\?(?P<text>[\x21-\x3e\x40-\x7e]+)#';
    // @codingStandardsIgnoreEnd
}
