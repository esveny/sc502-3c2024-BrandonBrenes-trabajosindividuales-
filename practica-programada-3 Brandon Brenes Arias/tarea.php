<?php

$transacciones = [];

function registrarTransaccion($id, $descripcion, $monto) {
    global $transacciones;
    $transaccion = [
        'id' => $id,
        'descripcion' => $descripcion,
        'monto' => $monto
    ];
    array_push($transacciones, $transaccion);
}

function generarEstadoDeCuenta() {
    global $transacciones;
    
    $montoContado = 0;

    foreach ($transacciones as $transaccion) {
        $montoContado += $transaccion['monto'];
    }

    $montoConInteres = $montoContado * 1.026;

    $cashback = $montoContado * 0.001;

    $montoFinal = $montoConInteres - $cashback;

    echo "Estado de Cuenta\n";
    echo "----------------\n";
    foreach ($transacciones as $transaccion) {
        echo "ID: {$transaccion['id']}, Descripción: {$transaccion['descripcion']}, Monto: {$transaccion['monto']}\n";
    }
    echo "Monto de contado: $montoContado\n";
    echo "Monto con interés (2.6%): $montoConInteres\n";
    echo "Cashback (0.1%): $cashback\n";
    echo "Monto final a pagar: $montoFinal\n";

    $contenido = "Estado de Cuenta\n----------------\n";
    foreach ($transacciones as $transaccion) {
        $contenido .= "ID: {$transaccion['id']}, Descripción: {$transaccion['descripcion']}, Monto: {$transaccion['monto']}\n";
    }
    $contenido .= "Monto de contado: $montoContado\n";
    $contenido .= "Monto con interés (2.6%): $montoConInteres\n";
    $contenido .= "Cashback (0.1%): $cashback\n";
    $contenido .= "Monto final a pagar: $montoFinal\n";

    file_put_contents("estado_cuenta.txt", $contenido);
}

registrarTransaccion(1, "Compra en supermercado", 1000);
registrarTransaccion(2, "Pago de servicios", 250000);
registrarTransaccion(3, "Cena en restaurante", 30000);

//mostrar el estado de cuenta
generarEstadoDeCuenta();

?>
