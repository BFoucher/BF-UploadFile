<?php

namespace UploadBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use UploadBundle\Entity\Fichier;
use UploadBundle\Form\FichierType;


class DefaultController extends Controller
{
    /**
     * @Route("/a-propos", name="a_propos")
     */
    public function aproposAction()
    {
        return $this->render('UploadBundle::apropos.html.twig');
    }


    /**
     * @Route("/", name="upload_file")
     */
    public function indexAction(Request $request)
    {
        $session = $request->getSession();
        
        if ($session->has('upload'))
        {
            $lastUpload = $session->get('upload');
            $now = time();
            $lastUploadTmp = [];
            foreach($lastUpload as $last){
                if ($last>($now-30)){
                    $lastUploadTmp[] = $last;
                }
            }
            $lastUpload = $lastUploadTmp;


            if(count($lastUpload)>3){
                throw $this->createNotFoundException(
                    'Too Many Upload Last Minute!'
                );
            }
        }

        $file = new Fichier();
        $form = $this->createForm(FichierType::class,$file);

        if ($request->getMethod() == 'POST') {
            if ($form->handleRequest($request)->isValid()) {
                if (!empty($file->getTmpFile())) {
                    $test = $file->preUpload($this->getParameter('upload_dir'));
                    if($test != true){
                        $this->addFlash('error','Erreur à la création du fichier '.$test);
                    };

                    $file->setDate(new \DateTime());
                    $lifetime = new \DateTime();
                    switch ($file->getLifetime()) {
                        case '10':
                            $lifetime->add(new \DateInterval('PT10M'));
                            break;
                        case '30':
                            $lifetime->add(new \DateInterval('PT30M'));
                            break;
                        case '60':
                            $lifetime->add(new \DateInterval('PT60M'));
                            break;
                        default:
                            $lifetime->add(new \DateInterval('PT30M'));

                    }

                    $file->setLifetime($lifetime);
                    $file->setActive(true);
                    $file->setIp($request->getClientIp());

                    $em = $this->getDoctrine()->getManager();
                    try{
                        $em->persist($file);
                        $em->flush();


                        $url = $this->generateUrl('file',['slug'=>$file->getPath()],true);
                        $this->addFlash('success','Envoi réussi: <a href="'.$url.'">'.$url.'</a>');
                        $lastUpload[] = $now;
                        $session->set('upload', $lastUpload);
                        return $this->render(
                            'UploadBundle:File:uploadSuccess.html.twig',
                            array('file' => $file)

                        );


                    }catch (Exception $e){
                        $file->deletTmpFile();
                        $this->addFlash('error','Erreur à la création du fichier');
                    }
                }
            }else{
                $this->addFlash('error',"Erreur durant l'envoi du fichier");
            }
        }

        return $this->render('UploadBundle:File:form.html.twig', array(
            'form' => $form->createView(),
        ));
    }


    /**
     * @Route("/file/{slug}", name="file")
     */
    public function showFile($slug)
    {
        $repo = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('UploadBundle:Fichier');

        $file = $repo->getFichier($slug);

        if (!$file || !$file->IsOpen()) {
            return $this->render(
                'UploadBundle:File:file_not_found.html.twig');
        }
        return $this->render(
            'UploadBundle:File:download.html.twig',
            array('file' => $file)

        );
    }

    /**
     * @Route("/download/{slug}" , name="download_file")
     *
     */
    public function DownloadAction($slug)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('UploadBundle:Fichier');

        $file = $repo->getFichier($slug);
        if (!$file || !$file->IsOpen()) {
            return $this->render(
                'UploadBundle:File:file_not_found.html.twig');
        }
        $file->setDownload($file->getDownload()+1);

        $em->persist($file);
        $em->flush();

        $response = new BinaryFileResponse( $this->getParameter('upload_dir') .$file->getPath());
        $response->trustXSendfileTypeHeader();
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT,$file->getName());


        return $response;

    }
}
