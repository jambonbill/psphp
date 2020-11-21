<?php
/**
 * PSPg
 * PSP `video` file format
 * @version 1.0.0
 * @author jambonbill
 */

namespace PSP;

use Exception;
//use SplFixedArray;
//use PSP\Charset;

class Pspg
{

    private $isDebug=false;


    public function __construct()
    {
        $charset=new Charset();
        //$this->_charset=$charset->pixels();
    }


    /**
     * Render frames to PNG at path
     * @param  string $path [description]
     * @return [type]       [description]
     */
    public function render(string $path)
    {
        //
    }

}