<?php
/**
 * Vice monitor(Vice monitor) reader
 * https://vice-emu.sourceforge.io/vice_12.html
 * @version 1.0.0
 * @author jambonbill
 */

namespace PSP;

use Exception;
use SplFixedArray;
//use PSP\Charset;

class ViceMon
{

    private $isDebug=false;
    private $ip='127.0.0.1';//file content
    private $port=6510;//file content
    //private $RAM=null;//RAM Section as binary string

    public function __construct()
    {

    }

    public function debug(bool $debug)
    {
        $this->isDebug=$debug;
    }

    /**
     * Set vice monitor IP
     * @param  string $ip [description]
     * @return [type]     [description]
     */
    public function ip(string $ip)
    {
        $this->ip=trim($ip);
    }


    /**
     * Set VICE monitor port
     * @param  int    $port [description]
     * @return [type]       [description]
     */
    public function port(int $port)
    {
        $this->port=$port;
    }

    public function nc(string $command)
    {
        //this is unsafe
        $cmd='echo '.$command.' | nc '.$this->ip.' '.$this->port;

        if($this->isDebug){
            echo "$cmd\n";
        }

        $str=shell_exec($cmd);

        if($this->isDebug){
            echo "$str\n";
        }

        return $str;
    }


    /**
     * Return Memory range
     * @param  [type] $addr0 [description]
     * @param  [type] $addr1 [description]
     * @return [type]        [description]
     */
    public function m(string $addr0, string $addr1)
    {
        $len=hexdec($addr1)-hexdec($addr0)+40;

        if ($len<1) {
            throw new Exception("Error Processing Request", 1);
        }

        //exit("len=$len");
        $command="m $addr0 $addr1";
        //$command='sc';
        echo "$command\n";

        $cmd='echo '.$command.' | nc '.$this->ip.' '.$this->port;
        $str=shell_exec($cmd);
        //echo $str;

        $rows=explode("\n", $str);
        //print_r($rows);//return $str;

        $dat=new SplFixedArray($len);

        $i=0;

        foreach($rows as $row){

            $row=trim($row);
            if(!$row)continue;

            //>C:0400  20 20 20 20  20 20 20 20  20 20 20 20  20 20 20 20

            preg_match("/C:[0-9a-f]{4}  ([0-9a-f ]+)/", $row, $o);
            if(!$o)continue;

            //echo "$row\n";
            //print_r($o);
            $nibs=explode(" ", $o[1]);
            $nibs=array_filter($nibs);

            //echo count($nibs);
            //print_r($x);
            foreach($nibs as $str)
            {

                if ($str) {
                    //echo "$str ".hexdec($str);
                    //echo "\n";
                    if($i>$len){
                        echo "$i > len\n";
                        continue;
                    }
                    echo "$i\n";
                    $dat[$i]=hexdec($str);
                    $i++;
                }
            }
        }
        return $dat;
    }

    /**
     * Return 1000 bytes of screen text
     * from (0x400 to 0x7e8) but it's easier to fetch multiples of 16 bytes
     * @return [type] [description]
     */
    public function screenMem()
    {
        $cmd='echo m 400 7ef | nc '.$this->ip.' '.$this->port;
        $str=shell_exec($cmd);
        //echo $str;


        $dat=new SplFixedArray(1000);//load
        $p=0;
        $rows=explode("\n", $str);

        foreach($rows as $l=>$row){
            $row=trim($row);
            //>C:0400  20 20 20 20  20 20 20 20  20 20 20 20  20 20 20 20
            preg_match("/>C:[0-9a-f]{4}  ([0-9a-f ]{50}+)/", $row, $o);//each row hold 16 bytes
            if(!$o)continue;
            if ($o[1]) {
                $nibs=array_filter(explode(" ", $o[1]));
                //$nibs=$nibs);
                //echo "[$l]\t".$o[1]."\n";
                foreach($nibs as $nib){
                    if($p<1000){
                        $dat[$p]=hexdec($nib);
                        $p++;
                    }
                }
            }
        }
        return $dat;
    }


    /**
     * Return Color Mem page (0xd800 to 0xdbf0)
     * @return [type] [description]
     */
    public function colorMem()
    {
        $cmd='echo m d800 dbf0 | nc '.$this->ip.' '.$this->port;
        $str=shell_exec($cmd);
        //echo $str;

        $dat=new SplFixedArray(1000);//load
        $p=0;
        $rows=explode("\n", $str);
        foreach($rows as $l=>$row){

            $row=trim($row);
            //>C:0400  20 20 20 20  20 20 20 20  20 20 20 20  20 20 20 20
            preg_match("/>C:[0-9a-f]{4}  ([0-9a-f ]{50}+)/", $row, $o);//each row hold 16 bytes
            if(!$o)continue;
            if ($o[1]) {
                $nibs=array_filter(explode(" ", $o[1]));
                //$nibs=$nibs);
                //echo "[$l]\t".$o[1]."\n";
                foreach($nibs as $nib){
                    if($p<1000){
                        $dat[$p]=hexdec($nib);
                        $p++;
                    }
                }
            }
        }
        return $dat;

    }
}