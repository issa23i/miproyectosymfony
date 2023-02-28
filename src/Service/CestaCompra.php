<?php

namespace App\Service;
use Symfony\Component\HttpFoundation\RequestStack;
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
    public function cambiar_unidades($cod, $unidades_cambiadas) {
        // comprobar que el artículo existe en la cesta
        if (array_key_exists($cod, $this->carrito)) {
            // si se ha metido un número 0 o negativo, se borran las unidades
            if ($unidades_cambiadas < 1) {
                unset($this->carrito[$cod]);
            } else {
                $this->carrito[$cod]['unidades'] = $unidades_cambiadas;
            }
        }
    }
}


