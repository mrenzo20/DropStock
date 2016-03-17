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

$dropstock_token = file_get_contents($site.'/token');
$dtoken = variable_get('dropstock_token', 'not-set');
$token_set = true;
if($dtoken == 'not-set'){
  $token_set = false;
}
if(!$token_set && $dropstock_token){
  variable_get('dropstock_token', $dropstock_token);
}

//comprovar el token
$provided_token = $_REQUEST['token'];
if(!isset($_REQUEST['token'])){
  die('No token provided');
}

//agafar el token de dropstock



if(!$dropstock_token){
  die("I can't connect to $site/token");
}

if($dropstock_token != $provided_token){
  die('tokens mismatch');
}
//els tokens son iguals, continuem.


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

variable_set('dropstock_token', $dropstock_token);
$cypher_token = variable_get('dropstock_cypher_token', 'not-set');
$cypher_token_set = true;
if($cypher_token == 'not-set'){
  $cypher_token = rand(0,9999);
  $cypher_token_set = false;

}

if(!$cypher_token_set){
  $setted = file_get_contents($site.'/encrypt-token?encrypt-token='.$cypher_token);
  if($setted){
    variable_set('dropstock_cypher_token', $cypher_token);
  }
}

$info = array(
  'name' => 'emfasi.com',
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
  $msg_decrypted = encrypt_decrypt('decrypt', $msg_encrypted, $cypher_token);

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
  $key = hash('sha256', $secret_key);

  // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
  $iv = substr(hash('sha256', $secret_iv), 0, 16);

  if( $action == 'encrypt' ) {
    $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
    $output = base64_encode($output);
  }
  else if( $action == 'decrypt' ){
    $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
  }

  return $output;
}