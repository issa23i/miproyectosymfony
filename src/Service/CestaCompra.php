<?php

namespace App\Service;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Entity\Product;
/**
 * Description of CESTACOMPRA
 *
 * @author issa2
 */
class CestaCompra {
    protected $carrito = [];
    protected $requestStack;
    protected $sesion;
    
    public function __construct(RequestStack $requestStack) {
        $this->requestStack = $requestStack;
        $this->sesion = $requestStack->getCurrentRequest()->getSession();
        $this->cargarCesta();
    }

    public function cargarCesta(){
        if($this->sesion->has('cesta')){
            $this->carrito = $this->sesion->get('cesta');
        } else {
           $this->guardarCesta();
        }
    }
    
    public function guardarCesta(){
        $this->sesion->set('cesta',$this->carrito);
    }
    
    /**
     * Carga la cesta de la sesión,
     * Busca si existe el código pasado por parámetro en la cesta,
     * Si existe, cambia las unidades por las unidades pasadas por parámetro
     * comprobando que si es 0 o número negativo, borre el artículo de la cesta
     * @param type $cod
     * @param type $unidades_cambiadas
     */
    public function cambiar_unidades(Product $producto, $unidades_cambiadas) {
        $cod_producto = $producto->getCod();
        // comprobar que el artículo existe en la cesta
        if (array_key_exists($cod_producto, $this->carrito)) {
            // si se ha metido un número 0 o negativo, se borran las unidades
            if ($unidades_cambiadas < 1) {
                unset($this->carrito[$cod]);
            } else {
                $this->carrito[$cod]['unidades'] = $unidades_cambiadas;
                $this->carrito[$cod]['producto'] = $producto;
            }
        }
    }
    
    public function getTotal(){
        $total = 0;
        foreach ($this->carrito as $cod => $prod) {
            
        }
    }
    
    /**
     * Devuelve el array carrito
     * @return type $carrito
     */
    public function getCarrito(){
        return $this->carrito;
    }
}


