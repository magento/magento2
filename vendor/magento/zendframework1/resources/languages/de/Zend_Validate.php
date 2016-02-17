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
    "Invalid type given. String, integer or float expected" => "Ungültiger Typ angegeben. String, Integer oder Float erwartet",
    "'%value%' contains characters which are non alphabetic and no digits" => "'%value%' enthält Zeichen welche keine Buchstaben und keine Ziffern sind",
    "'%value%' is an empty string" => "'%value%' ist ein leerer String",

    // Zend_Validate_Alpha
    "Invalid type given. String expected" => "Ungültiger Typ angegeben. String erwartet",
    "'%value%' contains non alphabetic characters" => "'%value%' enthält Zeichen welche keine Buchstaben sind",
    "'%value%' is an empty string" => "'%value%' ist ein leerer String",

    // Zend_Validate_Barcode
    "'%value%' failed checksum validation" => "'%value%' hat die Prüfung der Checksumme nicht bestanden",
    "'%value%' contains invalid characters" => "'%value%' enthält ungültige Zeichen",
    "'%value%' should have a length of %length% characters" => "'%value%' sollte eine Länge von %length% Zeichen haben",
    "Invalid type given. String expected" => "Ungültiger Typ angegeben. String erwartet",

    // Zend_Validate_Between
    "'%value%' is not between '%min%' and '%max%', inclusively" => "'%value%' ist nicht zwischen '%min%' und '%max%', inklusive diesen Werten",
    "'%value%' is not strictly between '%min%' and '%max%'" => "'%value%' ist nicht strikt zwischen '%min%' und '%max%'",

    // Zend_Validate_Callback
    "'%value%' is not valid" => "'%value%' ist nicht gültig",
    "An exception has been raised within the callback" => "Eine Exception wurde im Callback geworfen",

    // Zend_Validate_Ccnum
    "'%value%' must contain between 13 and 19 digits" => "'%value%' muss zwischen 13 und 19 Ziffern enthalten",
    "Luhn algorithm (mod-10 checksum) failed on '%value%'" => "Der Luhn Algorithmus (Mod-10 Checksumme) ist auf '%value%' fehlgeschlagen",

    // Zend_Validate_CreditCard
    "'%value%' seems to contain an invalid checksum" => "'%value%' scheint eine ungültige Prüfsumme zu enthalten",
    "'%value%' must contain only digits" => "'%value%' darf nur Ziffern enthalten",
    "Invalid type given. String expected" => "Ungültiger Typ angegeben. String erwartet",
    "'%value%' contains an invalid amount of digits" => "'%value%' enthält eine ungültige Anzahl an Ziffern",
    "'%value%' is not from an allowed institute" => "'%value%' ist nicht von einem der erlaubten Institute",
    "'%value%' seems to be an invalid creditcard number" => "'%value%' scheint eine ungültige Kreditkarten-Nummer zu sein",
    "An exception has been raised while validating '%value%'" => "Eine Exception wurde wärend der Prüfung von '%value%' geworfen",

    // Zend_Validate_Date
    "Invalid type given. String, integer, array or Zend_Date expected" => "Ungültiger Typ angegeben. String, Integer, Array oder Zend_Date erwartet",
    "'%value%' does not appear to be a valid date" => "'%value%' scheint kein gültiges Datum zu sein",
    "'%value%' does not fit the date format '%format%'" => "'%value%' passt nicht in das angegebene Datumsformat '%format%'",

    // Zend_Validate_Db_Abstract
    "No record matching '%value%' was found" => "Es wurde kein Eintrag gefunden der '%value%' entspricht",
    "A record matching '%value%' was found" => "Ein Eintrag der '%value%' entspricht wurde gefunden",

    // Zend_Validate_Digits
    "Invalid type given. String, integer or float expected" => "Ungültiger Typ angegeben. String, Integer oder Float erwartet",
    "'%value%' must contain only digits" => "'%value%' darf nur Ziffern enthalten",
    "'%value%' is an empty string" => "'%value%' ist ein leerer String",

    // Zend_Validate_EmailAddress
    "Invalid type given. String expected" => "Ungültiger Typ angegeben. String erwartet",
    "'%value%' is not a valid email address in the basic format local-part@hostname" => "'%value%' ist keine gültige Emailadresse im Basisformat local-part@hostname",
    "'%hostname%' is not a valid hostname for email address '%value%'" => "'%hostname%' ist kein gültiger Hostname für die Emailadresse '%value%'",
    "'%hostname%' does not appear to have a valid MX record for the email address '%value%'" => "'%hostname%' scheint keinen gültigen MX Eintrag für die Emailadresse '%value%' zu haben",
    "'%hostname%' is not in a routable network segment. The email address '%value%' should not be resolved from public network" => "'%hostname%' ist in keinem routebaren Netzwerksegment. Die Emailadresse '%value%' sollte nicht vom öffentlichen Netz aus aufgelöst werden",
    "'%localPart%' can not be matched against dot-atom format" => "'%localPart%' passt nicht auf das dot-atom Format",
    "'%localPart%' can not be matched against quoted-string format" => "'%localPart%' passt nicht auf das quoted-string Format",
    "'%localPart%' is not a valid local part for email address '%value%'" => "'%localPart%' ist kein gültiger lokaler Teil für die Emailadresse '%value%'",
    "'%value%' exceeds the allowed length" => "'%value%' ist länger als erlaubt",

    // Zend_Validate_File_Count
    "Too many files, maximum '%max%' are allowed but '%count%' are given" => "Zu viele Dateien. Maximal '%max%' sind erlaubt aber '%count%' wurden angegeben",
    "Too few files, minimum '%min%' are expected but '%count%' are given" => "Zu wenige Dateien. Minimal '%min%' wurden erwartet aber nur '%count%' wurden angegeben",

    // Zend_Validate_File_Crc32
    "File '%value%' does not match the given crc32 hashes" => "Die Datei '%value%' passt nicht auf die angegebenen Crc32 Hashes",
    "A crc32 hash could not be evaluated for the given file" => "Für die angegebene Datei konnte kein Crc32 Hash evaluiert werden",
    "File '%value%' is not readable or does not exist" => "Die Datei '%value%' konnte nicht gelesen werden oder existiert nicht",

    // Zend_Validate_File_ExcludeExtension
    "File '%value%' has a false extension" => "Die Datei '%value%' hat eine falsche Erweiterung",
    "File '%value%' is not readable or does not exist" => "Die Datei '%value%' konnte nicht gelesen werden oder existiert nicht",

    // Zend_Validate_File_ExcludeMimeType
    "File '%value%' has a false mimetype of '%type%'" => "Die Datei '%value%' hat einen falschen Mimetyp von '%type%'",
    "The mimetype of file '%value%' could not be detected" => "Der Mimetyp der Datei '%value%' konnte nicht erkannt werden",
    "File '%value%' is not readable or does not exist" => "Die Datei '%value%' konnte nicht gelesen werden oder existiert nicht",

    // Zend_Validate_File_Exists
    "File '%value%' does not exist" => "Die Datei '%value%' existiert nicht",

    // Zend_Validate_File_Extension
    "File '%value%' has a false extension" => "Die Datei '%value%' hat eine falsche Erweiterung",
    "File '%value%' is not readable or does not exist" => "Die Datei '%value%' konnte nicht gelesen werden oder existiert nicht",

    // Zend_Validate_File_FilesSize
    "All files in sum should have a maximum size of '%max%' but '%size%' were detected" => "Alle Dateien sollten in Summe eine maximale Größe von '%max%' haben, aber es wurde '%size%' erkannt",
    "All files in sum should have a minimum size of '%min%' but '%size%' were detected" => "Alle Dateien sollten in Summe eine minimale Größe von '%min%' haben, aber es wurde '%size%' erkannt",
    "One or more files can not be read" => "Ein oder mehrere Dateien konnten nicht gelesen werden",

    // Zend_Validate_File_Hash
    "File '%value%' does not match the given hashes" => "Die Datei '%value%' passt nicht auf die angegebenen Hashes",
    "A hash could not be evaluated for the given file" => "Für die angegebene Datei konnte kein Hash evaluiert werden",
    "File '%value%' is not readable or does not exist" => "Die Datei '%value%' konnte nicht gelesen werden oder existiert nicht",

    // Zend_Validate_File_ImageSize
    "Maximum allowed width for image '%value%' should be '%maxwidth%' but '%width%' detected" => "Die maximal erlaubte Breite für das Bild '%value%' ist '%maxwidth%', aber es wurde '%width%' erkannt",
    "Minimum expected width for image '%value%' should be '%minwidth%' but '%width%' detected" => "Die minimal erlaubte Breite für das Bild '%value%' ist '%minwidth%', aber es wurde '%width%' erkannt",
    "Maximum allowed height for image '%value%' should be '%maxheight%' but '%height%' detected" => "Die maximal erlaubte Höhe für das Bild '%value%' ist '%maxheight%', aber es wurde '%height%' erkannt",
    "Minimum expected height for image '%value%' should be '%minheight%' but '%height%' detected" => "Die minimal erlaubte Höhe für das Bild '%value%' ist '%minheight%', aber es wurde '%height%' erkannt",
    "The size of image '%value%' could not be detected" => "Die Größe des Bildes '%value%' konnte nicht erkannt werden",
    "File '%value%' is not readable or does not exist" => "Die Datei '%value%' konnte nicht gelesen werden oder existiert nicht",

    // Zend_Validate_File_IsCompressed
    "File '%value%' is not compressed, '%type%' detected" => "Die Datei '%value%' ist nicht komprimiert. Es wurde '%type%' erkannt",
    "The mimetype of file '%value%' could not be detected" => "Der Mimetyp der Datei '%value%' konnte nicht erkannt werden",
    "File '%value%' is not readable or does not exist" => "Die Datei '%value%' konnte nicht gelesen werden oder existiert nicht",

    // Zend_Validate_File_IsImage
    "File '%value%' is no image, '%type%' detected" => "Die Datei '%value%' ist kein Bild. Es wurde '%type%' erkannt",
    "The mimetype of file '%value%' could not be detected" => "Der Mimetyp der Datei '%value%' konnte nicht erkannt werden",
    "File '%value%' is not readable or does not exist" => "Die Datei '%value%' konnte nicht gelesen werden oder existiert nicht",

    // Zend_Validate_File_Md5
    "File '%value%' does not match the given md5 hashes" => "Die Datei '%value%' passt nicht auf die angegebenen Md5 Hashes",
    "A md5 hash could not be evaluated for the given file" => "Für die angegebene Datei konnte kein Md5 Hash evaluiert werden",
    "File '%value%' is not readable or does not exist" => "Die Datei '%value%' konnte nicht gelesen werden oder existiert nicht",

    // Zend_Validate_File_MimeType
    "File '%value%' has a false mimetype of '%type%'" => "Die Datei '%value%' hat einen falschen Mimetyp von '%type%'",
    "The mimetype of file '%value%' could not be detected" => "Der Mimetyp der Datei '%value%' konnte nicht erkannt werden",
    "File '%value%' is not readable or does not exist" => "Die Datei '%value%' konnte nicht gelesen werden oder existiert nicht",

    // Zend_Validate_File_NotExists
    "File '%value%' exists" => "Die Datei '%value%' existiert bereits",

    // Zend_Validate_File_Sha1
    "File '%value%' does not match the given sha1 hashes" => "Die Datei '%value%' passt nicht auf die angegebenen Sha1 Hashes",
    "A sha1 hash could not be evaluated for the given file" => "Für die angegebene Datei konnte kein Sha1 Hash evaluiert werden",
    "File '%value%' is not readable or does not exist" => "Die Datei '%value%' konnte nicht gelesen werden oder existiert nicht",

    // Zend_Validate_File_Size
    "Maximum allowed size for file '%value%' is '%max%' but '%size%' detected" => "Die maximal erlaubte Größe für die Datei '%value%' ist '%max%', aber es wurde '%size%' entdeckt",
    "Minimum expected size for file '%value%' is '%min%' but '%size%' detected" => "Die mindestens erwartete Größe für die Datei '%value%' ist '%min%', aber es wurde '%size%' entdeckt",
    "File '%value%' is not readable or does not exist" => "Die Datei '%value%' konnte nicht gelesen werden oder existiert nicht",

    // Zend_Validate_File_Upload
    "File '%value%' exceeds the defined ini size" => "Die Datei '%value%' übersteigt die definierte Größe in der Konfiguration",
    "File '%value%' exceeds the defined form size" => "Die Datei '%value%' übersteigt die definierte Größe des Formulars",
    "File '%value%' was only partially uploaded" => "Die Datei '%value%' wurde nur teilweise hochgeladen",
    "File '%value%' was not uploaded" => "Die Datei '%value%' wurde nicht hochgeladen",
    "No temporary directory was found for file '%value%'" => "Für die Datei '%value%' wurde kein temporäres Verzeichnis gefunden",
    "File '%value%' can't be written" => "Die Datei '%value%' konnte nicht geschrieben werden",
    "A PHP extension returned an error while uploading the file '%value%'" => "Eine PHP Erweiterung retournierte einen Fehler wärend die Datei '%value%' hochgeladen wurde",
    "File '%value%' was illegally uploaded. This could be a possible attack" => "Die Datei '%value%' wurde illegal hochgeladen. Dies könnte eine mögliche Attacke sein",
    "File '%value%' was not found" => "Die Datei '%value%' wurde nicht gefunden",
    "Unknown error while uploading file '%value%'" => "Ein unbekannter Fehler ist aufgetreten wärend die Datei '%value%' hochgeladen wurde",

    // Zend_Validate_File_WordCount
    "Too much words, maximum '%max%' are allowed but '%count%' were counted" => "Zu viele Wörter. Maximal '%max%' sind erlaubt, aber '%count%' wurden gezählt",
    "Too less words, minimum '%min%' are expected but '%count%' were counted" => "Zu wenige Wörter. Mindestens '%min%' wurden erwartet, aber '%count%' wurden gezählt",
    "File '%value%' is not readable or does not exist" => "Die Datei '%value%' konnte nicht gelesen werden oder existiert nicht",

    // Zend_Validate_Float
    "Invalid type given. String, integer or float expected" => "Ungültiger Typ angegeben. String, Integer oder Float erwartet",
    "'%value%' does not appear to be a float" => "'%value%' scheint kein Float zu sein",

    // Zend_Validate_GreaterThan
    "'%value%' is not greater than '%min%'" => "'%value%' ist nicht größer als '%min%'",

    // Zend_Validate_Hex
    "Invalid type given. String expected" => "Ungültiger Typ angegeben. String erwartet",
    "'%value%' has not only hexadecimal digit characters" => "'%value%' enthält nicht nur hexadezimale Ziffern",

    // Zend_Validate_Hostname
    "Invalid type given. String expected" => "Ungültiger Typ angegeben. String erwartet",
    "'%value%' appears to be an IP address, but IP addresses are not allowed" => "'%value%' scheint eine IP Adresse zu sein, aber IP Adressen sind nicht erlaubt",
    "'%value%' appears to be a DNS hostname but cannot match TLD against known list" => "'%value%' scheint ein DNS Hostname zu sein, aber die TLD wurde in der bekannten Liste nicht gefunden",
    "'%value%' appears to be a DNS hostname but contains a dash in an invalid position" => "'%value%' scheint ein DNS Hostname zu sein, enthält aber einen Bindestrich an einer ungültigen Position",
    "'%value%' appears to be a DNS hostname but cannot match against hostname schema for TLD '%tld%'" => "'%value%' scheint ein DNS Hostname zu sein, passt aber nicht in das Hostname Schema für die TLD '%tld%'",
    "'%value%' appears to be a DNS hostname but cannot extract TLD part" => "'%value%' scheint ein DNS Hostname zu sein, aber der TLD Teil konnte nicht extrahiert werden",
    "'%value%' does not match the expected structure for a DNS hostname" => "'%value%' passt nicht in die erwartete Struktur für einen DNS Hostname",
    "'%value%' does not appear to be a valid local network name" => "'%value%' scheint kein gültiger lokaler Netzerkname zu sein",
    "'%value%' appears to be a local network name but local network names are not allowed" => "'%value%' scheint ein lokaler Netzwerkname zu sein, aber lokale Netzwerknamen sind nicht erlaubt",
    "'%value%' appears to be a DNS hostname but the given punycode notation cannot be decoded" => "'%value%' scheint ein DNS Hostname zu sein, aber die angegebene Punycode Schreibweise konnte nicht dekodiert werden",

    // Zend_Validate_Iban
    "Unknown country within the IBAN '%value%'" => "Unbekanntes Land in der IBAN '%value%'",
    "'%value%' has a false IBAN format" => "'%value%' enthält ein falsches IBAN Format",
    "'%value%' has failed the IBAN check" => "Die IBAN Prüfung ist für '%value%' fehlgeschlagen",

    // Zend_Validate_Identical
    "The two given tokens do not match" => "Die zwei angegebenen Token stimmen nicht überein",
    "No token was provided to match against" => "Es wurde kein Token angegeben gegen den geprüft werden kann",

    // Zend_Validate_InArray
    "'%value%' was not found in the haystack" => "'%value%' wurde im Haystack nicht gefunden",

    // Zend_Validate_Int
    "Invalid type given. String or integer expected" => "Ungültiger Typ angegeben. String oder Integer erwartet",
    "'%value%' does not appear to be an integer" => "'%value%' scheint kein Integer zu sein",

    // Zend_Validate_Ip
    "Invalid type given. String expected" => "Ungültiger Typ angegeben. String erwartet",
    "'%value%' does not appear to be a valid IP address" => "'%value%' scheint keine gültige IP Adresse zu sein",

    // Zend_Validate_Isbn
    "Invalid type given. String or integer expected" => "Ungültiger Typ angegeben. String oder Integer erwartet",
    "'%value%' is not a valid ISBN number" => "'%value%' ist keine gültige ISBN Nummer",

    // Zend_Validate_LessThan
    "'%value%' is not less than '%max%'" => "'%value%' ist nicht weniger als '%max%'",

    // Zend_Validate_NotEmpty
    "Invalid type given. String, integer, float, boolean or array expected" => "Ungültiger Typ angegeben. String, Integer, Float, Boolean oder Array erwartet",
    "Value is required and can't be empty" => "Es wird ein Wert benötigt. Dieser darf nicht leer sein",

    // Zend_Validate_PostCode
    "Invalid type given. String or integer expected" => "Ungültiger Typ angegeben. String oder Integer erwartet",
    "'%value%' does not appear to be a postal code" => "'%value%' scheint keine gültige Postleitzahl zu sein",

    // Zend_Validate_Regex
    "Invalid type given. String, integer or float expected" => "Ungültiger Typ angegeben. String, Integer oder Float erwartet",
    "'%value%' does not match against pattern '%pattern%'" => "'%value%' scheint nicht auf das Pattern '%pattern%' zu passen",
    "There was an internal error while using the pattern '%pattern%'" => "Es gab einen internen Fehler bei der Verwendung des Patterns '%pattern%'",

    // Zend_Validate_Sitemap_Changefreq
    "'%value%' is not a valid sitemap changefreq" => "'%value%' ist keine gültige Changefreq für Sitemap",
    "Invalid type given. String expected" => "Ungültiger Typ angegeben. String erwartet",

    // Zend_Validate_Sitemap_Lastmod
    "'%value%' is not a valid sitemap lastmod" => "'%value%' ist keine gültige Lastmod für Sitemap",
    "Invalid type given. String expected" => "Ungültiger Typ angegeben. String erwartet",

    // Zend_Validate_Sitemap_Loc
    "'%value%' is not a valid sitemap location" => "'%value%' ist keine gültige Location für Sitemap",
    "Invalid type given. String expected" => "Ungültiger Typ angegeben. String erwartet",

    // Zend_Validate_Sitemap_Priority
    "'%value%' is not a valid sitemap priority" => "'%value%' ist keine gültige Priority für Sitemap",
    "Invalid type given. Numeric string, integer or float expected" => "Ungültiger Typ angegeben. Nummerischer String, Integer oder Float erwartet",

    // Zend_Validate_StringLength
    "Invalid type given. String expected" => "Ungültiger Typ angegeben. String erwartet",
    "'%value%' is less than %min% characters long" => "'%value%' ist weniger als %min% Zeichen lang",
    "'%value%' is more than %max% characters long" => "'%value%' ist mehr als %max% Zeichen lang",
);
