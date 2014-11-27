<?php

namespace Bcarmard\TestIAdvizeBundle\Controller;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View,
FOS\RestBundle\View\ViewHandler,
FOS\RestBundle\View\RouteRedirectView;
use JMS\Serializer\SerializationContext;

/**
 * Description of RestController
 *
 * @author Duah
 */
class RestController extends FOSRestController
{
    
    public function getPostsAction()
    {
             
        $em = $this->getDoctrine()
                ->getManager();
        $repo = $em->getRepository('BcarmardTestIAdvizeBundle:ApiVdm');
        $results = $repo->findAll();
        
        $view = $this->view($results, 200)
                ->setTemplate("TestIAdvizeBundle:ApiRest:get.html.twig")
                ->setTemplateVar('posts');
        
        return $this->handleView($view);
    }
    

    public function getPostAction($id)
    {
        $em = $this->getDoctrine()
                ->getManager();
        $repo = $em->getRepository('BcarmardTestIAdvizeBundle:ApiVdm');
        $results = $repo->find($id);
        
        $view = $this->view($results, 200)
                ->setTemplate("TestIAdvizeBundle:ApiRest:get.html.twig")
                ->setTemplateVar('posts');
        
        return $this->handleView($view);
    }

}
