<?php
/**
 * PSPg
 * PSP `video` file format
 * @version 1.0.0
 * @author jambonbill
 */

namespace PSP;

use Exception;
use SplFixedArray;
use PSP\LZW;

//use SplFixedArray;
//use PSP\Charset;

class Pspg
{

    private $isDebug=false;
    private $_zoom=1;//Pixel size

    public function __construct()
    {
        $charset=new Charset();
        //$this->_charset=$charset->pixels();
    }

    public function load(string $filename)
    {
        echo "load($filename)\n";
        
        $rows=file($filename);
        $header=array_shift($rows);
        $palette=array_shift($rows);

        echo "HEADER:$header";
        echo "PAL:$palette";

        echo count($rows)." frames\n";
        foreach($rows as $lzwdata){
            //echo "$lzwdata";
            //$this->decodePspg($lzwdata);
            //exit;
        }
    }

    public function toFrames()
    {
        //$screen=new PSP\Screen(16,16);
        /*
        for($i=0;$i<$screen->charNumber();$i++){
            $screen->poke($i,1,$i);
        }

        echo $screen->toPng("/tmp/screen.png");
        */
    }

    /**
     * (from petscii.php) Decode a pspg frame string (B64+LZW)
     * @param  string $str [description]
     * @return [type]      [description]
     */

    public function decodePspg(string $pstr='')
    {
        $b64str=LZW::decode(trim($pstr));
        
        $bstr=base64_decode($b64str);

        $cols=ord($bstr[0]);
        $rows=ord($bstr[1]);

        $dat=[];
        $dat['charset']='uppercase';
        $dat['cols']=$cols;
        $dat['rows']=$rows;

        $chars=new SplFixedArray($cols*$rows);
        $colrs=new SplFixedArray($cols*$rows);
        for($i=0;$i<$cols*$rows;$i++){
            $chars[$i]=ord($bstr[2+($i*2)]);
            $colrs[$i]=ord($bstr[2+($i*2)+1]);
        }
        $dat['charData'][0]=$chars;
        $dat['colrData'][0]=$colrs;
        //print_r($dat);exit;
        return $this->decode($dat);
    }
    


    /**
     * Render frames to PNG at path
     * @param  string $path [description]
     * @return [type]       [description]
     */
    public function render(string $filename, string $path)
    {
        //
    }

}