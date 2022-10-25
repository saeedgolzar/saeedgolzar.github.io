<?php
/**
 * Copyright (C) 2020 PrivateDaddy.com
 *
 * PrivateDaddy is free software; see http://www.privatedaddy.com/ for details.
 */

namespace PrivateDaddy;

define("PRIVATEDADDY_VERSION", "2.3");

class PrivateDaddy
{
    function __construct()
    {
        $this->token = $this->generate_token();
    }

    function generate_token()
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        $string = '';

        for ($i = 0; $i < 4; $i++) {
            $pos = rand(0, strlen($chars) - 1);
            $string .= $chars[$pos];
        }

        return $string;
    }

    function get_base_for_javascript()
    {
        return $this->string_to_hex($this->base, '%');
    }

    function get_token_for_javascript()
    {
        return $this->string_to_hex($this->token, '%');
    }

    function xor_encrypt($input, $key)
    {
        $key_length = strlen($key);

        for ($i = 0; $i < strlen($input); $i++) {

            $char_pos = $i % $key_length;

            $encrypted_char = ord($input[$i]) ^ ord($key[$char_pos]);

            $input[$i] = chr($encrypted_char);
        }

        return $input;
    }

    function string_to_hex($input, $prefix = '')
    {
        $result = '';

        for ($i = 0; $i < strlen($input); $i++) {
            $char_hex = dechex(ord($input[$i]));

            $char_hex = str_pad($char_hex, 2, '0', STR_PAD_LEFT);

            $result = $result . $prefix . $char_hex;
        }

        return $result;
    }

    function encode($s)
    {
        $result = base64_encode($s);
        $result = urlencode($result);
        $result = str_replace('%', '-', $result);

        return $result;
    }

    function obfuscate($href, $return_url)
    {
        $token = $this->token;

        $encrypted = $href;
        $encrypted = $this->xor_encrypt($encrypted, $this->base);
        $encrypted = $this->xor_encrypt($encrypted, $this->token);

        $mash = $encrypted . $token;
        $mash = $this->encode($mash) . '_' . urlencode($this->site_id);

        return 'https://www.privatedaddy.com/?q=' . $mash . "/r=" . $this->encode($return_url) . "/v=" . $this->encode(PRIVATEDADDY_VERSION) . "/p=" . $this->encode(phpversion());
    }

    function obfuscate_hrefs($input)
    {
        $href_regex = '/\<a[^\/\>]*href=["|\']((mailto|feedback):[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4})["|\']/Ui';

        preg_match_all($href_regex, $input, $out, PREG_PATTERN_ORDER);

        $result = $input;

        $return_url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . (isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : $_SERVER["SERVER_NAME"]) . $_SERVER["REQUEST_URI"];

        for ($i = 0; $i < sizeof($out[1]); $i++) {
            $href = $out[1][$i];

            $obfuscated_href = $this->obfuscate($href, $return_url);

            $result = str_ireplace($href, $obfuscated_href, $result);
        }

        return $result;
    }

    function partially_hide_email_address($email_address)
    {
        if (strpos($email_address, "@") === FALSE) {
            return $email_address;
        }

        $email_address_parts = explode("@", $email_address);
        $length = strlen($email_address_parts[0]);
        $shown = floor($length / 2);
        $hidden = $length - $shown;
        $mask = str_repeat("*", $hidden);

        return substr_replace($email_address_parts[0], $mask, $shown, $hidden) . "@" . substr_replace($email_address_parts[1], "**", 0, 2);
    }

    function obfuscate_elements_content($input)
    {
        $elements_regex = '/\>([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4})\</Ui';

        preg_match_all($elements_regex, $input, $out, PREG_PATTERN_ORDER);

        $result = $input;

        for ($i = 0; $i < sizeof($out[1]); $i++) {
            $content = $out[1][$i];

            $obfuscated_content = $this->partially_hide_email_address($content);

            $result = str_ireplace('>' . $content . '<', '>' . $obfuscated_content . '<', $result);
        }

        return $result;
    }

    function ob_callback($input, $status)
    {
        $result = $input;

        $result = $this->obfuscate_hrefs($result);

        $result = $this->obfuscate_elements_content($result);

        return $result;
    }

    function start()
    {
        ob_start(array(&$this, 'ob_callback'));
    }

    var $token;

	//Do NOT change the lines below - GRACEFUL DEGRADATION CAPABILITIES WILL CEASE TO WORK
	var $base = 'EUFcTbbFJuw1XUItE9LWdCzj41kkygI20e5Jg6fPqCHemgyRg9DkB2tvTEGiKfaA';
	var $site_id = '1328';
	//Do NOT change the lines above - GRACEFUL DEGRADATION CAPABILITIES WILL CEASE TO WORK
}

$privatedaddyObj = new PrivateDaddy();
$privatedaddy = &$privatedaddyObj;
?><script>var PrivateDaddy=new Object();PrivateDaddy.chainFunctions=function(a,b){return function(){if(a){a()}if(b){b()}}};PrivateDaddy.xorDecrypt=function(a,b){var i=0;var d='';var e;for(i=0;i<a.length;i++){var c=a.charCodeAt(i);e=b.charCodeAt(i%b.length);d+=String.fromCharCode(c^e)}return d};PrivateDaddy.base64Decode=function(a){var b="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";var c="";var d,chr2,chr3;var e,enc2,enc3,enc4;var i=0;a=a.replace(/[^A-Za-z0-9\+\/\=]/g,"");while(i<a.length){e=b.indexOf(a.charAt(i++));enc2=b.indexOf(a.charAt(i++));enc3=b.indexOf(a.charAt(i++));enc4=b.indexOf(a.charAt(i++));d=(e<<2)|(enc2>>4);chr2=((enc2&15)<<4)|(enc3>>2);chr3=((enc3&3)<<6)|enc4;c=c+String.fromCharCode(d);if(enc3!==64){c=c+String.fromCharCode(chr2)}if(enc4!==64){c=c+String.fromCharCode(chr3)}}return c};PrivateDaddy.unobfuscate=function(a){var b=a.substring(a.lastIndexOf('?q=')+3);if(b.indexOf("/")>0){b=b.substring(0, b.indexOf("/"));}b=b.replace(/[-]/g,'%');b=decodeURIComponent(b);b=b.substring(0,b.lastIndexOf('_'));b=PrivateDaddy.base64Decode(b);b=b.substring(0,b.length-4);b=PrivateDaddy.xorDecrypt(b,PrivateDaddy.token);b=PrivateDaddy.xorDecrypt(b,PrivateDaddy.base);return b};PrivateDaddy.pageHandler=function(){var a=document.getElementsByTagName('*');for(var i=0;i<a.length;i++){if(a[i].innerHTML){var b=a[i].innerHTML;if(b.indexOf('http://www.privatedaddy.com')<=4&&b.indexOf('http://www.privatedaddy.com')!==-1&&b.indexOf('?q=')!==-1){a[i].innerHTML=PrivateDaddy.unobfuscate(b.substring(4,b.length-4))}if(b.indexOf('https://www.privatedaddy.com')<=4&&b.indexOf('https://www.privatedaddy.com')!==-1&&b.indexOf('?q=')!==-1){a[i].innerHTML=PrivateDaddy.unobfuscate(b.substring(5,b.length-5))}}}var c=document.getElementsByTagName('a');for(i=0;i<c.length;i++){var d=c[i].href;if((d.indexOf('http://www.privatedaddy.com')===0||d.indexOf('https://www.privatedaddy.com')===0)&&d.indexOf('?q=')!==-1){c[i].href=PrivateDaddy.unobfuscate(d);if(c[i].innerText.indexOf("*@**")>=0&&c[i].innerText.indexOf(" ")<0){c[i].innerText=PrivateDaddy.unobfuscate(d).replace(/^mailto:([^?]+).*/, '$1');}}}};PrivateDaddy.base=unescape('<?php echo $privatedaddy->get_base_for_javascript(); ?>');PrivateDaddy.token=unescape('<?php echo $privatedaddy->get_token_for_javascript(); ?>');PrivateDaddy.addOnloadHandler=function(a){if(window.addEventListener){window.addEventListener('load',a,false)}else if(window.attachEvent){window.attachEvent('onload',function(){return a.apply(window,new Array(window.event))})}else{window.onload=PrivateDaddy.chainFunctions(window.onload,a)}};if(typeof jQuery==="undefined"){PrivateDaddy.addOnloadHandler(PrivateDaddy.pageHandler);}else{jQuery(document).ready(PrivateDaddy.pageHandler);}</script><?php $privatedaddy->start(); ?>