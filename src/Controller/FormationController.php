<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Form\FormationType;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Category;
use App\Form\CategoryType;
use App\Entity\CategorySearch;
use App\Form\CategorySearchType;
use Dompdf\Dompdf;
use Dompdf\Options;
use SweetAlert;
use Knp\Component\Pager\PaginatorInterface;
class FormationController extends AbstractController
{
    /**
     * @Route("/formation", name="formation")
     */
    public function index(Request $request, PaginatorInterface $paginator){
        $categorySearch = new CategorySearch();
        $form = $this->createForm(CategorySearchType::class,$categorySearch);
        $form->handleRequest($request);

        $donnees = $this->getDoctrine()->getRepository(Formation::class)->findBy([],['id' => 'desc']);
        $formation= $paginator->paginate(
            $donnees, // Requête contenant les données à paginer (ici nos articles)
            $request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
            3 // Nombre de résultats par page
        );

        if($form->isSubmitted() && $form->isValid()) {
            $category = $categorySearch->getCategory();

            if ($category!="")
                $formation= $category->getFormation();
            else

                $donnees = $this->getDoctrine()->getRepository(Formation::class)->findBy([],['id' => 'desc']);
            $formation= $paginator->paginate(
                $donnees, // Requête contenant les données à paginer (ici nos articles)
                $request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
                3 // Nombre de résultats par page
            );
        }

        return $this->render('formation/index.html.twig',[ 'form' =>$form->createView(), 'formation' => $formation]);


    }




    /**
     * @Route("/pdf", name="formation_pdf")
     */
    public function pdf()
    {
        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);

        $formation= $this->getDoctrine()->getRepository(formation::class)->findAll();



        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('formation/pdf.html.twig', ['formation' => $formation, ]);

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (force download)
        $dompdf->stream("mypdf.pdf", [
            "Attachment" => true
        ]);


    }
    /**
     * @Route("/formation/new", name="new_formation")
     * Method({"GET", "POST"})
     */
    public function new(Request $request) {
        $formation = new formation();
        $form = $this->createForm(FormationType::class,$formation);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $formation = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($formation);
            $entityManager->flush();
            return $this->redirectToRoute('formation');
        }
        return $this->render('formation/new.html.twig',['form' => $form->createView()]);
    }




    /**
     * @Route("/formation/edit/{id}", name="edit_formation")
     * Method({"GET", "POST"})
     */
    public function edit(Request $request, $id) {
        $formation = new formation;
        $formation = $this->getDoctrine()->getRepository(formation::class)->find($id);

        $form = $this->createForm(FormationType::class,$formation);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            return $this->redirectToRoute('formation');
        }

        return $this->render('formation/edit.html.twig', ['form' =>$form->createView()]);
    }


    /**
     * @Route("/formation/delete/{id}",name="delete_formation")
     * Method({"DELETE"})
     */
    public function delete(Request $request, $id) {
        $formation = $this->getDoctrine()->getRepository(formation::class)->find($id);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($formation);
        $entityManager->flush();

        $response = new Response();
        $response->send();

        return $this->redirectToRoute('formation');

    }

    /**
     * @Route("/formation/{id}", name="formation_show")
     */
    public function show($id) {
        $formation= $this->getDoctrine()->getRepository(formation::class)->find($id);
        return $this->render('formation/show.html.twig',
            array('formation' => $formation));
    }


    /**
     * @Route("/category/newCat", name="new_category")
     * Method({"GET", "POST"})
     */
    public function newCategory(Request $request) {
        $category = new Category();
        $form = $this->createForm(CategoryType::class,$category);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $formation = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($category);
            $entityManager->flush();
            return $this->redirectToRoute('formation');
        }
        return $this->render('formation/newCategory.html.twig',['form'=>
            $form->createView()]);
    }




}
