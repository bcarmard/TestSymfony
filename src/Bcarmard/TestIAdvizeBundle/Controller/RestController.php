<?php

namespace Bcarmard\TestIAdvizeBundle\Controller;

use DateTime;
use Doctrine\DBAL\Types\Type;
use FOS\RestBundle\Controller\Annotations\QueryParam;
//use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
/**
 * Description of RestController
 * Ceci est un controleur REST full, utulisant FosRestConroller
 *
 * @author Bcarmard
 * 
 */
class RestController extends FOSRestController
{
      /**
     * @QueryParam(name="from", requirements="^(19|20)\d\d[- /.](0[1-9]|1[012])[- /.](0[1-9]|[12][0-9]|3[01])$", default="1", description="Date de début.")
     * @QueryParam(name="to", requirements="^(19|20)\d\d[- /.](0[1-9]|1[012])[- /.](0[1-9]|[12][0-9]|3[01])$", default="1", description="Date de fin.")
     * @QueryParam(name="author", requirements="^[A-Za-z0-9 _]*[A-Za-z0-9][A-Za-z0-9 _]*$", default="1", description="Auteur")
     * @param ParamFetcher $paramFetcher
     */
    public function getPostsAction(ParamFetcher $paramFetcher)
    {   
        //récupération des paramètres passés en Get
        $from = $paramFetcher->get('from');
        $to = $paramFetcher->get('to');
        $author = $paramFetcher->get('author');
        
        //appel du query builder
        $em = $this->getDoctrine()
                ->getManager();
        $qb = $em->createQueryBuilder();
        
        //si From et To sont définis
        if($from != 1  && $to != 1 )
        {
            //passage des dates du format string au format DateTime
            $date_from = new DateTime($from);
            $date_to = new DateTime($to);
                        
            //génération de la requete
            $query = $qb->select('s')
                        ->from('Bcarmard\TestIAdvizeBundle\Entity\ApiVdm', 's')
                        ->andWhere($qb->expr()->between('s.date', ':date_from', ':date_to'))
                        ->setParameter('date_from', $date_from, \Doctrine\DBAL\Types\Type::DATETIME)
                        ->setParameter('date_to', $date_to, \Doctrine\DBAL\Types\Type::DATETIME)
                        ->getQuery();
           
            $posts = $query->getResult();
        }
        //si juste From est défini
        elseif($from != 1)
        {
            //passage de la date du format string au format DateTime
            $date_from = new DateTime($from);
            
            //génération de la requete
            $query = $qb->select('s')
                        ->from('Bcarmard\TestIAdvizeBundle\Entity\ApiVdm', 's')
                        ->where($qb->expr()->gte('s.date', ':date_from'))
                        ->setParameter('date_from', $date_from, \Doctrine\DBAL\Types\Type::DATETIME)
                        ->getQuery();
           
            $posts = $query->getResult();
            
        }
        //si juste To est défini
        elseif($to != 1)
        {
            //passage de la date du format string au format DateTime
            $date_to = new DateTime($to);
            
            //génération de la requete
            $query = $qb->select('s')
                        ->from('Bcarmard\TestIAdvizeBundle\Entity\ApiVdm', 's')
                        ->where($qb->expr()->lte('s.date', ':date_to'))
                        ->setParameter('date_to', $date_to, \Doctrine\DBAL\Types\Type::DATETIME)
                        ->getQuery();
           
            $posts = $query->getResult();
        }
        //si l'auteur est défini
        elseif($author !=1)
        {

            //génération de la requete
            $query = $qb->select('s')
                        ->from('Bcarmard\TestIAdvizeBundle\Entity\ApiVdm', 's')
                        ->where($qb->expr()->eq('s.auteur', ':author'))
                        ->setParameter('author', $author)
                        ->getQuery();
           
            $posts = $query->getResult();
        }
        //dans tous les autres cas
        else{
            //on récupère toutes les vdm
            $repo = $em->getRepository('BcarmardTestIAdvizeBundle:ApiVdm');
            $posts = $repo->findAll();
        }
        
        //on compte le nombre de posts récupérés
        $count = count($posts);
        
        //on passe un tableau associatif avec les données que l'on souhaite afficher.
        //FosRestBundle se charge du format.
        $view = $this->view(array(
                    'posts' => $posts,
                    'count' => $count)
                , 200);
        
        return $this->handleView($view);
    }
    

    public function getPostAction($id)
    {
        //appel de doctrine et du repository
        $em = $this->getDoctrine()
                ->getManager();
        $repo = $em->getRepository('BcarmardTestIAdvizeBundle:ApiVdm');
        //on récupère la vdm qui nous intéresse
        $post = $repo->find($id);
        //on passe les données à la vue.
        $view = $this->view(array(
                    'post' => $post
                )
                , 200);
        
        return $this->handleView($view);
    }

}
