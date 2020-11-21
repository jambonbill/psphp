<?php
/**
 * PSP LZW
 * @version 1.0.0
 * @author jambonbill
 */

namespace PSP;

use Exception;

class LZW
{

    private $DEBUG=false;


    public function __construct()
    {
        //
    }

    /**
     * LZW Decoder
     * @param  string $s [description]
     * @return [type]    [description]
     */
    public function decode(string $s): string
    {

        mb_internal_encoding('UTF-8');

        $dict = array();
        $currChar = mb_substr($s, 0, 1);
        $oldPhrase = $currChar;
        $out = array($currChar);
        $code = 256;
        $phrase = '';

        for ($i=1; $i < mb_strlen($s); $i++) {
          $currCode = implode(unpack('N*', str_pad(iconv('UTF-8', 'UTF-16BE', mb_substr($s, $i, 1)), 4, "\x00", STR_PAD_LEFT)));
          if($currCode < 256) {
              $phrase = mb_substr($s, $i, 1);
          } else {
            //must fix//
            $phrase = @$dict[$currCode] ? $dict[$currCode] : ($oldPhrase.$currChar);

          }
          $out[] = $phrase;
          $currChar = mb_substr($phrase, 0, 1);
          $dict[$code] = $oldPhrase.$currChar;
          $code++;
          $oldPhrase = $phrase;
        }
        //var_dump($dict);
        return(implode($out));
    }

}