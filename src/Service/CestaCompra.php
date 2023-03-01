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

    protected function cargarCesta() {
        if ($this->sesion->has('cesta')) {
            $this->carrito = $this->sesion->get('cesta');
        } else {
            $this->carrito = [];
        }
    }

    public function guardarCesta() {
        $this->sesion->set('cesta', $this->carrito);
    }
    
    public function borrarCesta() {
        $this->carrito = [];
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
            // si se ha metido un número 0 o negativo no hará nada
            // si es mayor que 1 suma las unidades
            if (!(floatval($unidades_cambiadas) < 1)) {
                $this->carrito[$cod_producto]['unidades'] += floatval($unidades_cambiadas);
            }
        } else {
            $this->carrito[$cod_producto]['unidades'] = floatval($unidades_cambiadas);
            $this->carrito[$cod_producto]['producto'] = $producto;
        }
    }

    public function eliminar(Product $producto, $unidades) {
        $cod_producto = $producto->getCod();
        // comprobar que el artículo existe en la cesta
        // si no existe no hace nada (caso imposible)
        if (array_key_exists($cod_producto, $this->carrito)) {
            // si se ha metido un número 0 o negativo no hará nada
            // si es mayor que 1 resta las unidades
            if (!(floatval($unidades) < 1)) {
                $total_unidades = floatval($this->carrito[$cod_producto]['unidades']) - floatval($unidades);
                // si el producto queda a 0 o menos
                if ($total_unidades < 1) {
                    unset($this->carrito[$cod_producto]);
                } else {
                    $this->carrito[$cod_producto]['unidades'] = $total_unidades;
                }
            }
        }
    }

    public function getTotal() {
        $total = 0;
        foreach ($this->carrito as $cod => $prod) {
            $precio = $prod['producto']->getPrecio();
            $unidades = $prod['unidades'];
            $total_producto = $precio * $unidades;
            $total += $total_producto;
        }
        return $total;
    }

    /**
     * Devuelve el array carrito
     * @return type $carrito
     */
    public function getCarrito() {
        return $this->carrito;
    }

}
