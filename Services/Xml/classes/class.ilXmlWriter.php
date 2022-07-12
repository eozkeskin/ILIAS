<?php

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * XML writer class
 *
 * Class to simplify manual writing of xml documents.
 * It only supports writing xml sequentially, because the xml document
 * is saved in a string with no additional structure information.
 * The author is responsible for well-formedness and validity
 * of the xml document.
 *
 * @author  Matthias Rulinski <matthias.rulinski@mi.uni-koeln.de>
 * @version $Id$
 */
class ilXmlWriter
{
    private string $xmlStr;
    
    private string $version;
    
    private string $outEnc;
    
    private string $inEnc;
    
    private string $dtdDef = "";
    
    private string $stSheet = "";
    
    private string $genCmt = "Generated by ILIAS XmlWriter";
    
    public function __construct(
        string $version = "1.0",
        string $outEnc = "utf-8",
        string $inEnc = "utf-8"
    ) {
        // initialize xml string
        $this->xmlStr = "";
        
        // set properties
        $this->version = $version;
        $this->outEnc = $outEnc;
        $this->inEnc = $inEnc;
    }
    
    /**
     * Sets dtd definition
     */
    public function xmlSetDtdDef(string $dtdDef) : void
    {
        $this->dtdDef = $dtdDef;
    }
    
    /**
     * Sets stylesheet
     */
    private function xmlSetStSheet(string $stSheet) : void
    {
        $this->stSheet = $stSheet;
    }
    
    /**
     * Sets generated comment
     */
    public function xmlSetGenCmt(string $genCmt) : void
    {
        $this->genCmt = $genCmt;
    }
    
    /**
     * Escapes reserved characters
     */
    private function xmlEscapeData(string $data) : string
    {
        $position = 0;
        $length = strlen($data);
        $escapedData = "";
        
        for (; $position < $length;) {
            $character = substr($data, $position, 1);
            $code = Ord($character);
            
            switch ($code) {
                case 34:
                    $character = "&quot;";
                    break;
                
                case 38:
                    $character = "&amp;";
                    break;
                
                case 39:
                    $character = "&apos;";
                    break;
                
                case 60:
                    $character = "&lt;";
                    break;
                
                case 62:
                    $character = "&gt;";
                    break;
                
                default:
                    if ($code < 32) {
                        $character = ("&#" . $code . ";");
                    }
                    break;
            }
            
            $escapedData .= $character;
            $position++;
        }
        return $escapedData;
    }
    
    /**
     * Encodes text from input encoding into output encoding
     */
    private function xmlEncodeData(string $data) : string
    {
        if ($this->inEnc == $this->outEnc) {
            $encodedData = $data;
        } else {
            switch (strtolower($this->outEnc)) {
                case "utf-8":
                    if (strtolower($this->inEnc) == "iso-8859-1") {
                        $encodedData = utf8_encode($data);
                    } else {
                        die(
                            "<b>Error</b>: Cannot encode iso-8859-1 data in " . $this->outEnc .
                            " in <b>" . __FILE__ . "</b> on line <b>" . __LINE__ . "</b><br />"
                        );
                    }
                    break;
                
                case "iso-8859-1":
                    if (strtolower($this->inEnc) == "utf-8") {
                        $encodedData = utf8_decode($data);
                    } else {
                        die(
                            "<b>Error</b>: Cannot encode utf-8 data in " . $this->outEnc .
                            " in <b>" . __FILE__ . "</b> on line <b>" . __LINE__ . "</b><br />"
                        );
                    }
                    break;
                
                default:
                    die(
                        "<b>Error</b>: Cannot encode " . $this->inEnc . " data in " . $this->outEnc .
                        " in <b>" . __FILE__ . "</b> on line <b>" . __LINE__ . "</b><br />"
                    );
            }
        }
        return $encodedData;
    }
    
    /**
     * Indents text for better reading
     */
    public function xmlFormatData(string $data) : ?string
    {
        // regular expression for tags
        $formatedXml = preg_replace_callback(
            "|<[^>]*>[^<]*|",
            [$this, "xmlFormatElement"],
            $data
        );
        
        return $formatedXml;
    }
    
    /**
     * Callback function for xmlFormatData; do not invoke directly
     */
    private function xmlFormatElement(array $array) : string
    {
        $found = trim($array[0]);
        
        static $indent;
        
        // linebreak (default)
        $nl = "\n";
        
        $tab = str_repeat(" ", $indent * 2);
        
        // closing tag
        if (substr($found, 0, 2) == "</") {
            if ($indent) {
                $indent--;
            }
            $tab = str_repeat(" ", $indent * 2);
        } elseif (substr(
            $found,
            -2,
            1
        ) == "/" or // opening and closing, comment, ...
            strpos($found, "/>") or
            substr($found, 0, 2) == "<!") {
            // do not change indent
        } elseif (substr($found, 0, 2) == "<?") {
            // do not change indent
            // no linebreak
            $nl = "";
        } else { // opening tag
            $indent++;
        }
        
        // content
        if (substr($found, -1) != ">") {
            $found = str_replace(
                ">",
                ">\n" . str_repeat(" ", ($indent + 0) * 2),
                $found
            );
        }
        
        return $nl . $tab . $found;
    }
    
    /**
     * Writes xml header
     */
    public function xmlHeader() : void
    {
        // version and encoding
        $this->xmlStr .= "<?xml version=\"" . $this->version . "\" encoding=\"" . $this->outEnc . "\"?>";
        
        // dtd definition
        if ($this->dtdDef <> "") {
            $this->xmlStr .= $this->dtdDef;
        }
        
        // stSheet
        if ($this->stSheet <> "") {
            $this->xmlStr .= $this->stSheet;
        }
        
        // generated comment
        if ($this->genCmt <> "") {
            $this->xmlComment($this->genCmt);
        }
    }
    
    /**
     * Writes a starttag
     */
    public function xmlStartTag(
        string $tag,
        ?array $attrs = null,
        bool $empty = false,
        bool $encode = true,
        bool $escape = true
    ) : void {
        // write first part of the starttag
        $this->xmlStr .= "<" . $tag;
        
        // check for existing attributes
        if (is_array($attrs)) {
            // write attributes
            foreach ($attrs as $name => $value) {
                // encode
                if ($encode) {
                    $value = $this->xmlEncodeData($value);
                }
                
                // escape
                if ($escape) {
                    $value = $this->xmlEscapeData($value);
                }
                
                $this->xmlStr .= " " . $name . "=\"" . $value . "\"";
            }
        }
        
        // write last part of the starttag
        if ($empty) {
            $this->xmlStr .= "/>";
        } else {
            $this->xmlStr .= ">";
        }
    }
    
    /**
     * Writes an endtag
     */
    public function xmlEndTag(string $tag) : void
    {
        $this->xmlStr .= "</" . $tag . ">";
    }
    
    /**
     * Writes a comment
     */
    private function xmlComment(string $comment) : void
    {
        $this->xmlStr .= "<!--" . $comment . "-->";
    }
    
    /**
     * Writes data
     */
    public function xmlData(
        string $data,
        bool $encode = true,
        bool $escape = true
    ) : void {
        // encode
        if ($encode) {
            $data = $this->xmlEncodeData($data);
        }
        
        // escape
        if ($escape) {
            $data = $this->xmlEscapeData($data);
        }
        
        $this->xmlStr .= $data;
    }
    
    /**
     * Writes a basic element (no children, just textual content)
     */
    public function xmlElement(
        string $tag,
        $attrs = null,
        $data = null,
        $encode = true,
        $escape = true
    ) : void {
        // check for existing data (element's content)
        if (is_string($data) or
            is_integer($data) or
            is_float($data)) {
            // write starttag
            $this->xmlStartTag($tag, $attrs, false, $encode, $escape);
            
            // write text
            $this->xmlData($data, $encode, $escape);
            
            // write endtag
            $this->xmlEndTag($tag);
        } else { // no data
            // write starttag (= empty tag)
            $this->xmlStartTag($tag, $attrs, true, $encode, $escape);
        }
    }
    
    /**
     * Dumps xml document from memory into a file
     */
    public function xmlDumpFile(string $file, bool $format = true) : void
    {
        // open file
        if (!($fp = fopen($file, "w+"))) {
            throw new RuntimeException(
                "<b>Error</b>: Could not open \"" . $file . "\" for writing" .
                " in <b>" . __FILE__ . "</b> on line <b>" . __LINE__ . "</b><br />"
            );
        }
        
        // set file permissions
        chmod($file, 0770);
        
        // format xml data
        if ($format) {
            $xmlStr = $this->xmlFormatData($this->xmlStr);
        } else {
            $xmlStr = $this->xmlStr;
        }
        
        // write xml data into the file
        fwrite($fp, $xmlStr);
        
        // close file
        fclose($fp);
    }
    
    /**
     * Returns xml document from memory
     */
    public function xmlDumpMem(bool $format = true) : string
    {
        // format xml data
        if ($format) {
            $xmlStr = $this->xmlFormatData($this->xmlStr);
        } else {
            $xmlStr = $this->xmlStr;
        }
        
        return $xmlStr;
    }
    
    /**
     * append xml string to document
     */
    public function appendXML(string $a_str) : void
    {
        $this->xmlStr .= $a_str;
    }
    
    /**
     * clears xmlStr
     */
    public function xmlClear() : void
    {
        // reset xml string
        $this->xmlStr = "";
    }
}
