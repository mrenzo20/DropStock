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
try{

  define('DRUPAL_ROOT', getcwd());

  require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
  drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
  // menu_execute_active_handler();
 }
catch(Exception $e){
  $status = 'ko';
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
header('Content-Type: text/javascript; charset=utf-8');

if (isset($var)) {
  echo json_encode($var);
 }
