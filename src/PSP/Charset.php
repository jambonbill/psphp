<?php
/**
 * PSP Charset
 * @version 1.0.0
 * @author jambonbill
 */

namespace PSP;

use Exception;
use SplFixedArray;

class Charset
{

    private $DEBUG=false;

    /**
     * The binary charset data in a lookup table
     * PIXELS[n]=Array(64);//64 bits
     * @var array
     */
    //private $_PIXELS=[];//crap


    /**
     * data[n]=[0x00,0x00,0x00,0x00,0x00,0x00,0x00,0x00];
     * @var array
     */
    private $data=null;

    public function __construct()
    {
        $this->flush();
        $this->charsetImg = imagecreatefromstring ( base64_decode($this->UPPER) );


        $this->data=[];
        // We're looking at a 16x16Chr picture (128x128px)
        for($col=0;$col<16;$col++){
            for($row=0;$row<16;$row++){
                $charId=$row*16+$col;
                $char=[];
                //scan pixels
                for($y=0;$y<8;$y++){
                    $char[$y]=0;
                    for($x=0;$x<8;$x++){
                        $rgb=ImageColorAt($this->charsetImg, $col*8+$x, $row*8+$y);
                        $r = ($rgb >> 16) & 0xFF;
                        $g = ($rgb >> 8) & 0xFF;
                        $b = $rgb & 0xFF;
                        $out=($r+$g+$b)/3;
                        if($out>64){
                            // 1
                            $char[$y]+=[1,2,4,8,16,32,64,128][$x];
                        } else {
                            // 0
                        }
                    }
                    //$char[$y]=
                }
                $this->data[$charId]=$char;
            }
        }
    }

    /**
     * Set debug mode
     * @param  bool   $b [description]
     * @return [type]    [description]
     */
    public function debug(bool $b): self
    {
        $this->DEBUG=$b;
        return $this;
    }


    //png charset image as B64
    private $UPPER='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';

    private $LOWER='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';


    /**
     * Init charset to zero
     * @return [type] [description]
     */
    public function flush()
    {

        for($i=0;$i<256;$i++){
            $this->data[$i]=new SplFixedArray(8);
        }
        return true;

    }


    /**
     * Load charset image
     * @param  string $path [description]
     * @return [type]       [description]
     */
    public function loadPng(string $path)
    {
        if(!is_file($filename)){
            throw new Exception("File not found", 1);
        }

        if(!is_readable($filename)){
            throw new Exception("File not readable", 1);
        }

        //TODO
    }


    /**
     * Load Raw Binary charset
     * check http://kofler.dot.at/c64/index.html
     * @param  string $path [description]
     * @return [type]       [description]
     */
    public function loadBinary(string $path)
    {
        //todo
        throw new Exception("not implemented", 1);
    }


    /**
     * Open a CTM file (CharPad file format)
     * https://csdb.dk/release/?id=176475
     * @return [type] [description]
     */
    public function loadCtm(string $filename)
    {
        if(!is_file($filename)){
            throw new Exception("File not found", 1);
        }

        if(!is_readable($filename)){
            throw new Exception("File not readable", 1);
        }

        //https://github.com/martinpiper/C64Public/blob/master/ExternalTools/CharPad/Docs/CharPad%20-%20CTM%20(V3)%20Format.txt
        /*
        File Header, 20 bytes for v3, 24 byte for v4

        ID          [00-02]    3 bytes  : ASCII ID string... "CTM"
        VERSION     [03]       1 byte   : version number, currently $03.
        COLOURS     [04-07]    4 bytes  : BGR, MC1, MC2, RAM.
        COLOUR_MODE [08]       1 byte   : 0 = Global, 1 = Per Tile, 2 = Per Tile Cell.
        VIC_RES     [09]       1 byte   : 0 = Hi Resolution, 1 = Multicolour.

        MAP_WID     [10,11]    2 bytes  : 16-bit Map width (low, high).
        MAP_HEI     [12,13]    2 bytes  : 16-bit Map height (low, high).

        NUM_CHARS   [14,15]    2 bytes  : 16-bits, Number of chars -1 (low, high).
        NUM_TILES   [16]       1 byte   : Number of tiles -1.
        TILE_SIZE   [17]       1 byte   : Tile Size (currently 1-5 square).

        EXPANDED    [18]       1 byte   : Boolean flag, 1 = CHAR_DATA is in "Expanded" form (CELL_DATA is unnecessary and absent).
        RESERVED    [19]       1 byte   : (total header size is 20)

        Then File Data...

        CHAR_DATA.      The character set. Size = NUM_CHARS * 8 bytes.
        */
        $f=fopen($filename, "r");

        $ID = fread($f, 3);//CTM
        $VERSION = fread($f, 1);//version number

        if(ord($VERSION)!=4){
            throw new Exception("CTM file version not implemented (TODO)", 1);

            //File version 3 has a shorter header (20 bytes, and num chars is at a different position)
        }

        $COLOURS = fread($f, 4);//BGR, MC1, MC2, RAM.
        $COLOUR_MODE = fread($f, 1);//0 = Global, 1 = Per Tile, 2 = Per Tile Cell.
        $VIC_RES = fread($f, 1);//0 = Hi Resolution, 1 = Multicolour.

        $NUM_CHAR0 = fread($f, 1);//16-bits, Number of chars -1 (low, high).
        $NUM_CHAR1 = fread($f, 1);//16-bits, Number of chars -1 (low, high).

        $NUM_CHARS = ord($NUM_CHAR0);//This is acceptable (since we limit to 256)

        $NUM_TILES = fread($f, 1);//Number of tiles -1

        $TIL_WID = fread($f, 1);//Tile size
        $TIL_HEI = fread($f, 1);//Tile size

        $MAP_WID = fread($f, 2);// 16-bit Map width (low, high).
        $MAP_HEI = fread($f, 2);// 16-bit Map height (low, high).

        $EXPANDED = fread($f, 1);
        $RESERVED = fread($f, 1);
        $RESERVED = fread($f, 1);
        $RESERVED = fread($f, 1);
        $RESERVED = fread($f, 1);

        if ($this->DEBUG) {
            echo "ID=$ID\n";
            echo "VERSION=".ord($VERSION)."\n";
            echo "COLOURS=$COLOURS\n";
            echo "COLOUR_MODE=".ord($COLOUR_MODE)."\n";
            echo "NUM_CHAR0=".ord($NUM_CHAR0)."\n";
            echo "NUM_CHAR1=".ord($NUM_CHAR1)."\n";//unused
            echo "NUM_CHARS=$NUM_CHARS\n";
        }

        $this->flush();

        function reverse($char){
            $binary = decbin(ord($char));
            $binary = str_pad($binary, 8, 0, STR_PAD_LEFT);
            $binary = strrev($binary);
            $reversednumber= bindec($binary);
            $reversed = pack("C", $reversednumber);
            return $reversed;
        }

        for($i=0;$i<$NUM_CHARS;$i++){
            //$this->data[$i]=array(8);
            $this->data[$i]=new SplFixedArray(8);
            for($y=0;$y<8;$y++){
                $s=reverse(fread($f, 1));
                $this->data[$i][$y]=ord($s);
            }
        }


    }


    /**
     * Return Raw Charset data
     * @return [type] [description]
     */
    public function data()
    {
        return $this->data;
    }


    /**
     * Return char data
     * @param  int    $n [description]
     * @return [type]    [description]
     */
    public function char(int $n)
    {
        return $this->data[$n];
    }



    /**
     * Render charset to png, for debug or something
     * @param  [type] $path [description]
     * @return [type]       [description]
     */
    public function toPng(string $path)
    {
        $im = imagecreatetruecolor(128, 128);//truecolor
        $white = imagecolorallocate($im, 255, 255, 255);
        $black = imagecolorallocate($im, 0, 0, 0);

        imagefilledrectangle($im, 0, 0, 128, 128, $black);//solid bg

        /*
        function extract_bit($value, $pos)
        {
            return ($value >> $pos) & 1;
        }
        */

        //write pixels
        for($row=0;$row<16;$row++){
            for($col=0;$col<16;$col++){
                $char=$this->data[$col+$row*16];
                //scan pixels
                for($y=0;$y<8;$y++){
                    for($x=0;$x<8;$x++){
                        if ($char[$y]>>$x&1) {
                           imagesetpixel($im, $col*8+$x, $row*8+$y, $white);
                        }
                    }
                }
            }
        }

        imagepng($im, $path);
    }


    /**
     * Convert charset image into a lookup table
     * @return array [pixels]
     */
    /*
    public function pixels($charsetCase='uppercase')
    {

        if ($this->DEBUG) {
            echo __FUNCTION__."($charsetCase)\n";
        }


        $this->charsetImg = imagecreatefromstring ( base64_decode($this->UPPER) );


        $PIXELS=[];
        // We're looking at a 16x16Chr picture (128x128px)
        for($col=0;$col<16;$col++){
            for($row=0;$row<16;$row++){
                $charId=$row*16+$col;
                //scan pixels
                for($y=0;$y<8;$y++){
                    for($x=0;$x<8;$x++){
                        $rgb=ImageColorAt($this->charsetImg, $col*8+$x, $row*8+$y);
                        $r = ($rgb >> 16) & 0xFF;
                        $g = ($rgb >> 8) & 0xFF;
                        $b = $rgb & 0xFF;
                        $out=($r+$g+$b)/3;
                        if($out>64)$PIXELS[$charId][]=true;
                        else $PIXELS[$charId][]=false;
                    }
                }
            }
        }

        $this->_PIXELS=$PIXELS;//cache pixels
        return $PIXELS;
    }
    */
}