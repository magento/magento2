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
 * EN-Revision: 22668
 */
return array(
    // Zend_Validate_Alnum
    "Invalid type given. String, integer or float expected" => "Chybný typ. Byl očekáván řetězec, celé nebo desetinné číslo",
    "'%value%' contains characters which are non alphabetic and no digits" => "'%value%' obsahuje i jiné znaky než písmena a číslice",
    "'%value%' is an empty string" => "'%value%' je prázdný řetězec",

    // Zend_Validate_Alpha
    "Invalid type given. String expected" => "Chybný typ. Byl očekáván řetězec",
    "'%value%' contains non alphabetic characters" => "'%value%' obsahuje i jiné znaky než písmena",
    "'%value%' is an empty string" => "'%value%' je prázdný řetězec",

    // Zend_Validate_Barcode
    "'%value%' failed checksum validation" => "'%value%' má chybný kontrolní součet",
    "'%value%' contains invalid characters" => "'%value%' obsahuje neplatné znaky",
    "'%value%' should have a length of %length% characters" => "'%value%' by měl mít délku %length% znaků",
    "Invalid type given. String expected" => "Chybný typ. Byl očekáván řetězec.",

    // Zend_Validate_Between
    "'%value%' is not between '%min%' and '%max%', inclusively" => "'%value%' není mezi '%min%' a '%max%', včetně",
    "'%value%' is not strictly between '%min%' and '%max%'" => "'%value%' není ostře mezi '%min%' a '%max%'",

    // Zend_Validate_Callback
    "'%value%' is not valid" => "Hodnota '%value%' není platná",
    "An exception has been raised within the callback" => "Během volání byla vyvolána výjimka",

    // Zend_Validate_Ccnum
    "'%value%' must contain between 13 and 19 digits" => "'%value%' musí obsahovat 13 až 19 číslic",
    "Luhn algorithm (mod-10 checksum) failed on '%value%'" => "Luhnův algoritmus (kontrolní součet mod-10) nevyšel pro '%value%'",

    // Zend_Validate_CreditCard
    "'%value%' seems to contain an invalid checksum" => "'%value%' obsahuje neplatný kontrolní součet",
    "'%value%' must contain only digits" => "'%value%' musí obsahovat jen čísla",
    "Invalid type given. String expected" => "Chybný typ. Byl očekáván řetězec",
    "'%value%' contains an invalid amount of digits" => "'%value%' obsahuje neplatný počet číslic",
    "'%value%' is not from an allowed institute" => "'%value%' není od povolené společnosti",
    "'%value%' seems to be an invalid creditcard number" => "'%value%' není platné číslo kreditní karty",
    "An exception has been raised while validating '%value%'" => "Během validace '%value%' byla vyvolána výjimka",

    // Zend_Validate_Date
    "Invalid type given. String, integer, array or Zend_Date expected" => "Chybný typ. Byl očekáván řetězec, číslo, pole nebo Zend_Date",
    "'%value%' does not appear to be a valid date" => "'%value%' není platné datum",
    "'%value%' does not fit the date format '%format%'" => "'%value%' neodpovídá formátu data '%format%'",

    // Zend_Validate_Db_Abstract
    "No record matching '%value%' was found" => "Nebyl nalezen žádný záznam odpovídající '%value%'",
    "A record matching '%value%' was found" => "Byl nalezen záznam odpovídající '%value%'",

    // Zend_Validate_Digits
    "Invalid type given. String, integer or float expected" => "Chybný typ. Byl očekáván řetězec, celé nebo desetinné číslo",
    "'%value%' must contain only digits" => "'%value%' musí obsahovat jen číslice",
    "'%value%' is an empty string" => "'%value%' je prázdný řetězec",

    // Zend_Validate_EmailAddress
    "Invalid type given. String expected" => "Chybný typ. Byl očekáván řetězec",
    "'%value%' is no valid email address in the basic format local-part@hostname" => "'%value%' není platná e-mailová adresa ve formátu local-part@hostname",
    "'%hostname%' is not a valid hostname for email address '%value%'" => "'%hostname%' není platné hostname pro emailovou adresu '%value%'",
    "'%hostname%' does not appear to have a valid MX record for the email address '%value%'" => "'%hostname%' neobsahuje platný MX záznam pro e-mailovou adresu '%value%'",
    "'%hostname%' is not in a routable network segment. The email address '%value%' should not be resolved from public network" => "'%hostname%' není v směrovatelném úseku sítě. E-mailová adresa '%value%' by neměla být požadována z veřejné sítě",
    "'%localPart%' can not be matched against dot-atom format" => "'%localPart%' nemůže být porovnán proti dot-atom formátu",
    "'%localPart%' can not be matched against quoted-string format" => "'%localPart%' nemůže být porovnán proti quoted-string formátu",
    "'%localPart%' is no valid local part for email address '%value%'" => "'%localPart%' není platná 'local part' pro e-mailovou adresu '%value%'",
    "'%value%' exceeds the allowed length" => "'%value%' překročil dovolenou délku",

    // Zend_Validate_File_Count
    "Too many files, maximum '%max%' are allowed but '%count%' are given" => "Příliš mnoho souborů. Maximum je '%max%', ale bylo zadáno '%count%'",
    "Too few files, minimum '%min%' are expected but '%count%' are given" => "Příliš málo souborů. Minimum je '%min%', ale byl zadáno jen '%count%'",

    // Zend_Validate_File_Crc32
    "File '%value%' does not match the given crc32 hashes" => "Soubor '%value%' neodpovídá zadanému crc32 hashi",
    "A crc32 hash could not be evaluated for the given file" => "Pro zadaný soubor nemohl být vypočítán crc32 hash",
    "File '%value%' is not readable or does not exist" => "Soubor '%value%' buď není čitelný nebo neexistuje",

    // Zend_Validate_File_ExcludeExtension
    "File '%value%' has a false extension" => "Soubor '%value%' má nesprávnou příponu",
    "File '%value%' is not readable or does not exist" => "Soubor '%value%' buď není čitelný nebo neexistuje",

    // Zend_Validate_File_ExcludeMimeType
    "File '%value%' has a false mimetype of '%type%'" => "Soubor '%value%' má nesprávný mimetyp '%type%'",
    "The mimetype of file '%value%' could not be detected" => "Mimetyp souboru '%value%' nebylo možné zjistit",
    "File '%value%' is not readable or does not exist" => "Soubor '%value%' buď není čitelný nebo neexistuje",

    // Zend_Validate_File_Exists
    "File '%value%' does not exist" => "Soubor '%value%' neexistuje",

    // Zend_Validate_File_Extension
    "File '%value%' has a false extension" => "Soubor '%value%' má nesprávnou příponu",
    "File '%value%' is not readable or does not exist" => "Soubor '%value%' buď není čitelný nebo neexistuje",

    // Zend_Validate_File_FilesSize
    "All files in sum should have a maximum size of '%max%' but '%size%' were detected" => "Součet velikostí všech souborů by měl být maximálně '%max%', ale je '%size%'",
    "All files in sum should have a minimum size of '%min%' but '%size%' were detected" => "Součet velikostí všech souborů by měl být nejméně '%min%', ale je '%size%'",
    "One or more files can not be read" => "Jeden nebo více souborů není možné načíst",

    // Zend_Validate_File_Hash
    "File '%value%' does not match the given hashes" => "Soubor '%value%' neodpovídané danému hashi",
    "A hash could not be evaluated for the given file" => "Hash nemohl být pro daný soubor vypočítán",
    "File '%value%' is not readable or does not exist" => "Soubor '%value%' buď není čitelný nebo neexistuje",

    // Zend_Validate_File_ImageSize
    "Maximum allowed width for image '%value%' should be '%maxwidth%' but '%width%' detected" => "Maximální šířka obrázku '%value%' by měla být '%maxwidth%', ale je '%width%'",
    "Minimum expected width for image '%value%' should be '%minwidth%' but '%width%' detected" => "Minimální šířka obrázku '%value%' by měla být '%minwidth%', ale je '%width%'",
    "Maximum allowed height for image '%value%' should be '%maxheight%' but '%height%' detected" => "Maximální výška obrázku '%value%' by měla být '%maxheight%', ale je '%height%'",
    "Minimum expected height for image '%value%' should be '%minheight%' but '%height%' detected" => "Minimální výška obrázku '%value%' by měla být '%minheight%', ale je '%height%'",
    "The size of image '%value%' could not be detected" => "Rozměry obrázku '%value%' nebylo možné zjistit",
    "File '%value%' is not readable or does not exist" => "Soubor '%value%' buď není čitelný nebo neexistuje",

    // Zend_Validate_File_IsCompressed
    "File '%value%' is not compressed, '%type%' detected" => "Soubor '%value%' není komprimovaný, ale '%type%'",
    "The mimetype of file '%value%' could not be detected" => "Mimetyp souboru '%value%' nebylo možné zjistit",
    "File '%value%' is not readable or does not exist" => "Soubor '%value%' buď není čitelný nebo neexistuje",

    // Zend_Validate_File_IsImage
    "File '%value%' is no image, '%type%' detected" => "Soubor '%value%' není obrázek, ale '%type%'",
    "The mimetype of file '%value%' could not be detected" => "Mimetyp souboru '%value%' nebylo možné zjistit",
    "File '%value%' is not readable or does not exist" => "Soubor '%value%' buď není čitelný nebo neexistuje",

    // Zend_Validate_File_Md5
    "File '%value%' does not match the given md5 hashes" => "Soubor '%value%' neodpovídá danému md5 hashi",
    "A md5 hash could not be evaluated for the given file" => "md5 hash nemohl být pro daný soubor vypočítán",
    "File '%value%' is not readable or does not exist" => "Soubor '%value%' buď není čitelný nebo neexistuje",

    // Zend_Validate_File_MimeType
    "File '%value%' has a false mimetype of '%type%'" => "Soubor '%value%' má neplatný mimetyp '%type%'",
    "The mimetype of file '%value%' could not be detected" => "Mimetyp souboru '%value%' nebylo možné zjistit",
    "File '%value%' is not readable or does not exist" => "Soubor '%value%' buď není čitelný nebo neexistuje",

    // Zend_Validate_File_NotExists
    "File '%value%' exists" => "Soubor '%value%' již existuje",

    // Zend_Validate_File_Sha1
    "File '%value%' does not match the given sha1 hashes" => "Soubor '%value%' neodpovídá danému sha1 hashi",
    "A sha1 hash could not be evaluated for the given file" => "sha1 hash nemohl být pro daný soubor vypočítán",
    "File '%value%' is not readable or does not exist" => "Soubor '%value%' buď není čitelný nebo neexistuje",

    // Zend_Validate_File_Size
    "Maximum allowed size for file '%value%' is '%max%' but '%size%' detected" => "Maximální povolená velikost souboru je '%max%', ale '%value%' má '%size%'",
    "Minimum expected size for file '%value%' is '%min%' but '%size%' detected" => "Minimální velikost souboru je '%min%', ale '%value%' má '%size%'",
    "File '%value%' is not readable or does not exist" => "Soubor '%value%' buď není čitelný nebo neexistuje",

    // Zend_Validate_File_Upload
    "File '%value%' exceeds the defined ini size" => "Soubor '%value%' překročil velikost definovanou v ini souboru",
    "File '%value%' exceeds the defined form size" => "Soubor '%value%' překročil velikost definovanou ve formuláři",
    "File '%value%' was only partially uploaded" => "Soubor '%value%' byl nahrán jen částečně",
    "File '%value%' was not uploaded" => "Soubor '%value%' nebyl nahrán",
    "No temporary directory was found for file '%value%'" => "Pro soubor '%value%' nebyl nalezen žádný dočasný adresář",
    "File '%value%' can't be written" => "Soubor '%value%' nemůže být zapsán",
    "A PHP extension returned an error while uploading the file '%value%'" => "PHP rozšíření vrátilo chybu během nahrávání souboru '%value%'",
    "File '%value%' was illegally uploaded. This could be a possible attack" => "Soubor '%value%' byl nedovoleně nahrán. Může se jednat o útok",
    "File '%value%' was not found" => "Soubor '%value%' nebyl nalezen",
    "Unknown error while uploading file '%value%'" => "Během nahrávání souboru '%value%' došlo k chybě",

    // Zend_Validate_File_WordCount
    "Too much words, maximum '%max%' are allowed but '%count%' were counted" => "Příliš mnoho slov. Je jich dovoleno maximálně '%max%', ale bylo zadáno '%count%'",
    "Too less words, minimum '%min%' are expected but '%count%' were counted" => "Příliš málo slov. Musí jich být alespoň '%min%', ale bylo zadáno jen '%count%'",
    "File '%value%' is not readable or does not exist" => "Soubor '%value%' buď není čitelný nebo neexistuje",

    // Zend_Validate_Float
    "Invalid type given. String, integer or float expected" => "Chybný typ. Byl očekáván řetězec, celé nebo desetinné číslo",
    "'%value%' does not appear to be a float" => "'%value%' není desetinné číslo",

    // Zend_Validate_GreaterThan
    "'%value%' is not greater than '%min%'" => "'%value%' není větší než '%min%'",

    // Zend_Validate_Hex
    "Invalid type given. String expected" => "Chybný typ. Byl očekáván řetězec",
    "'%value%' has not only hexadecimal digit characters" => "'%value%' neobsahuje jen znaky hexadecimálních čísel.",

    // Zend_Validate_Hostname
    "Invalid type given. String expected" => "Chybný typ. Byl očekáván řetězec",
    "'%value%' appears to be an IP address, but IP addresses are not allowed" => "'%value%' vypadá jako IP adresa, ale ty nejsou dovoleny",
    "'%value%' appears to be a DNS hostname but cannot match TLD against known list" => "'%value%' vypadá jako hostname, ale nemohl být ověřen proti známým TLD",
    "'%value%' appears to be a DNS hostname but contains a dash in an invalid position" => "'%value%' vypadá jako hostname, ale obsahuje pomlčku na nedovoleném místě",
    "'%value%' appears to be a DNS hostname but cannot match against hostname schema for TLD '%tld%'" => "'%value%' vypadá jako hostname, ale neodpovídá formátu hostname pro '%tld%'",
    "'%value%' appears to be a DNS hostname but cannot extract TLD part" => "'%value%' sice vypadá jako hostname, ale nemohu určit TLD",
    "'%value%' does not match the expected structure for a DNS hostname" => "'%value%' neodpovídá očekáváné struktuře hostname",
    "'%value%' does not appear to be a valid local network name" => "'%value%' nevypadá jako platné síťové jméno",
    "'%value%' appears to be a local network name but local network names are not allowed" => "'%value%' vypadá jako hostname lokální sítě, ty ale nejsou povoleny",
    "'%value%' appears to be a DNS hostname but the given punycode notation cannot be decoded" => "'%value%' vypadá jako DNS hostname ale zadanou punycode notaci není možné dekódovat",

    // Zend_Validate_Iban
    "Unknown country within the IBAN '%value%'" => "Neznámý stát v IBAN '%value%'",
    "'%value%' has a false IBAN format" => "'%value%' není platný formát IBAN",
    "'%value%' has failed the IBAN check" => "'%value%' neprošlo kontrolou IBAN",

    // Zend_Validate_Identical
    "The two given tokens do not match" => "Zadané položky nejsou shodné",
    "No token was provided to match against" => "Nebyla zadána položka pro porovnání",

    // Zend_Validate_InArray
    "'%value%' was not found in the haystack" => "'%value%' nebyla nalezena v seznamu",

    // Zend_Validate_Int
    "Invalid type given. String or integer expected" => "Chybný typ. Byl očekáván řetězec nebo celé číslo",
    "'%value%' does not appear to be an integer" => "'%value%' není celé číslo",

    // Zend_Validate_Ip
    "Invalid type given. String expected" => "Chybný typ. Byl očekáván řetězec",
    "'%value%' does not appear to be a valid IP address" => "'%value%' není platná IP adresa",

    // Zend_Validate_Isbn
    "Invalid type given. String or integer expected" => "Chybný typ. Byl očekáván řetězec nebo celé číslo",
    "'%value%' is no valid ISBN number" => "'%value%' není platné ISBN",

    // Zend_Validate_LessThan
    "'%value%' is not less than '%max%'" => "'%value%' není méně než '%max%'",

    // Zend_Validate_NotEmpty
    "Invalid type given. String, integer, float, boolean or array expected" => "Chybný typ. Byl očekáván řetězec, celé nebo desetinné číslo, boolean nebo pole",
    "Value is required and can't be empty" => "Položka je povinná a nesmí být prázdná",

    // Zend_Validate_PostCode
    "Invalid type given. String or integer expected" => "Chybný typ. Byl očekáván řetězec nebo celé číslo",
    "'%value%' does not appear to be a postal code" => "'%value%' nevypadá jako PSČ",

    // Zend_Validate_Regex
    "Invalid type given. String, integer or float expected" => "Chybný typ. Byl očekáván řetězec, celé nebo desetinné číslo",
    "'%value%' does not match against pattern '%pattern%'" => "'%value%' neodpovídá šabloně '%pattern%'",
    "There was an internal error while using the pattern '%pattern%'" => "Během zpracování šablony '%pattern%' došlo k interní chybě",

    // Zend_Validate_Sitemap_Changefreq
    "'%value%' is no valid sitemap changefreq" => "'%value%' není platné 'changefreq' pro sitemapu",
    "Invalid type given. String expected" => "Chybný typ. Byl očekáván řetězec",

    // Zend_Validate_Sitemap_Lastmod
    "'%value%' is no valid sitemap lastmod" => "'%value%' není platné 'lastmod' pro sitemapu",
    "Invalid type given. String expected" => "Chybný typ. Byl očekáván řetězec",

    // Zend_Validate_Sitemap_Loc
    "'%value%' is no valid sitemap location" => "'%value%' není platná 'location' pro sitemapu",
    "Invalid type given. String expected" => "Chybný typ. Byl očekáván řetězec",

    // Zend_Validate_Sitemap_Priority
    "'%value%' is no valid sitemap priority" => "'%value%' není platná 'priority' pro sitemapu",
    "Invalid type given. Numeric string, integer or float expected" => "Chybný typ. Byl očekáván číselný řetězec, celé nebo desetinné číslo.",

    // Zend_Validate_StringLength
    "Invalid type given. String expected" => "Chybný typ. Byl očekáván řetězec",
    "'%value%' is less than %min% characters long" => "'%value%' je kratší než %min% znaků",
    "'%value%' is more than %max% characters long" => "'%value%' je delší než %max% znaků",
);
