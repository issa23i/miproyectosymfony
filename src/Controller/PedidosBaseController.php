<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Familia;
use App\Entity\Product;
//use App\Entity\Pedido;
//use App\Entity\PedidosProductos;
/**
*@IsGranted("ROLE_USER")
*/
class PedidosBaseController extends AbstractController
{
    /**
     * inyectamos un servicio Manager Resgistry
     * @Route("/familias", name="familias")
     */
    public function obtenerFamilias(ManagerRegistry $doctrine): Response
    {
        $familias = $doctrine->getRepository(Familia::class)->findAll();
        return $this->render('familia.html.twig', [
            'familias' => $familias,
        ]);
    }
    
    /**
     * inyectamos un servicio Manager Resgistry
     * @Route("/productos/{id_familia}", name="productos")
     */
    public function obtenerProducts(ManagerRegistry $doctrine, $id_familia): Response
    {
        $productos = $doctrine->getRepository(Familia::class)->find($id_familia)->getProducts();
        return $this->render('listado_productos.html.twig', [
            'productos' => $productos,
        ]);
    }
    
    
}
