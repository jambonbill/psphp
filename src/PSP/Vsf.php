<?php
/**
 * VSF (Vice Snapshot File, saved-session file) reader
 * http://www.unusedino.de/ec64/technical/formats/vice_frz.html
 * @version 1.0.0
 * @author jambonbill
 */

namespace PSP;

use Exception;
use SplFixedArray;
//use PSP\Charset;

class Vsf
{

    private $isDebug=false;
    private $content=null;//file content
    private $RAM=null;//RAM Section as binary string

    public function __construct($filename)
    {
        if($filename){
            $this->open($filename);
        }
    }


    /**
     * Open vsf file
     * @param  string $filename [description]
     * @return [type]           [description]
     */
    public function open(string $filename)
    {
        if(!is_file($filename)){
            throw new Exception("File not found", 1);
        }

        if(!is_readable($filename)){
            throw new Exception("File not readable", 1);
        }


        //Read file signature
        $f=fopen($filename,"r");
        $sign = fread($f, 0x12);//VICE signature
        $ver = fread($f, 2);//Snapshot version $00/$00 (major/minor)
        $machine = fread($f, 15);//Name of emulated machine in ASCII (padded with  $00)
        fclose($f);

        //Let's make sure we are reading a valid vsf
        if($sign!='VICE Snapshot File'){
            throw new Exception("Error reading VICE Snapshot File", 1);
        }

        /*
        echo "$sign\n";
        echo "$ver\n";

        */
        echo "machine=`$machine`\n";

        $this->content=file_get_contents($filename);//as a large binary string
        $this->readC64MEM();
    }


    /**
     * Locate vsf modules address
     * @param  string $filename [description]
     * @return [type]           [description]
     */
    public function findModules()
    {
        //C64 -
        $keys=['MAINCPU', 'C64MEM', 'C64ROM', 'VIC-II', 'CIA1', 'CIA2', 'SID', 'REU', 'ACIA1', 'TPI'];
        $addr=[];
        foreach($keys as $key){
            $addr[$key]=strpos($this->content, $key);
        }
        return $addr;
    }


    /**
     * Get RAM from file-content
     * @return [type] [description]
     */
    public function readC64MEM()
    {
        if(!$this->content){
            throw new Exception("File content not found", 1);
        }

        /*
        Bytes: $0000-000F - Module type ("C64MEM"), padded with $00
                0010-0011 - Module version $00/00 (major/minor)
                0012-0015 - Module size (lo/hi), including this header
                     0016 - CPU Port data byte (RAM location $01)
                     0017 - CPU Port direction byte (RAM location $00)
                     0018 - State of the EXROM cartridge line
                     0019 - State of the GAME cartridge line
               001A-10019 - 64K RAM dump
         */

        $addr=strpos($this->content, 'C64MEM');

        if(!$addr){
            throw new Exception("C64MEM not found", 1);
        }

        $mtype=substr($this->content,$addr,0x000F);//`C64MEM`
        //echo "Module type=$mtype\n";
        //$ramdump=substr($content,$addr+0x001A, 0x10019);
        //echo "ramdump len=".strlen($ramdump);//1024*64=65536
        $this->RAM=substr($this->content, $addr+0x001A, 0x10019);
        return $this->RAM;
    }



    public function readVIC()
    {
        if(!$this->content){
            throw new Exception("File content not found", 1);
        }

        $addr=strpos($this->$content, 'VIC-II');

        if(!$addr){
            throw new Exception("C64MEM not found", 1);
        }
    }


    /**
     * Return Screen memory page ($0400-$07FF)
     * https://www.c64-wiki.com/wiki/Memory_Map#RAM_Table
     * @return [type] [description]
     */
    public function screenMemory()
    {
        $dat=new SplFixedArray(1024);
        for($i=0;$i<1024;$i++){
            $dat[$i]=ord($this->RAM[$i+0x400]);
        }
        return $dat;
    }


    /**
     * $D800-$DBFF
     * @return [type] [description]
     */
    public function colorMemory()
    {
        $dat=new SplFixedArray(1024);
        for($i=0;$i<1024;$i++){
            $dat[$i]=ord($this->RAM[$i+0xd800]);
        }
        return $dat;
    }


    /**
     * the background color of characters and graphics (HiRes)
     * @return [type] [description]
     */
    public function bgColor()
    {
        //https://www.c64-wiki.com/wiki/53281
        return ord($this->RAM[53281]);
    }

    /**
     * RAM section as binary string
     */
    public function RAM()
    {
        return $this->RAM;
    }


}