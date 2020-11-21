<?php
/**
 * PSP Screen
 * Handle Screen memory page
 * @version 1.0.0
 * @author jambonbill
 */

namespace PSP;

use Exception;
use SplFixedArray;
use PSP\Charset;

class Screen
{

    private $isDebug=false;


    //list of frames
    private $_chars=null;//CHAR PAGE
    private $_colrs=null;//COLR PAGE

    private $_cols=0;//screen size
    private $_rows=0;//screen size

    private $_colors=["#000000","#ffffff","#ab3126","#66daff","#bb3fb8","#55ce58","#1d0e97","#eaf57c","#b97418","#785300","#dd9387","#5b5b5b","#8b8b8b","#b0f4ac","#aa9def","#b8b8b8"];//default 16 colors
    private $_charset=null;//pixel data?
    private $_bgColor=0;//black

    /**
     * Dependency injection
     *
     * @param Base $B [description]
     */
    public function __construct(int $width, int $height)
    {
        $this->resize($width,$height);

        $charset=new Charset();
        $this->_charset=$charset->pixels();
    }


    /**
     * [PEEK description]
     * @param int $addr [description]
     */
    public function PEEK(int $addr): array
    {
        return [$this->_chars[$addr],$this->_colrs[$addr]];
    }


    /**
     * [POKE description]
     * @param int  $addr  [description]
     * @param byte $value [description]
     */
    public function POKE(int $addr, int $char, int $color): bool
    {
        $this->_chars[$addr]=$char;
        $this->_colrs[$addr]=$color;
        return true;
    }


    /**
     * Resize screen
     * @param  byte   $cols [description]
     * @param  byte   $rows [description]
     * @return [type]       [description]
     */
    public function resize(int $cols, int $rows)
    {
        if($cols<0||$cols>255)throw new Exception("Screen resize Error 1", 1);
        if($rows<0||$rows>255)throw new Exception("Screen resize Error 2", 1);

        $this->_cols=$cols;
        $this->_rows=$rows;
        $this->_chars=new SplFixedArray($cols*$rows);
        $this->_colrs=new SplFixedArray($cols*$rows);
        for($i=0;$i<$cols*$rows;$i++){
            $this->_chars[$i]=0;//`@`
            $this->_colrs[$i]=1;//black
        }
        return true;
    }


    /**
     * helper method
     * @return [type] [description]
     */
    public function charNumber(): int
    {
        return $this->_cols*$this->_rows;
    }



    /**
     * [bgColor description]
     * @param  integer $colorIndex [description]
     * @return [type]              [description]
     */

    public function bgColor(int $colorIndex)
    {
        $this->_bgColor=$colorIndex%16;
        return $this->_bgColor;
    }


   public function fromDb(array $r)
   {
        $jso=json_decode($r['json']);
        $colors=$jso->colors;
        //print_r($colors);exit;
        foreach($colors as $k=>$color){
            $this->_colors[$k]=$color;
        }
        $this->resize($r['cols'], $r['rows']);
        $this->bgColor($jso->bgColor);
        for($i=0;$i<$this->charNumber();$i++){
            $char=ord($r['bin'][$i*2]);
            $colr=ord($r['bin'][$i*2+1]);
            $this->poke($i,$char,$colr);
        }
   }

    /**
     * Render screen to a PNG file and save at path
     * @param  string $path [description]
     * @return [type]       [description]
     */
    public function toPng(string $path)
    {

        $W=$this->_cols*8;
        $H=$this->_rows*8;
        $im = imagecreatetruecolor($W, $H);//truecolor

        // build palette //

        $palette=[];
        for($i=0;$i<16;$i++){
            if(!isset($this->_colors[$i])){
                //warning this should/must not happen
                continue;
            }
            $r=substr($this->_colors[$i],1,2);
            $g=substr($this->_colors[$i],3,2);
            $b=substr($this->_colors[$i],5,2);
            $palette[$i]=imagecolorallocate($im,hexdec($r),hexdec($g),hexdec($b));
        }

        //unnecessary
        imagefilledrectangle($im, 0, 0, $W, $H, $palette[$this->_bgColor]);//solid bg

        for ($i=0;$i<($this->charNumber());$i++) {// Draw chars //

            $col=($i%$this->_cols);
            $row=floor($i/$this->_cols);

            $char=$this->_chars[$i];
            $colr=$this->_colrs[$i]%16;
            $bgColor=floor($this->_colrs[$i]/16);//CharBgColor

            $PX=$this->_charset[$char%256];

            if (!$PX) {
                throw new Exception("pixels not found. char=$char", 1);
            }

            // Draw BGColor Rect (colorextended)
            if ($bgColor>0) {
                imagefilledrectangle($im, $col*8, $row*8, $col*8+8, $row*8+8, $palette[$bgColor]);
            }

            foreach($PX as $p=>$pixel){
                $x=$p%8;
                $y=floor($p/8);
                if ($pixel) {
                    $color=$palette[$colr%16];
                    imagesetpixel($im,($col*8)+$x,($row*8)+$y,$color);
                }
            }
        }

        imagepng($im, $path);
    }

}