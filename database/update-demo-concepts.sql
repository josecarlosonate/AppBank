-- Ejecutar solo en una base de datos local de demostración AppBank.
-- No modifica movimientos, importes, saldos ni relaciones.
-- Conserva el marcador técnico que utiliza seed-demo.php.
-- Es idempotente: una segunda ejecución no modifica registros.

START TRANSACTION;

UPDATE transactions
SET concept = CONCAT(
    SUBSTRING(concept, 1, LOCATE('] ', concept) + 1),
    'Transferencia entre cuentas'
)
WHERE concept LIKE '[AppBank demo v1 #%] %'
  AND LOCATE('] ', concept) > 0
  AND SUBSTRING(concept, LOCATE('] ', concept) + 2) IN (
    'Arriendo',
    'Servicios del hogar',
    'Mercado',
    'Ahorro mensual',
    'Pago de proyecto',
    'Transporte',
    'Reembolso de compra'
  );

SELECT ROW_COUNT() AS conceptos_actualizados;

COMMIT;
