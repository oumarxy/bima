<?php

# override the default TCPDF config file
/* if(!defined('K_TCPDF_EXTERNAL_CONFIG')) {	
  define('K_TCPDF_EXTERNAL_CONFIG', TRUE);
  }
 */
# include TCPDF
require(APPPATH . 'config/tcpdf' . EXT);
require_once($tcpdf['base_directory'] . '/tcpdf.php');




/* * **********************************************************
 * TCPDF - CodeIgniter Integration
 * Library file
 * ----------------------------------------------------------
 * @author Jonathon Hill http://jonathonhill.net
 * @version 1.0
 * @package tcpdf_ci
 * ********************************************************* */

class pdf extends TCPDF {

    /**
     * TCPDF system constants that map to settings in our config file
     *
     * @var array
     * @access private
     */
    private $cfg_constant_map = array(
        'K_PATH_MAIN' => 'base_directory',
        'K_PATH_URL' => 'base_url',
        'K_PATH_FONTS' => 'fonts_directory',
        'K_PATH_CACHE' => 'cache_directory',
        'K_PATH_IMAGES' => 'image_directory',
        'K_BLANK_IMAGE' => 'blank_image',
        'K_SMALL_RATIO' => 'small_font_ratio',
    );

    /**
     * Settings from our APPPATH/config/tcpdf.php file
     *
     * @var array
     * @access private
     */
    private $_config = array();

    /**
     * Initialize and configure TCPDF with the settings in our config file
     *
     */
    function __construct() {

        # load the config file
        require(APPPATH . 'config/tcpdf' . EXT);
        $this->_config = $tcpdf;
        unset($tcpdf);



        # set the TCPDF system constants
        foreach ($this->cfg_constant_map as $const => $cfgkey) {
            if (!defined($const)) {
                define($const, $this->_config[$cfgkey]);
                #echo sprintf("Defining: %s = %s\n<br />", $const, $this->_config[$cfgkey]);
            }
        }
        if (defined('SMARTWEB_PAGE')) {
            $this->_config['page_orientation'] = SMARTWEB_PAGE;
        }

        if (defined('DOMAIN_NAME')) {
            $this->_config['header_title'] = DOMAIN_NAME;
        }

        # initialize TCPDF		
        parent::__construct(
                $this->_config['page_orientation'], $this->_config['page_unit'], $this->_config['page_format'], $this->_config['unicode'], $this->_config['encoding'], $this->_config['enable_disk_cache']
        );


        # language settings
        if (is_file($this->_config['language_file'])) {
            include($this->_config['language_file']);
            $this->setLanguageArray($l);
            unset($l);
        }

        # margin settings
        $this->SetMargins($this->_config['margin_left'], $this->_config['margin_top'], $this->_config['margin_right']);

        # header settings
        $this->print_header = $this->_config['header_on'];
        #$this->print_header = FALSE; 
        $this->setHeaderFont(array($this->_config['header_font'], '', $this->_config['header_font_size']));
        $this->setHeaderMargin($this->_config['header_margin']);

        # footer settings
        $this->print_footer = $this->_config['footer_on'];
        $this->setFooterFont(array($this->_config['footer_font'], '', $this->_config['footer_font_size']));
        $this->setFooterMargin($this->_config['footer_margin']);

        # page break
        $this->SetAutoPageBreak($this->_config['page_break_auto'], $this->_config['footer_margin']);

        # cell settings
        $this->cMargin = $this->_config['cell_padding'];
        $this->setCellHeightRatio($this->_config['cell_height_ratio']);

        # document properties
        $this->author = $this->_config['author'];
        $this->creator = $this->_config['creator'];

        # font settings
        #$this->SetFont($this->_config['page_font'], '', $this->_config['page_font_size']);
        # image settings
        $this->imgscale = $this->_config['image_scale'];
    }

    //Page header
    public function Header() {
        // Set font
        $this->SetFont('helvetica', 'B', 12);
        // Logo
        $image_file = K_PATH_IMAGES . 'thorn_logo.jpg';
        $this->Image($image_file, 15, 10, '', '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // Title
        $this->Cell(80, 15, strtoupper($this->_config['header_title']), 0, 1, 'C', 0, '', 0, false, 'M', 'M');
        $this->Cell(120, 15, $this->_config['header_string'], 0, 1, 'C', 0, '', 0, false, 'M', 'M');
        $y = $this->GetY();
        $x = $this->GetX();
        $x2 = $this->getRemainingWidth() + 20;
        $this->Line($x, $y, $x2, $y);
    }

// Page footer
    public function Footer() {

        //$this->SetY(-15);
        $y = $this->GetY();
        $x = $this->GetX();
        $x2 = $this->getRemainingWidth() + 20;
        //Set font
        $this->SetFont('helvetica', 'I', 6);
        //Contact
        $this->Line($x, $y, $x2, $y);
        $this->MultiCell(100, 10, ucfirst(strtolower($this->_config['header_title'])) . "\n Printed on :" . date('d-m-Y , H:i:s') . "\n", 0, '');

        $this->MultiCell(100, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, 'R', 0, 1, 100, $y + 2, TRUE);
    }

}
