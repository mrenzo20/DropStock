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
      var_dump($values);
      
      var_dump($values);
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
          $url = $site->getUrl();
          try{
            $contents = file_get_contents($url);
            if($contents){
              $checked = true;
            }
          }
          catch(Exception $e){
            $checked = false;
          }
          $site->json = $contents;
          $site->json_decoded = json_decode($site->json);
          // print '<pre>'.print_r($site->json_decoded,true).'</pre>';
          $site->dump = '';

          
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
          $modules = $site->getModules();
          
          $m_array = preg_grep('/.*'.$partialname.'.*/', $modules);
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
}
