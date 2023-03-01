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
use App\Entity\Usuario;
use App\Entity\Pedido;
use App\Entity\PedidosProductos;
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
        $unidades = floatval($request->request->get('unidades'));
        
        // uso del servicio CestaCompra
        $cesta->cambiar_unidades($producto,$unidades);
        $cesta->guardarCesta();
         // $this->addFlash('exito', 'Se ha añadido el producto');
         //echo 'estoy en la función añadir';
        return $this->redirectToRoute('cesta');
    }
    
    /**
     * Recoge los datos del formulario de eliminar producto (request)
     * Hace una consulta a la bbdd con el entity manager doctrine
     * que obtiene el producto y hace uso de eliminar del servicio CestaCompra
     * guarda la sesión también haciendo uso del servicio CestaCompra
     * y vuelve a mostrar la cesta
     * 
     * @Route("/eliminar/{producto_id}", name="eliminar")
     */
    public function eliminar(Request $request, ManagerRegistry $doctrine, $producto_id, CestaCompra $cesta ): Response {
        
        $producto = $doctrine->getRepository(Product::class)->find($producto_id);
        $unidades = floatval($request->request->get('unidades'));
        
        // uso del servicio CestaCompra
        $cesta->eliminar($producto, $unidades);
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
        
        $carrito = $cesta->getCarrito();
        $total = $cesta->getTotal();
        
        return $this->render('ver_cesta.html.twig'
                                ,['carrito'=>$carrito, 'total'=>$total]);
    }
    
    /**
     * Obtiene el total con el request y el id del usuario 
     * Crea un nuevo Pedido y en el Entity manager, persiste y 
     * actualiza la base de datos, renderiza tanto si da error
     * como si se realiza con éxito
     * @param ManagerRegistry $doctrine
     * @param CestaCompra $cesta
     * @return Response
     * @Route("/pedido", name="pedido")
     */
    public function pedido(Request $request, ManagerRegistry $doctrine, CestaCompra $cesta ): Response {
        $em = $doctrine->getManager();
        
        $total = floatval($request->request->get('total'));
        $usuario = $this->getUser();
        
        $pedido = new Pedido;
        
        $pedido->setFecha(\DateTime::createFromFormat('Y-m-d', date("Y-m-d")));
        $pedido->setCoste($total);
        $pedido->setUsuario($usuario);
        $cod_pedido;
        
        
        try {
            // guardar o persistir el producto
            $em->persist($pedido);
            // enviar la consulta a la bbdd 
            $em->flush();
            // sólo después de enviar el flush se puede 
            // obtener el id (autoincremento)
            $cod_pedido = $pedido->getId();
            
        } catch (Exception $exc) {
            // si hubo error, obtiene el código del error 
            // y retorna el renderizado con el código del error
            // y el código de pedido será null
            $codigo_error = $exc->getCode();
            return $this->render('pedido.html.twig', [
                'cod_pedido'=>null,
                'error'=>$codigo_error,
            ]);
        }
        
        $cesta->borrarCesta();
        $cesta->guardarCesta();
        
        // si no hay error, se pone a null el error
        return $this->render('pedido.html.twig', [
            'cod_pedido'=>$cod_pedido,
            'error'=> null,
        ]);
    }
    
    /**
     * 
     * @param CestaCompra $cesta
     * @Route("/pedidos", name="pedidos")
     */
    public function pedidos(CestaCompra $cesta, Pedido $pedido, ManagerRegistry $doctrine) {
        // persistir y flush de la tabla pedidos_productos
        $em = $doctrine->getManager();
        $carrito = $cesta->getCarrito();
        try {
            
            foreach ($carrito as $producto) {
                $pedido_producto = new PedidosProductos;
                $pedido_producto->setProducto($producto['producto']);
                $pedido_producto->setPedido($pedido);
                $pedido_producto->setUnidades($producto['unidades']);
                $em->persist($pedido_producto);
            }
            
            $em->flush();
        } catch (Exception $exc) {
            // si hubo error, obtiene el código del error 
            // pone el pedido a null
            // y retorna el renderizado con el código del error
            // y el código de pedido será null
            $codigo_error = $exc->getCode();
            $pedido->removePedidosProducto($pedido_producto);
            return $this->render('pedido.html.twig', [
                'cod_pedido'=>null,
                'error'=>$codigo_error,
            ]);
        }
    }
    
}
