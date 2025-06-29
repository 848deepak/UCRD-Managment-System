<?php
/**
 * Simplified TCPDF class for UCRD Management System
 * 
 * This is a simplified version of TCPDF library to handle basic PDF generation
 * For a complete PDF solution, download the full TCPDF library from https://tcpdf.org/
 */

// Define constants
define('PDF_PAGE_ORIENTATION', 'P');
define('PDF_UNIT', 'mm');
define('PDF_PAGE_FORMAT', 'A4');

class TCPDF {
    // Properly declare all properties to avoid dynamic property warnings
    public $html = '';  // Added to fix dynamic property warning
    public $title = '';
    public $subject = '';
    public $creator = '';
    public $author = '';
    public $headerTitle = '';
    public $headerDescription = '';
    protected $orientation;
    protected $unit;
    protected $format;
    protected $unicode;
    protected $encoding;
    protected $diskcache;
    protected $headerFont;
    protected $footerFont;
    protected $defaultMonospacedFont;
    protected $margins = [];
    protected $headerMargin;
    protected $footerMargin;
    protected $autoPageBreak;
    protected $autoPageBreakMargin;
    protected $fontFamily;
    protected $fontStyle;
    protected $fontSize;
    
    /**
     * Constructor
     */
    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8', $diskcache = false) {
        $this->orientation = $orientation;
        $this->unit = $unit;
        $this->format = $format;
        $this->unicode = $unicode;
        $this->encoding = $encoding;
        $this->diskcache = $diskcache;
    }
    
    /**
     * Set document information
     */
    public function SetCreator($creator) {
        $this->creator = $creator;
    }
    
    public function SetAuthor($author) {
        $this->author = $author;
    }
    
    public function SetTitle($title) {
        $this->title = $title;
    }
    
    public function SetSubject($subject) {
        $this->subject = $subject;
    }
    
    /**
     * Set header data
     */
    public function SetHeaderData($logo = '', $logoWidth = 0, $title = '', $description = '') {
        $this->headerTitle = $title;
        $this->headerDescription = $description;
    }
    
    /**
     * Set header font
     */
    public function setHeaderFont($font) {
        $this->headerFont = $font;
    }
    
    /**
     * Set footer font
     */
    public function setFooterFont($font) {
        $this->footerFont = $font;
    }
    
    /**
     * Set default monospaced font
     */
    public function SetDefaultMonospacedFont($font) {
        $this->defaultMonospacedFont = $font;
    }
    
    /**
     * Set margins
     */
    public function SetMargins($left, $top, $right = -1) {
        $this->margins = [
            'left' => $left,
            'top' => $top,
            'right' => ($right == -1) ? $left : $right
        ];
    }
    
    /**
     * Set header margin
     */
    public function SetHeaderMargin($margin) {
        $this->headerMargin = $margin;
    }
    
    /**
     * Set footer margin
     */
    public function SetFooterMargin($margin) {
        $this->footerMargin = $margin;
    }
    
    /**
     * Set auto page break
     */
    public function SetAutoPageBreak($auto, $margin = 0) {
        $this->autoPageBreak = $auto;
        $this->autoPageBreakMargin = $margin;
    }
    
    /**
     * Add a page
     */
    public function AddPage($orientation = '', $format = '') {
        // In a simplified version, this doesn't do anything
    }
    
    /**
     * Set font
     */
    public function SetFont($family, $style = '', $size = 0) {
        $this->fontFamily = $family;
        $this->fontStyle = $style;
        $this->fontSize = $size;
    }
    
    /**
     * Write HTML
     */
    public function writeHTML($html, $ln = true, $fill = false, $reseth = false, $cell = false, $align = '') {
        // For our simplified version, we'll just store the HTML
        $this->html = $html;
    }
    
    /**
     * Output PDF
     */
    public function Output($name = 'doc.pdf', $dest = 'I') {
        // Instead of generating a real PDF, we'll create a simple HTML output with styling to mimic a PDF
        // In a production environment, you should use the full TCPDF library
        
        // Clean output buffer and send headers before any output
        if (ob_get_length()) {
            ob_clean();
        }
        
        // Set headers for browser download
        // Use a condition to prevent "headers already sent" warnings
        if (!headers_sent()) {
            header('Content-Type: text/html; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $name . '"');
        }
        
        // Output HTML to mimic PDF
        echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>' . htmlspecialchars($this->title) . '</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 20px;
            background-color: #f8f8f8;
        }
        .pdf-container {
            background-color: white;
            margin: 0 auto;
            padding: 40px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            max-width: 800px;
        }
        .pdf-header {
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .pdf-title {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
        }
        .pdf-description {
            font-size: 14px;
            color: #666;
            margin: 5px 0 0 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            padding: 8px;
            text-align: left;
        }
        td {
            padding: 8px;
        }
        .pdf-footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="pdf-container">
        <div class="pdf-header">
            <h1 class="pdf-title">' . htmlspecialchars($this->headerTitle) . '</h1>
            <p class="pdf-description">' . htmlspecialchars($this->headerDescription) . '</p>
        </div>
        
        <div class="pdf-content">
            ' . $this->html . '
        </div>
        
        <div class="pdf-footer">
            <p>Generated by ' . htmlspecialchars($this->creator) . ' on ' . date('Y-m-d H:i:s') . '</p>
        </div>
    </div>
</body>
</html>';
        
        exit;
    }
}
?> 