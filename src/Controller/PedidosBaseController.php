<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Familia;
use App\Entity\Product;
use Symfony\Component\HttpFoundation\Request;
use App\Service\CestaCompra;
//use App\Entity\Pedido;
//use App\Entity\PedidosProductos;
/**
*@IsGranted("ROLE_USER")
*/
class PedidosBaseController extends AbstractController
{
    /**
     * Hace una consulta a la BD mediante en entity manager
     * obtiendo el objeto Familias
     * Con findAll se obtienen todas 
     * finalmente se renderiza en la vista
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
    
    /**
     * Recoge los datos del formulario, id, unidades y producto (request)
     *      y hace una consulta a la bbdd (doctrine)
     *      y teniendo el producto lo pasa a cesta , (cambiar_unidades)
     *      la guarda en la sesión, (guardarCesta del servicio CestaCompra)
     *      llama a otro controlador (cesta)
     * 
     * @Route("/anadir/{producto_id}", name="anadir")
     */
    public function anadir(Request $request, ManagerRegistry $doctrine, $producto_id, CestaCompra $cesta ): Response {
        
        $producto = $doctrine->getRepository(Product::class)->find($producto_id);
        $unidades = $request->request->get('unidades');
        
        $cesta->cambiar_unidades($producto,$unidades);
        $cesta->guardarCesta();
        
        return $this->redirectToRoute('cesta');
    }
    
    /**
     * El controlador cesta recoge los datos de la sesión 
     *      , la obtiene del servicio CestaCompra
     *      que a su vez accede a la sesión con RequestStack
     *      Finalmente se renderiza pasando el array carrito 
     *      y el precio total como parámetros
     * 
     * @Route("/cesta", name="cesta")
     */
    public function obtenerCesta(CestaCompra $cesta): Response {
        $cesta->cargarCesta();
        $carrito = $cesta->getCarrito();
        $total = $cesta->getTotal();
        
        return $this->render('ver_cesta.html.twig'
                                ,['carrito'=>$carrito, 'total'=>$total]);
    }
}
