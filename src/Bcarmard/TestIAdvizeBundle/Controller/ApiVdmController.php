<?php

namespace Bcarmard\TestIAdvizeBundle\Controller;

use Bcarmard\TestIAdvizeBundle\Entity\ApiVdm;
use DateTime;
use DOMDocument;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Description of ApivdmController
 *
 * @author Bcarmard
 */
class ApiVdmController extends Controller
{
    // affichage des posts présents en bdd s'il y en a et invitation à actualiser ces derniers.
    public function indexAction(){
        
        //On récupère les vdm en bdd
        $em = $this->getDoctrine()
                ->getManager();
        $repo = $em->getRepository('BcarmardTestIAdvizeBundle:ApiVdm');
        $result = $repo->findAll();
        //on apelle la vue index en lui passant les résultats de la requête
        return $this->render('BcarmardTestIAdvizeBundle:ApiVdm:index.html.twig',
                array ('result' => $result));
    }
    
    public function loadAction(){
        
        //on vide la base de données
        $em = $this->getDoctrine()->getManager();
        $connection = $em->getConnection();
        $platform = $connection->getDatabasePlatform();

        $connection->executeUpdate($platform->getTruncateTableSQL('apivdm'));
        
        //récupération des VDM
        
        $nbVdm = 0; //initialisation du compteur de vdm
        
        //a raison de 13 vdm/page 200/13 = 15.38 donc doit récupérer les vdm des 16 premières pages
        for($ii=0; $ii<16; $ii++)
        {
            //Récup du contenu de la page $ii à l'aide de cURL
            $url = 'http://m.viedemerde.fr/?page='.$ii.'';	
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_USERAGENT, 'test IAdvize Bcarmard');
            $resultat = curl_exec ($ch);
            curl_close($ch);
                        
            $page = new DOMDocument();
            @$page->loadHTML($resultat);
           
          
            //Pour chaque élement <li> de la page html
            foreach($page->getElementsByTagName('li') as $li)
            {

                //Si le nombre de vdm est < à 200
                if($nbVdm<200)
                {
                    // si l'id de <li> contien "fml"
                    if(strstr($li->getAttribute('id'),"fml"))
                    {
                        
                        // récup du Contenu de la Vdm
                        $contenu = $li->getElementsByTagName('p')->item(0)->nodeValue;
                        
                        //récup des infos de la vdm
                        $infos = $li->getElementsByTagName('span')->item(0)->nodeValue;

                        //extraction de la date
                        $dateFr = explode("/",substr($infos, 0, 10));
                        $date = new DateTime($dateFr[2]."-".$dateFr[1]."-".$dateFr[0]); //conversion de la date pour doctrine

                        //extraction du nom de l'auteur
                        $auteur =  substr($infos, 10, 100);

                        //Insertion de la VDM en bdd

                        $vdm = new ApiVdm();
                        $vdm->setContenu($contenu);
                        $vdm->setAuteur($auteur);
                        $vdm->setDate($date);

                        $em->persist($vdm);
                        $em->flush();

                        //Incrémentation du compteur de vdm
                        $nbVdm++;	
                    }
                }
            }
            
        }

        //redirection vers l'index
        return $this->redirect($this->generateUrl('bcarmard_apivdm_home'));
        

    }
}
