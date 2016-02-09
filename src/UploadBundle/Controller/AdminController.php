<?php

namespace UploadBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Config\Definition\Exception\Exception;

class AdminController extends Controller
{
    /**
     * @Route( "/view/files/{filter}/{order}",
     *         defaults={"filter" = "null", "order" = "DESC"},
     *         name="admin_list_files_active"
     *      )
     *
     */
    public function listFilesAction($filter=null,$order)
    {
        $repo = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('UploadBundle:Fichier');

        $listFiles = $repo->getAll($filter,$order);

        return $this->render(
            'UploadBundle:Admin:list.html.twig',
            array('files' => $listFiles)
        );
    }

    /**
     * @Route( "/view/file/{slug}",
     *         name="admin_file"
     *      )
     *
     */
    public function viewFileAction($slug)
    {
        $repo = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('UploadBundle:Fichier');

        $file = $repo->findOneByPath($slug);

        return $this->render(
            'UploadBundle:Admin:file.html.twig',
            array('file' => $file)
        );
    }

    /**
     * @Route( "delete/file/{slug}",
     *         name="admin_file_delete"
     *      )
     *
     */
    public function deleteAction($slug)
    {
        $em = $this->getDoctrine()->getManager();
        $repo =$em->getRepository('UploadBundle:Fichier');

        $file = $repo->findOneByPath($slug);
        if ($file->getActive() == true){
            try{
                unlink($this->getParameter('upload_dir').$file->getPath());
                $this->addFlash('notice',' Fichier supprimé : '.$file->getName());
            }catch (Exception $e){
                $this->createNotFoundException('Erreur à la suppression dud fichier '.$e);
                $this->addFlash('error',' Echec à la supression du fichier: '.$file->getName());
            }
        }
        $file->setActive(false);
        $em->persist($file);
        $em->flush();
        return $this->render(
            'UploadBundle:Admin:file.html.twig',
            array('file' => $file)
        );
    }

    /**
     * @Route( "/delete/all",
     *         name="admin_files_delete"
     *      )
     *
     */
    public function deleteAllAction()
    {
        $nbFiles = 0;
        $nbActive = 0;
        $em = $this->getDoctrine()->getManager();
        $repo =$em->getRepository('UploadBundle:Fichier');

        $listFiles = $repo->getAll('old');

        foreach($listFiles as $file){
            if ($file->getActive() == 1){
                if (file_exists($this->getParameter('upload_dir').$file->getPath())) {
                    unlink($this->getParameter('upload_dir') . $file->getPath());
                    $nbFiles++;
                }
                $file->setActive(false);
                $em->persist($file);
                $nbActive++;
            }
        }
        $em->flush();
        $this->addFlash('notice', ' '.$nbActive.' Fiches désactivées');
        $this->addFlash('notice', ' '.$nbFiles.' Fichiers supprimées');
        return $this->redirectToRoute('admin_list_files_active');
    }


}