<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Translate
 * @subpackage Ressource
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id:$
 */

/**
 * EN-Revision: 23772
 */
return array(
    // Zend_Validate_Alnum
    "Invalid type given. String, integer or float expected" => "不正な形式です。文字列、整数もしくは小数が期待されています",
    "'%value%' contains characters which are non alphabetic and no digits" => "'%value%' にアルファベットと数字以外の文字が含まれています",
    "'%value%' is an empty string" => "'%value%' は空の文字列です",

    // Zend_Validate_Alpha
    "Invalid type given. String expected" => "不正な形式です。文字列が期待されています",
    "'%value%' contains non alphabetic characters" => "'%value%' にアルファベット以外の文字が含まれています",
    "'%value%' is an empty string" => "'%value%' は空の文字列です",

    // Zend_Validate_Barcode
    "'%value%' failed checksum validation" => "'%value%' はチェックサムが一致していません",
    "'%value%' contains invalid characters" => "'%value%' は不正な文字を含んでいます",
    "'%value%' should have a length of %length% characters" => "'%value%' は %length% 文字である必要があります",
    "Invalid type given. String expected" => "不正な形式です。文字列が期待されています",

    // Zend_Validate_Between
    "'%value%' is not between '%min%' and '%max%', inclusively" => "'%value%' は '%min%' 以上 '%max%' 以下ではありません",
    "'%value%' is not strictly between '%min%' and '%max%'" => "'%value%' は '%min%' 以下か '%max%' 以上です",

    // Zend_Validate_Callback
    "'%value%' is not valid" => "'%value%' は正しくありません",
    "An exception has been raised within the callback" => "コールバック内で例外が発生しました",

    // Zend_Validate_Ccnum
    "'%value%' must contain between 13 and 19 digits" => "'%value%' は 13 桁から 19 桁の数字でなければなりません",
    "Luhn algorithm (mod-10 checksum) failed on '%value%'" => "'%value%' でルーンアルゴリズム(mod-10 チェックサム)に失敗しました",

    // Zend_Validate_CreditCard
    "%value%' seems to contain an invalid checksum" => "'%value%' は不正なチェックサムを含んでいるようです",
    "'%value%' must contain only digits" => "'%value%' は数値だけで構成される必要があります",
    "Invalid type given. String expected" => "不正な形式です。文字列が期待されています",
    "'%value%' contains an invalid amount of digits" => "'%value%' は不正な桁数です",
    "'%value%' is not from an allowed institute" => "'%value%' は認可機関から許可されていません",
    "%value%' seems to be an invalid creditcard number" => "'%value%' は不正なクレジットカード番号を含んでいるようです",
    "An exception has been raised while validating '%value%" => "'%value%' を検証中に例外が発生しました",

    // Zend_Validate_Date
    "Invalid type given, value should be string, integer, array or Zend_Date" => "不正な形式です。値は文字列、整数、配列もしくは Zend_Date 形式である必要があります",
    "'%value%' does not appear to be a valid date" => "'%value%' は正しい日付ではないようです",
    "'%value%' does not fit the date format '%format%'" => "'%value%' は '%format%' フォーマットに一致していません",

    // Zend_Validate_Db_Abstract
    "No record matching '%value%' was found" => " '%value%' に一致するレコードは見つかりませんでした",
    "A record matching '%value%' was found" => " '%value%' に一致するレコードが見つかりました",

    // Zend_Validate_Digits
    "Invalid type given. String, integer or float expected" => "不正な形式です。文字列、整数または小数が期待されています",
    "%value%' must contain only digits" => "'%value%' は数字のみである必要があります",
    "'%value%' is an empty string" => "'%value%' は空の文字列です",

    // Zend_Validate_EmailAddress
    "Invalid type given. String expected" => "不正な形式です。文字列が期待されています",
    "'%value%' is no valid email address in the basic format local-part@hostname" => "'%value%' はメールアドレスの基本的な形式 local-part@hostname ではありません",
    "'%hostname%' is not a valid hostname for email address '%value%'" => "メールアドレス '%value%' 内の '%hostname%' は有効なホスト名ではありません",
    "'%hostname%' does not appear to have a valid MX record for the email address '%value%'" => "メールアドレス '%value%' 内の '%hostname%' は有効な MX レコードではないようです",
    "'%hostname%' is not in a routable network segment. The email address '%value%' should not be resolved from public network" => "'%hostname%' はネットワークセグメントにありません。メールアドレス '%value%' はパブリックなネットワークから名前解決できませんでした",
    "'%localPart%' can not be matched against dot-atom format" => "'%localPart%' はドットアトム形式ではありません",
    "'%localPart%' can not be matched against quoted-string format" => "'%localPart%' は引用文字列形式ではありません",
    "'%localPart%' is no valid local part for email address '%value%'" => "メールアドレス '%value%' 内の '%localPart%' は有効なローカルパートではありません",
    "'%value%' exceeds the allowed length" => "'%value%' は許された長さを超えています",

    // Zend_Validate_File_Count
    "Too many files, maximum '%max%' are allowed but '%count%' are given" => "ファイル数が多すぎます。最大 '%max%' まで許されていますが、 '%count%' 個指定しました",
    "Too few files, minimum '%min%' are expected but '%count%' are given" => "ファイル数が少なすぎます。最小 '%min%' 以上の必要がありますが、 '%count%' 個指定されていません",

    // Zend_Validate_File_Crc32
    "File '%value%' does not match the given crc32 hashes" => "ファイル '%value%' は crc32 ハッシュ値と一致しませんでした",
    "A crc32 hash could not be evaluated for the given file" => "ファイルに crc32 ハッシュ値が見つかりませんでした",
    "File '%value%' is not readable or does not exist" => "ファイル '%value%' は読み込めないかもしくは存在しません",

    // Zend_Validate_File_ExcludeExtension
    "File '%value%' has a false extension" => "ファイル '%value%' は誤った拡張子です",
    "File '%value%' is not readable or does not exist" => "ファイル '%value%' は読み込めないかもしくは存在しません",

    // Zend_Validate_File_ExcludeMimeType
    "File '%value%' has a false mimetype of '%type%'" => "ファイル '%value%' は誤った mimetype '%type%' です",
    "The mimetype of file '%value%' could not be detected" => "ファイル '%value%' の mimetype が見つかりませんでした",
    "File '%value%' is not readable or does not exist" => "ファイル '%value%' は読み込めないかもしくは存在しません",

    // Zend_Validate_File_Exists
    "File '%value%' does not exist" => "ファイル '%value%' は存在しません",

    // Zend_Validate_File_Extension
    "File '%value%' has a false extension" => "ファイル '%value%' は誤った拡張子です",
    "File '%value%' is not readable or does not exist" => "ファイル '%value%' は読み込めないかもしくは存在しません",

    // Zend_Validate_File_FilesSize
    "All files in sum should have a maximum size of '%max%' but '%size%' were detected" => "全てのファイルの合計は最大 '%max%' より小さい必要があります。しかしファイルサイズは '%size%' でした",
    "All files in sum should have a minimum size of '%min%' but '%size%' were detected" => "全てのファイルの合計は最小 '%min%' より大きい必要があります。しかしファイルサイズは '%size%' でした",
    "One or more files can not be read" => "ファイルを読み込めませんでした",

    // Zend_Validate_File_Hash
    "File '%value%' does not match the given hashes" => "ファイル '%value%' は設定されたハッシュ値と一致しませんでした",
    "A hash could not be evaluated for the given file" => "渡されたファイルのハッシュ値を評価できませんでした",
    "File '%value%' is not readable or does not exist" => "ファイル '%value%' は読み込めないかもしくは存在しません",

    // Zend_Validate_File_ImageSize
    "Maximum allowed width for image '%value%' should be '%maxwidth%' but '%width%' detected" => "画像 '%value%' の横幅は '%width%' でした。横幅は最大 '%maxwidth%' まで許されています",
    "Minimum expected width for image '%value%' should be '%minwidth%' but '%width%' detected" => "画像 '%value%' の横幅は '%width%' でした。横幅は最小 '%minwidth%' 以上である必要があります",
    "Maximum allowed height for image '%value%' should be '%maxheight%' but '%height%' detected" => "画像 '%value%' の高さは '%height%' でした。高さは最大 '%maxheight%' まで許されています",
    "Minimum expected height for image '%value%' should be '%minheight%' but '%height%' detected" => "画像 '%value%' の高さは '%height%' でした。高さは最小 '%minheight%' 以上である必要があります",
    "The size of image '%value%' could not be detected" => "画像 '%value%' の大きさを取得できませんでした",
    "File '%value%' is not readable or does not exist" => "ファイル '%value%' は読み込めないかもしくは存在しません",

    // Zend_Validate_File_IsCompressed
    "File '%value%' is not compressed, '%type%' detected" => " '%type%' が見つかりました。ファイル '%value%' は圧縮されていません",
    "The mimetype of file '%value%' could not be detected" => "ファイル '%value%' の Mimetype は見つかりませんでした",
    "File '%value%' is not readable or does not exist" => "ファイル '%value%' は読み込めないかもしくは存在しません",

    // Zend_Validate_File_IsImage
    "File '%value%' is no image, '%type%' detected" => "ファイル '%value%' は画像ではありません。 '%type%' です",
    "The mimetype of file '%value%' could not be detected" => "ファイル '%value%' の Mimetype は見つかりませんでした",
    "File '%value%' is not readable or does not exist" => "ファイル '%value%' は読み込めないかもしくは存在しません",

    // Zend_Validate_File_Md5
    "File '%value%' does not match the given md5 hashes" => "ファイル '%value%' は md5 ハッシュ値と一致していません",
    "A md5 hash could not be evaluated for the given file" => "渡されたファイルの md5 ハッシュ値を評価できませんでした",
    "File '%value%' is not readable or does not exist" => "ファイル '%value%' は読み込めないかもしくは存在しません",

    // Zend_Validate_File_MimeType
    "File '%value%' has a false mimetype of '%type%'" => "ファイル '%value%' は誤った MimeType '%type%' です",
    "The mimetype of file '%value%' could not be detected" => "ファイル '%value%' の Mimetype は見つかりませんでした",
    "File '%value%' is not readable or does not exist" => "ファイル '%value%' は読み込めないかもしくは存在しません",

    // Zend_Validate_File_NotExists
    "File '%value%' exists" => "ファイル '%value%' は存在しています",

    // Zend_Validate_File_Sha1
    "File '%value%' does not match the given sha1 hashes" => "ファイル '%value%' は sha1 ハッシュ値と一致していません",
    "A sha1 hash could not be evaluated for the given file" => "渡されたファイルの sha1 ハッシュ値を評価できませんでした",
    "File '%value%' is not readable or does not exist" => "ファイル '%value%' は読み込めないかもしくは存在しません",

    // Zend_Validate_File_Size
    "Maximum allowed size for file '%value%' is '%max%' but '%size%' detected" => "ファイルサイズは '%size%' です。ファイル '%value%' のサイズは最大 '%max%' まで許されています",
    "Minimum expected size for file '%value%' is '%min%' but '%size%' detected" => "ファイルサイズは '%size%' です。ファイル '%value%' のサイズは最小 '%min%' 以上必要です",
    "File '%value%' is not readable or does not exist" => "ファイル '%value%' は読み込めないかもしくは存在しません",

    // Zend_Validate_File_Upload
    "File '%value%' exceeds the defined ini size" => "ファイル '%value%' は ini で定義されたサイズを超えています",
    "File '%value%' exceeds the defined form size" => "ファイル '%value%' はフォームで定義されたサイズを超えています",
    "File '%value%' was only partially uploaded" => "ファイル '%value%' は一部のみしかアップロードされていません",
    "File '%value%' was not uploaded" => "ファイル '%value%' はアップロードされませんでした",
    "No temporary directory was found for file '%value%'" => "ファイル '%value%' をアップロードする一時ディレクトリが見つかりませんでした",
    "File '%value%' can't be written" => "ファイル '%value%' は書き込めませんでした",
    "A PHP extension returned an error while uploading the file '%value%'" => "ファイル '%value%' をアップロード中に拡張モジュールがエラーを応答しました",
    "File '%value%' was illegally uploaded. This could be a possible attack" => "ファイル '%value%' は不正なアップロードでした。攻撃の可能性があります",
    "File '%value%' was not found" => "ファイル '%value%' は見つかりませんでした",
    "Unknown error while uploading file '%value%'" => "ファイル '%value%' をアップロード中に未知のエラーが発生しました",

    // Zend_Validate_File_WordCount
    "Too much words, maximum '%max%' are allowed but '%count%' were counted" => "単語数 '%count%' が多過ぎます。最大で '%max%' 個が許されます",
    "Too less words, minimum '%min%' are expected but '%count%' were counted" => "単語数 '%count%' が少な過ぎます。少なくとも '%min%' 個必要です",
    "File '%value%' is not readable or does not exist" => "ファイル '%value%' は読み込めないかもしくは存在しません",

    // Zend_Validate_Float
    "Invalid type given. String, integer or float expected" => "不正な形式です。文字列、整数もしくは小数が期待されています",
    "'%value%' does not appear to be a float" => " '%value%' は小数ではないようです",

    // Zend_Validate_GreaterThan
    "'%value%' is not greater than '%min%'" => " '%value%' は '%min%' より大きくありません",

    // Zend_Validate_Hex
    "Invalid type given. String expected" => "不正な形式です。文字列が期待されています",
    "'%value%' has not only hexadecimal digit characters" => " '%value%' は 16 進文字列以外を含んでいます",

    // Zend_Validate_Hostname
    "Invalid type given. String expected" => "不正な形式です。文字列が期待されています",
    "'%value%' appears to be an IP address, but IP addresses are not allowed" => " '%value%' は IP アドレスのようですが、 IP アドレスは許されていません",
    "'%value%' appears to be a DNS hostname but cannot match TLD against known list" => " '%value%' は DNS ホスト名のようですが、 TLD が一覧に見つかりません",
    "'%value%' appears to be a DNS hostname but contains a dash in an invalid position" => " '%value%' は DNS ホスト名のようですが不正な位置にダッシュがあります",
    "'%value%' appears to be a DNS hostname but cannot match against hostname schema for TLD '%tld%'" => " '%value%' は DNS ホスト名のようですが TLD '%tld%' のホスト名スキーマと一致していません",
    "'%value%' appears to be a DNS hostname but cannot extract TLD part" => " '%value%' は DNS ホスト名のようですが TLD 部を展開できません",
    "'%value%' does not match the expected structure for a DNS hostname" => " '%value%' は DNS ホスト名の構造に一致していません",
    "'%value%' does not appear to be a valid local network name" => " '%value%' は有効なローカルネットワーク名ではないようです",
    "'%value%' appears to be a local network name but local network names are not allowed" => " '%value%' はローカルネットワーク名のようですがローカルネットワーク名は許されていません",
    "'%value%' appears to be a DNS hostname but the given punycode notation cannot be decoded" => " '%value%' は DNS ホスト名のようですが、 punycode 変換ができませんでした",

    // Zend_Validate_Iban
    "Unknown country within the IBAN '%value%'" => "IBAN コード '%value%' に未知の国があります",
    "'%value%' has a false IBAN format" => " '%value%' は誤った IBAN 書式です",
    "'%value%' has failed the IBAN check" => " '%value%' は IBAN コードチェックに失敗しました",

    // Zend_Validate_Identical
    "The two given tokens do not match" => "2 つのトークンは一致しませんでした",
    "No token was provided to match against" => "チェックを行うためのトークンがありませんでした",

    // Zend_Validate_InArray
    "'%value%' was not found in the haystack" => " '%value%' が haystack の中に見つかりませんでした",

    // Zend_Validate_Int
    "Invalid type given. String or integer expected" => "不正な形式です。文字列または整数が期待されています",
    "'%value%' does not appear to be an integer" => " '%value%' は整数ではないようです",

    // Zend_Validate_Ip
    "Invalid type given. String expected" => "不正な形式です。文字列が期待されています",
    "'%value%' does not appear to be a valid IP address" => " '%value%' は IP アドレスではないようです",

    // Zend_Validate_Isbn
     "Invalid type given. String or integer expected" => "不正な形式です。文字列または整数が期待されています",
    "'%value%' is no valid ISBN number" => " '%value%' は ISBN 番号ではありません",

    // Zend_Validate_LessThan
    "'%value%' is not less than '%max%'" => " '%value%' は '%max%' 未満ではありません",

    // Zend_Validate_NotEmpty
    "Invalid type given. String, integer, float, boolean or array expected" => "不正な形式です。文字列、整数、小数、真偽値もしくは配列が期待されています",
    "Value is required and can't be empty" => "値は必須です。空値は許可されていません",

    // Zend_Validate_PostCode
    "Invalid type given. String or integer expected" => "不正な形式です。文字列もしくは整数が期待されています",
    "'%value%' does not appear to be a postal code" => " '%value%' は郵便番号でないようです",

    // Zend_Validate_Regex
    "Invalid type given. String, integer or float expected" => "不正な形式です。文字列、整数、もしくは小数が期待されています",
    "'%value%' does not match against pattern '%pattern%'" => " '%value%' はパターン '%pattern%' と一致していません",
    "There was an internal error while using the pattern '%pattern%'" => "正規表現パターン '%pattern%' を使用中に内部エラーが発生しました。",

    // Zend_Validate_Sitemap_Changefreq
    "'%value%' is no valid sitemap changefreq" => " '%value%' は正しいサイトマップの更新頻度ではありません",
    "Invalid type given. String expected" => "不正な形式です。文字列が期待されています",

    // Zend_Validate_Sitemap_Lastmod
    "'%value%' is no valid sitemap lastmod" => " '%value%' は正しいサイトマップの最終更新日ではありません",
    "Invalid type given. String expected" => "不正な形式です。文字列が期待されています",

    // Zend_Validate_Sitemap_Loc
    "'%value%' is no valid sitemap location" => " '%value%' は正しいサイトマップの位置ではありません",
    "Invalid type given. String expected" => "不正な形式です。文字列が期待されています",

    // Zend_Validate_Sitemap_Priority
    "'%value%' is no valid sitemap priority" => " '%value%' は正しいサイトマップの優先度ではありません",
    "Invalid type given. Numeric string, integer or float expected" => "不正な形式です。数字、整数もしくは小数が期待されています",

    // Zend_Validate_StringLength
    "Invalid type given. String expected" => "不正な形式です。文字列が期待されています",
    "'%value%' is less than %min% characters long" => " '%value%' は %min% 文字より短いです",
    "'%value%' is more than %max% characters long" => " '%value%' は %max% 文字より長いです",
);
