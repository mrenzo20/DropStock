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
      $site = new Site();
      $site->setDefault();
      $values = $request->query->all();
      foreach($values as $key => $val){
        $site->set($key,$val);
      }

        
      $em = $this->getDoctrine()->getManager();
      $em->persist($site);
      $em->flush();

      return $this->redirectToRoute('site_show', array('id' => $site->getId()));
        
      // return new Response('hola',200);//'hola';
      $values = $request->request->all();
      // var_dump($values);
      
      // var_dump($values);
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
        //obtindre el json
        if($site->getUrl()){
          //http://dev.www.emfasi.com/dropstock.php?dropsite=http://127.0.0.1:8000/site/1&token=mytoken
          $url = $site->getUrl();
          
          $params =  'dropsite='.urlencode('http://'.$_SERVER['HTTP_HOST'].'/site/'.$site->getId().'');
          $params .= '&token='.urlencode($site->getToken());
          try{
            $full_url = $url.'?'.$params;
            // var_dump($full_url);die();
           
            $contents = file_get_contents($full_url);
            // var_dump($contents);die();
            if($contents){
              $checked = true;
            }
            // var_dump($contents);
            // $decrypted = $site->encrypt_decrypt('decrypt',$contents, $site->getCrypt());
            $site->json = $decrypted;
            $site->json_decoded = json_decode($site->json);
            // print '<pre>'.print_r($site->json_decoded,true).'</pre>';
            $site->dump = '';
          }
          catch(Exception $e){
            $checked = false;
            die($e->getMessage());
          }
        }


        //guardar valors del json
        if($checked){
          $now = \date('Y-m-d H:i:s');
          $site->setPlatform($site->json_decoded->software);
          $site->setStatus('checked '.$now);
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
        // var_dump($sites);
        $contains = array();
        foreach($sites as $site){
          $modules = $site->getModules();;
          // var_dump($partialname); 
          $partial = str_replace("_","_",trim($partialname));
          $pattern = "/$partial/";
          // var_dump($pattern);
          $m_array = preg_grep($pattern, $modules);
          // print '<pre>'.print_r($m_array,true).'</pre>';
          if(count($m_array)>1){
            $site->modules_match = $m_array;
            // var_dump($modules);
            $contains[] = $site;
            // print '<pre>'.print_r($modules,true).'</pre>';
          }
          // var_dump($modules);
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
      // var_dump($request);
      $values = $request->query->all();
      if(isset($values['encrypt-token'])){
        $em = $this->getDoctrine()->getManager();
        $site->setCrypt($values['encrypt-token']);
        $em->persist($site);
        $em->flush();
      }
      return new Response($site->getCrypt().'',200);
    }


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
  public function encrypt_decrypt($action, $string, $secret) {
    $output = false;

    $encrypt_method = "AES-256-CBC";
    $secret_key = $secret;
    $secret_iv = ''; //salt

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
  
}
