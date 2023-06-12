<?php
/**
 * PSP Screen
 * Handle a Character Screen page
 * @version 1.0.1
 * @author jambonbill
 */

namespace PSP;

use Exception;
use SplFixedArray;
use PSP\Charset;

class Screen
{

    private $isDebug=false;

    private $_chars=null;//CHAR PAGE
    private $_colrs=null;//COLR PAGE

    private $_cols=0;//screen size
    private $_rows=0;//screen size

    private $_colors=["#000000","#ffffff","#ab3126","#66daff","#bb3fb8","#55ce58","#1d0e97","#eaf57c","#b97418","#785300","#dd9387","#5b5b5b","#8b8b8b","#b0f4ac","#aa9def","#b8b8b8"];//default 16 colors

    //private $_charset=null;//raw charset data
    private $_bgColor=0;//black
    private $_borderColor=0;//not implemented


    public function __construct(int $width, int $height)
    {
        $this->resize($width,$height);
        $charset=new Charset();//Uppercase by default
        $this->_charset=$charset->data();
    }


    /**
     * [PEEK description]
     * @param int $addr [description]
     */
    public function PEEK(int $addr): array
    {
        return [$this->_chars[$addr]%256, $this->_colrs[$addr]%256];
    }


    /**
     * [POKE description]
     * @param int  $addr  [description]
     * @param byte $value [description]
     */
    public function POKE(int $addr, int $char, int $color): bool
    {
        $this->_chars[$addr]=$char%256;
        $this->_colrs[$addr]=$color%256;
        return true;
    }


    /**
     * Resize screen
     * @param  byte   $cols [description]
     * @param  byte   $rows [description]
     * @return [type]       [description]
     */
    public function resize(int $cols, int $rows): self
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
        return $this;
    }


    /**
     * helper method
     * @return [int] Total number of chars in Screen
     */
    public function charNumber(): int
    {
        return $this->_cols*$this->_rows;
    }


    /**
     * Get/Set color palette
     * @return [type] [description]
     */
    public function colorPalette()
    {
        return $this->_colors;
    }

    
    /**
     * Redefine color palette
     *
     * @param array|string $palette
     * @return void
     */
    public function setColorPalette(array|string $palette)
    {
        
        //$_colors=["#000000","#ffffff","#ab3126","#66daff","#bb3fb8","#55ce58","#1d0e97","#eaf57c","#b97418","#785300","#dd9387","#5b5b5b","#8b8b8b","#b0f4ac","#aa9def","#b8b8b8"];//default 16 colors
        
        if(is_array($palette)){
            $this->_colors=$palette;    
            return $this;
        }

        if (is_string($palette)) {//convert string into color palette array
            //TODO
            $palette=explode(",",$palette);
            $this->_colors=$palette;
            return $this;
        }
        
        
        
    }





    /**
     * GET/SET bgColor index
     * @param  integer $colorIndex [description]
     * @return [type]              [description]
     */

    public function bgColor(int $colorIndex)
    {
        $this->_bgColor=$colorIndex%16;
        return $this->_bgColor;
    }


    /**
     * Import screen data
     * @param  [type] $chars  [description]
     * @param  [type] $colors [description]
     * @return [type]         [description]
     */
    public function import(array $chars, array $colors): self
    {
        foreach($chars as $i=>$char)
        {
            $this->_chars[$i]=$char;
        }

        foreach($colors as $i=>$color)
        {
            $this->_colrs[$i]=$color%16;
        }

        return $this;
    }



    /**
     * PSP file method
     * @param  array  $r [description]
     * @return [type]    [description]
     */
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
     * Set frame data from given Base64 string
     *
     * @param string $b64str
     * @return void
     */
    public function fromB64(string $b64str):bool
    {
        $bstr=base64_decode($b64str);

        $cols=ord($bstr[0]);
        $rows=ord($bstr[1]);
        
        //$this->_cols=$cols;
        //$this->_rows=$rows;
        $this->resize($cols, $rows);

        //$dat=[];
        //$dat['charset']='uppercase';
        //$dat['cols']=$cols;
        //$dat['rows']=$rows;

        $chars=new SplFixedArray($cols*$rows);
        $colrs=new SplFixedArray($cols*$rows);
        for($i=0;$i<$cols*$rows;$i++){
            //$chars[$i]=ord($bstr[2+($i*2)]);
            //$colrs[$i]=ord($bstr[2+($i*2)+1]);
            $char=ord($bstr[2+($i*2)]);
            $colr=ord($bstr[2+($i*2)+1]);
            $this->poke($i,$char,$colr);
        }
        //$dat['charData'][0]=$chars;
        //$dat['colrData'][0]=$colrs;
        //return $dat;
        return true;
    }


    /**
     * Render screen to a PNG file
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
            // Draw BGColor Rect (colorextended)
            if ($bgColor>0) {
                imagefilledrectangle($im, $col*8, $row*8, $col*8+8, $row*8+8, $palette[$bgColor]);
            }

            $glyph=$this->_charset[$char%256];

            if (!$glyph) {
                throw new Exception("pixels not found. char=$char", 1);
            }

            foreach($glyph as $y=>$pixels){
               for($x=0;$x<8;$x++){
                    if ($pixels>>$x&1) {
                        $color=$palette[$colr%16];
                        imagesetpixel($im,($col*8)+$x,($row*8)+$y,$color);
                    }
               }
            }
        }
        imagepng($im, $path);
    }


    /**
     * Build a PSP Json file
     * @return [type] [description]
     */
    public function toJson(string $path): string
    {
        $arr=[];
        $arr['version']=10;//json format version
        $arr['cols']=$this->_cols;
        $arr['rows']=$this->_rows;
        $arr['bgColor']=$this->_bgColor;
        $arr['colors']=$this->_colors;//palette

        // this is ugly but i dont know how to do it right
        // Splfixed array is not 8bit, and do not stringify well
        $A='';
        $B='';
        for($i=0;$i<$this->charNumber();$i++){
            $A.=chr($this->_chars[$i]);
            $B.=chr($this->_colrs[$i]);
        }

        $arr['charData']=[];//Base64 encoded
        $arr['charData'][0]=base64_encode($A);

        $arr['colrData']=[];
        $arr['colrData'][0]=base64_encode($B);
        //var_dump($arr);exit;
        $json=json_encode($arr);

        if (($path)) {//save to file
            file_put_contents($path, $json);
        }
        return $json;
    }
}