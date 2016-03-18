<?php

/**
 * @file
 * The PHP page that serves all page requests on a Drupal installation.
 *
 * The routines here dispatch control to the appropriate handler, which then
 * prints the appropriate page.
 *
 * All Drupal code is released under the GNU General Public License.
 * See COPYRIGHT.txt and LICENSE.txt.
 */




/**
 * Root directory of Drupal installation.
 */

$status = 'ok';
try
{
  define('DRUPAL_ROOT', getcwd());

  require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
  drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
  // menu_execute_active_handler();
}
catch(Exception $e)
{
  $status = 'ko';
}



//identificar el origen de la crida
if(!isset($_REQUEST['dropsite'])){
  die('you need to povide a "dropsite"');
}

$site = $_REQUEST['dropsite'];
$exploded_site = explode('|',$site);
$site = $exploded_site[0];
if(!isset($_REQUEST['token'])){
  $_REQUEST['token'] = $exploded_site[1];
}

//agafar els tokens de dropstock
$dropstock_token = curl_get($site.'/token',array(),array());
if($dropstock_token){
  variable_set('dropstock_token', $dropstock_token);
}
$cypher_token = curl_get($site.'/encrypt-token',array(), array());
if($cypher_token){
  variable_set('dropstock_cypher_token', $cypher_token);
}


//comprovar el token
if(!isset($_REQUEST['token'])){
  die('No token provided');
}
$provided_token = $_REQUEST['token'];


if(!$dropstock_token){
  die("I can't connect to $site/token");
}

if($dropstock_token != $provided_token){
  die('tokens mismatch');
}
//els tokens son iguals, continuem.


$info = array(
  'name' => '',
  'software' => 'Drupal '.VERSION,
  'status' => $status,
  'modules' => module_list(),
  'languages' => language_list(),
  'modules_enabled' => system_list('module_enabled'),
);

$var = $info;


if (isset($var)) {

  $json = json_encode($var);
  $msg_encrypted = encrypt_decrypt('encrypt',$json, $cypher_token);
  header('Content-Type: text/plain; charset=utf-8');
  print $msg_encrypted;
}
print '';
die();

/* FUNCTIONS */
/**
 * simple method to encrypt or decrypt a plain text string
 * initialization vector(IV) has to be the same when encrypting and decrypting
 * PHP 5.4.9
 *
 * this is a beginners template for simple encryption decryption
 * before using this in production environments, please read about encryption
 *
 * @param string $action: can be 'encrypt' or 'decrypt'
 * @param string $string: string to encrypt or decrypt
 *
 * @return string
 */
function encrypt_decrypt($action, $string, $secret) {
  $output = false;

  $encrypt_method = "AES-256-CBC";
  $secret_key = $secret;
  $secret_iv = '1234'; //salt

  // hash
  $key = substr(hash('sha256', $secret_key), 0, 32);

  // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
  $iv = substr(hash('sha256', $secret_iv), 0, 16);

  if( $action == 'encrypt' ) {
    $output = encrypt($string, $key);
  }
  else if( $action == 'decrypt' ){
    $output = decrypt($string, $key);
  }

  return $output;
}

  function encrypt($value,$key){
    $text = $value;
    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
    $crypttext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $text, MCRYPT_MODE_ECB, $iv);
    return $crypttext;
  }

    function decrypt($value,$key){
      $crypttext = $value;
      $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
      $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
      $decrypttext = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $crypttext, MCRYPT_MODE_ECB, $iv);
      return trim($decrypttext);
    }


/**
 * Send a GET requst using cURL
 * @param string $url to request
 * @param array $get values to send
 * @param array $options for cURL
 * @return string
 */
function curl_get($url, array $get = NULL, array $options = array())
{
  if(!function_exists('curl_init')){
    $result =file_get_contents($url. (strpos($url, '?') === FALSE ? '?' : ''). http_build_query($get));
  }
  else{
    $defaults = array(
      CURLOPT_URL => $url. (strpos($url, '?') === FALSE ? '?' : ''). http_build_query($get),
      CURLOPT_HEADER => 0,
      CURLOPT_RETURNTRANSFER => TRUE,
      CURLOPT_TIMEOUT => 4
    );
   
    $ch = curl_init();
    curl_setopt_array($ch, ($options + $defaults));
    $result = curl_exec($ch) ;
    if( !$result )
    {
      trigger_error(curl_error($ch));
    }
    curl_close($ch);
  }
  return $result;
} 
