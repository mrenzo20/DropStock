<?php

namespace DropstockBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use DropstockBundle\Entity\Site;
use DropstockBundle\Form\SiteType;

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



function isJson($string) {
 json_decode($string);
 return (json_last_error() == JSON_ERROR_NONE);
}



function decrypt($value,$key){
  $crypttext = $value;
  $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
  $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
  $decrypttext = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $crypttext, MCRYPT_MODE_ECB, $iv);
  return trim($decrypttext);
}

/**
 * Send a POST requst using cURL
 * @param string $url to request
 * @param array $post values to send
 * @param array $options for cURL
 * @return string
 */
function curl_post($url, array $post = NULL, array $options = array())
{
  $defaults = array(
    CURLOPT_POST => 1,
    CURLOPT_HEADER => 0,
    CURLOPT_URL => $url,
    CURLOPT_FRESH_CONNECT => 1,
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_FORBID_REUSE => 1,
    CURLOPT_TIMEOUT => 4,
    CURLOPT_POSTFIELDS => http_build_query($post)
  );

  $ch = curl_init();
  curl_setopt_array($ch, ($options + $defaults));
  if( ! $result = curl_exec($ch))
  {
    trigger_error(curl_error($ch));
  }
  curl_close($ch);
  return $result;
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

  // $result =file_get_contents($url. (strpos($url, '?') === FALSE ? '?' : ''). http_build_query($get));
  $defaults = array(
    CURLOPT_URL => $url. (strpos($url, '?') === FALSE ? '?' : ''). http_build_query($get),
    CURLOPT_HEADER => 0,
    CURLOPT_RETURNTRANSFER => TRUE,
    CURLOPT_TIMEOUT => 4
  );
   
  $ch = curl_init();
  curl_setopt_array($ch, ($options + $defaults));
  $result = curl_exec($ch) ;
  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  var_dump('http code',$httpCode);
  if( !$result )
  {
    trigger_error(curl_error($ch));
  }
  curl_close($ch);
  return array($result,$httpCode);
} 

/**
 * Site controller.
 *
 * @Route("/site")
 */
class SiteController extends Controller
{
  /**
   * Lists all Site entities.
   *
   * @Route("/", name="site_index")
   * @Method("GET")
   */
  public function indexAction()
  {
    $em = $this->getDoctrine()->getManager();

    $sites = $em->getRepository('DropstockBundle:Site')->findAll();

    return $this->render('site/index.html.twig', array(
                           'sites' => $sites,
                         ));
  }

  /**
   * Lists all Site entities.
   *
   * @Route("/cron", name="site_cron")
   * @Method("GET")
   */
  public function cronAction()
  {
    $em = $this->getDoctrine()->getManager();

    $sites = $em->getRepository('DropstockBundle:Site')->findAll();
    foreach($sites as $site){
      $this->checkAction($site);
    }

    return $this->render('site/index.html.twig', array(
                           'sites' => $sites,
                         ));
  }

  /**
   * Creates a new Site entity.
   *
   * @Route("/new", name="site_new")
   * @Method({"GET", "POST"})
   */
  public function newAction(Request $request)
  {
    $site = new Site();
    $form = $this->createForm('DropstockBundle\Form\SiteType', $site);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $em->persist($site);
      $em->flush();

      return $this->redirectToRoute('site_show', array('id' => $site->getId()));
    }

    return $this->render('site/new.html.twig', array(
                           'site' => $site,
                           'form' => $form->createView(),
                         ));
  }
  /**
   * Registers a new Site entity.
   *
   * @Route("/register", name="site_register")
   * @Method({"GET", "POST"})
   */
  public function registerAction(Request $request)
  {
     $values = $request->query->all();
   
    $parsed_url = parse_url($values['url']);
    $em = $this->getDoctrine()->getManager();
    $site = $em->getRepository('DropstockBundle:Site')->findOneBy(array('url'=>'%'.$parsed_url['host'].'%'));
    if(!$site){
      $site = new Site();
      $site->setDefault();
    }
    foreach($values as $key => $val){
      $site->set($key,$val);
    }
        
    $em = $this->getDoctrine()->getManager();
    $em->persist($site);
    $em->flush();

    return $this->redirectToRoute('site_show', array('id' => $site->getId()));
        
    // return new Response('hola',200);//'hola';
    $values = $request->request->all();

    return new Response($request,200);
    return $this->render('base.html.twig', array(
                         ));
  }

  /**
   * Finds and displays a Site entity.
   *
   * @Route("/{id}", name="site_show")
   * @Method("GET")
   */
  public function showAction(Site $site)
  {
    $deleteForm = $this->createDeleteForm($site);

    return $this->render('site/show.html.twig', array(
                           'site' => $site,
                           'delete_form' => $deleteForm->createView(),
                         ));
  }

  /**
   * Displays a form to edit an existing Site entity.
   *
   * @Route("/{id}/edit", name="site_edit")
   * @Method({"GET", "POST"})
   */
  public function editAction(Request $request, Site $site)
  {
    $deleteForm = $this->createDeleteForm($site);
    $editForm = $this->createForm('DropstockBundle\Form\SiteType', $site);
    $editForm->handleRequest($request);

    if ($editForm->isSubmitted() && $editForm->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $em->persist($site);
      $em->flush();

      return $this->redirectToRoute('site_edit', array('id' => $site->getId()));
    }

    return $this->render('site/edit.html.twig', array(
                           'site' => $site,
                           'edit_form' => $editForm->createView(),
                           'delete_form' => $deleteForm->createView(),
                         ));
  }

  /**
   * Deletes a Site entity.
   *
   * @Route("/{id}", name="site_delete")
   * @Method("DELETE")
   */
  public function deleteAction(Request $request, Site $site)
  {
    $form = $this->createDeleteForm($site);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $em->remove($site);
      $em->flush();
    }

    return $this->redirectToRoute('site_index');
  }

  /**
   * Creates a form to delete a Site entity.
   *
   * @param Site $site The Site entity
   *
   * @return \Symfony\Component\Form\Form The form
   */
  private function createDeleteForm(Site $site)
  {
    return $this->createFormBuilder()
      ->setAction($this->generateUrl('site_delete', array('id' => $site->getId())))
      ->setMethod('DELETE')
      ->getForm()
      ;
  }


  /**
   * Finds and displays a Site entity.
   *
   * @Route("/{id}/check", name="site_check")
   * @Method("GET")
   */
  public function checkAction(Site $site)
  {
    $checked = false;
    $contents = '';

    //reset tokens
    $site->setToken(rand(0,9999));
    $site->setCrypt(rand(0,9999));
    $em = $this->getDoctrine()->getManager();
    $em->persist($site);
    $em->flush();


    //get json info
    $httpCode = 0;
    if($site->getUrl()){
      $url = $site->getUrl();

      $dev_environment = '';
      $pos = strpos($_SERVER['HTTP_HOST'], 'emfasi.local');
      if($pos !== false){
        // $dev_environment = '/app_dev.php';
      }
      $get = array(
        'dropsite' => 'http://'.$_SERVER['HTTP_HOST'].$dev_environment.'/site/'.$site->getId().''.'|'.($site->getToken()),
      );
      
      try{
        $options = array(
          
          CURLOPT_CONNECTTIMEOUT=> 2,
          CURLOPT_RETURNTRANSFER=> 1,
          CURLOPT_USERAGENT=> 'Dropstock',
        );

        list($contents,$httpCode) = curl_get($url,$get,$options) ;
        if($contents){
          $checked = true;
        }
      }
      catch(Exception $e){
        $checked = false;
        $site->setStatus('error '.$e->getMessage());
      }
    }


    //guardar valors del json
    $decrypted = 'not checked';
    $site->setStatus($httpCode.' code|contents:'.$contents.'|');
    if($checked){
      $now = \date('Y-m-d H:i:s');
      if(isJson($contents)){
        $site->json = $contents;
      }
      else{
        $decrypted = encrypt_decrypt('decrypt',$contents, $site->getCrypt());
        $site->json = $decrypted;
      }
      $site->json_decoded = json_decode($site->json);
      $site->setPlatform($site->json_decoded->software);
      if(!isset($site->json_decoded->status)){
        $site->json_decoded->status = $site->json;
      }
      $site->setStatus('checked '.$now. ' ' . $site->json_decoded->status);
      $site->dump = '';//print_r($site->json_decoded,'true');

          
      // info dels moduls
      $modules = array();
      foreach($site->json_decoded->modules_enabled as $module => $info){
        $modules[] = $module.' '.$info->info->version. ' '. $info->schema_version;
      }
      // print '<pre>'.print_r($modules,true).'</pre>';
      $site->setModules($modules);

          
      $em = $this->getDoctrine()->getManager();
      $em->persist($site);
      $em->flush();
    }

    //reset tokens
    $site->setToken(rand(0,9999));
    $site->setCrypt(rand(0,9999));
    $em = $this->getDoctrine()->getManager();
    $em->persist($site);
    $em->flush();
    
    return new Response($decrypted.$contents,200);
    return $this->render('site/check.html.twig', array(
                           'site' => $site,
                         ));
  }


  /**
   * Lists all Site entities.
   *
   * @Route("/contains/{partialname}", name="site_contains")
   * @Method("GET")
   */
  public function containsAction($partialname)
  {
    $em = $this->getDoctrine()->getManager();

    // print_r($partialname);
    $sites = $em->getRepository('DropstockBundle:Site')->findAll();
    $contains = array();
    foreach($sites as $site){
      $modules = $site->getModules();;
      $partial = str_replace("_","_",trim($partialname));
      $pattern = "/$partial/";
      $m_array = preg_grep($pattern, $modules);
      // print '<pre>'.print_r($m_array,true).'</pre>';
      if(count($m_array)>1){
        $site->modules_match = $m_array;
        $contains[] = $site;
        // print '<pre>'.print_r($modules,true).'</pre>';
      }
    }

    $sites = $contains;
    return $this->render('site/modules.html.twig', array(
                           'sites' => $contains,
                         ));
  }


  /**
   * Displays a form to edit an existing Site entity.
   *
   * @Route("/{id}/token", name="site_token")
   * @Method({"GET", "POST"})
   */
  public function tokenAction(Request $request, Site $site)
  {
    // return new Response('hola',200);//'hola';$site->getToken();
    return new Response($site->getToken(),200);//'hola';$site->getToken();
  }

  /**
   * Displays a form to edit an existing Site entity.
   *
   * @Route("/{id}/encrypt-token", name="site_encrypt-token")
   * @Method({"GET", "POST"})
   */
  public function encrypttokenAction(Request $request, Site $site)
  {
   $values = $request->query->all();
    if(isset($values['encrypt-token'])){
      $em = $this->getDoctrine()->getManager();
      $site->setCrypt($values['encrypt-token']);
      $em->persist($site);
      $em->flush();
    }
    return new Response($site->getCrypt().'',200);
  }


  
  
}
