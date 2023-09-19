## Endpoints
### iflow
    1) obtener token                                            http://api.iflow21.com/api/login
    2) obtener ventas de newsan                                 http://api.iflow21.com/api/v1/client/shipping/?page=1&limit=100
    3) obtener listado de estados de la orden (shipment_id)     http://api.iflow21.com/api/order/state/OR0022306581
### newsan
    1) notificar a la api de newsan                             https://api.newsan.com.ar/api/notifications/IFLOW

## Proceso
1) me traigo las ventas de newsan y las guardo en NewSan_orders en estado no finalizado
2) obtengo los registros no finalizados de la tabla NewSan_orders y por cada una de ellos:
    - obtengo y actualizo el ultimo estado de la api de iflow.
    - si el ultimo estado es `Entregado` o `En proceso de devolucion` se marca el registro como finalizado.
    - luego creo un registro en NewSan_orders_informed con estado no finalizado si es que aun no se encuentra registrado anteriormente.
3) obtengo los registros no finalizados de la table NewSan_order_informed y por cada una de ellos:
    - notifico a la api de NewSan
    - si el codigo de la respuesta es 200 (correcta):
        - se aumenta en 1 la cantidad de notificados
        - si el estado es `Entregado` o `En proceso de devolucion`:
            - se marca el registro como finalizado
            - se aumenta en 1 la cantidad de finalizados
    - si el codigo de la respuesta no es 200

## Estados de ordenes conocidos
```
[
    1  => 'Registrado',
    8  => 'Retirado',
    10 => 'Descargado',
    30 => 'Despachado a Nodo Interno',
    31 => 'Arribo a Nodo',
    35 => 'Pedido en Distribución',
    25 => 'Entregado',
    26 => 'No Entregado',
    51 => 'En proceso de devolucion',
    53 => 'Devolucion a Central',
]
```
