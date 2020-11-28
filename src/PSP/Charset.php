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



    /**
     * Init charset to zero
     * @return [type] [description]
     */
    public function flush(): self
    {
        for($i=0;$i<256;$i++){
            $this->data[$i]=new SplFixedArray(8);
        }
        return $this;

    }


    /**
     * Load charset image
     * @param  string $path [description]
     * @return [type]       [description]
     */
    public function loadPng(string $path)
    {
        if(!is_file($path)){
            throw new Exception("File not found", 1);
        }

        if(!is_readable($path)){
            throw new Exception("File not readable", 1);
        }

        $size=getimagesize($path);
        $cols=floor($size[0]/8);
        $rows=floor($size[1]/8);

        if ($this->DEBUG) {
            echo "$cols x $rows chars\n";
        }

        $im=imagecreatefrompng($path);

        $this->flush();

        //read image
        for($col=0;$col<$cols;$col++){
            for($row=0;$row<$rows;$row++){
                //read pixels
                $charId=$row*$cols+$col;
                if ($charId>255) {
                    //8bit is max
                    continue;
                }
                $char=[];
                //scan pixels
                for($y=0;$y<8;$y++){
                    $char[$y]=0;
                    for($x=0;$x<8;$x++){
                        $rgb=ImageColorAt($im, $col*8+$x, $row*8+$y);
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
        return true;
    }



    /**
     * Reverse BIT order in given Byte (char)
     * @param  [type] $char [description]
     * @return [type]       [description]
     */
    private function reverse($char)
    {
        $binary = decbin(ord($char));
        $binary = str_pad($binary, 8, 0, STR_PAD_LEFT);
        $binary = strrev($binary);
        $reversednumber= bindec($binary);
        $reversed = pack("C", $reversednumber);
        return $reversed;
    }


    /**
     * Load Raw Binary charset (64c)
     * I think there is a way to dump custom charsets with VICE ?
     * check http://kofler.dot.at/c64/index.html for 64c files
     * @param  string $path [description]
     * @return [type]       [description]
     */
    public function loadBinary(string $path)
    {
        //todo
        if (!is_file($path)) {
            throw new Exception("not implemented", 1);
        }

        $this->flush();

        $binstring=file_get_contents($path);

        //we must skip the first two bytes

        //$b0=$binstring[0];
        //$b1=$binstring[1];
        /*
        echo ord($binstring[0]);
        echo " ";
        echo ord($binstring[1]);//i assume its the number of chars in the font
        echo "\n";
        */

        $len=strlen($binstring);
        $charnum=floor($len/8);
        for($i=0;$i<$charnum;$i++){
            $this->data[$i]=new SplFixedArray(8);
            for($y=0;$y<8;$y++){
                $addr=2+$i*8+$y;
                if(isset($binstring[$addr])){
                    $char=$binstring[$addr];
                }else{
                    $char=' ';
                }
                //TODO fix `Uninitialized string offset: 465`
                $this->data[$i][$y]=ord($this->reverse($char));
            }
        }
        return $charnum;
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
        /*
        function reverse($char){
            $binary = decbin(ord($char));
            $binary = str_pad($binary, 8, 0, STR_PAD_LEFT);
            $binary = strrev($binary);
            $reversednumber= bindec($binary);
            $reversed = pack("C", $reversednumber);
            return $reversed;
        }
        */

        for($i=0;$i<$NUM_CHARS;$i++){
            //$this->data[$i]=array(8);
            $this->data[$i]=new SplFixedArray(8);
            for($y=0;$y<8;$y++){
                $s=$this->reverse(fread($f, 1));
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


}