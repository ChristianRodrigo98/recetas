<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\Length;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use App\Entity\Receta;
use App\Repository\RecetaRepository;
use Doctrine\ORM\EntityManagerInterface;

class RecetasController extends AbstractController
{
    #[Route('/recetas', name: 'app_recetas', methods:["GET"])]
    public function buscarTodas(RecetaRepository $repo): Response
    {
        $receta = $repo->findAll();
        return $this->render('recetas/index.html.twig', [ "controller_name" => "Hola, este es el indice de Recetas",
        "recetas" => $receta
    ]);

        //return $this->json($receta, Response::HTTP_OK);
    }

    #[ Route("/recetas", methods: ["POST"])]
    public function crearReceta (EntityManagerInterface $emi, Receta $receta): JsonResponse{
        $receta = new Receta();
        $receta->setNombre("A?");
        $receta->setTexto("que dicen");

        $emi->persist($receta);
        $emi->flush();

        return new JsonResponse("Receta creada con id ".$receta->getId(), Response::HTTP_CREATED);
    }

    #[Route("/recetas/receta_crear", name: "recetas_crear", methods:["GET", "POST"])]
    public function crearRecetaConFormulario(Request $request, EntityManagerInterface $emy): Response{
        $receta = new Receta();

        $fb = $this->createFormBuilder($receta);
        $fb->add("nombre", TextType::class, ["constraints" => [new Length(["min" => 15, "max" => 30])]]);
        $fb->add("texto", TextType::class);
        $fb->add("guardar", SubmitType::class);
        $formulario = $fb->getForm();

        $formulario->handleRequest($request);

        if ($formulario->isSubmitted() && $formulario->isValid()){
            $receta = $formulario->getData();
          return  $this->redirectToRoute("app_recetas");
        }else {
            return $this->render("recetas/receta_crear.html.twig", ["formulario" => $formulario]);
        }

    }

    #[ Route("/recetas/{idReceta}", name : "recetas_detalle", methods: ["GET"])]
    public function buscarReceta (RecetaRepository $repo ,int $idReceta): Response{
        $receta = $repo->find($idReceta);
        if($receta==null){
            return $this->json("Receta no encontrada", Response::HTTP_NOT_FOUND);
        }
        return $this->render("recetas/receta.html.twig", [ "receta" => $receta ]);
        //return $this->json($receta , Response::HTTP_OK);
    }

    #[ Route("/recetas/{idReceta}", methods:["PATCH"])]
    public function actualizarReceta(EntityManagerInterface $emi, RecetaRepository $repo, int $idReceta): JsonResponse{
        $receta = $repo->find($idReceta);
        if($receta!=null){
            $receta->setNombre("Nuevo nombre");
            $emi->flush();
            return $this->json("Receta con ID: ". $idReceta . " modificada.", Response::HTTP_OK);
        }else{
            return $this->json("Receta no encontrada.",Response::HTTP_NOT_FOUND);
        }
        
    }

    #[ Route("/recetas/{idReceta}", methods: ["DELETE"])]
    public function eliminarReceta (EntityManagerInterface $emi, RecetaRepository $repo, int $idReceta): Response{
        $receta = $repo->find($idReceta);
        if($receta!=null){
            $emi->remove($receta);
            $emi->flush();
            return $this->json("Receta con ID: ". $idReceta . " eliminada.", Response::HTTP_OK);
        }else{
            return $this->json("Receta no encontrada.",Response::HTTP_NOT_FOUND);
        }
    }
}
