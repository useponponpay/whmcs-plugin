<?php
/**
 * PonponPay WHMCS Payment Gateway - Spanish Language File
 *
 * @package    PonponPay
 * @author     PonponPay Engineering
 * @version    2.0.0
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

$_PONPONPAY_LANG = [
    // Meta & Config
    'gateway_name' => 'PonponPay',
    'gateway_description' => 'Pasarela de pago cripto profesional que soporta USDT, BTC, ETH y más en Tron, Ethereum, Polygon, Solana, etc. Cobros seguros y confiables.',
    'friendly_name' => 'PonponPay - Pasarela de Pago Cripto',
    
    // Config descriptions
    'config_api_credentials' => '🔐 Credenciales API',
    'config_api_key_desc' => 'Inicie sesión en la consola del comerciante y copie la clave API de la página <strong>"Claves API"</strong>.',
    'config_credentials_validated' => 'Las credenciales se validarán automáticamente al guardar.',
    'config_wallet_setup' => '⚙️ Configuración de Billetera y Pago',
    'config_wallets' => 'Billeteras',
    'config_wallets_desc' => 'agregar direcciones de recepción',
    'config_payments' => 'Pagos',
    'config_payments_desc' => 'habilitar redes y monedas',
    'config_open_console' => 'Abrir ponponpay.com',
    
    // Payment selection page
    'invoice_already_paid' => 'Factura ya pagada',
    'payment_page_opened' => 'Página de pago abierta en una nueva pestaña',
    'choose_payment_method' => 'Elija un método de pago cripto',
    'invoice_amount' => 'Monto de la Factura',
    'payable_amount' => 'Monto a Pagar',
    'please_select_method' => 'Por favor seleccione un método',
    'select_network_currency' => 'Seleccione red y moneda:',
    'please_choose_network' => 'Por favor elija una red y moneda...',
    'create_crypto_payment' => 'Crear pago cripto',
    'creating_order' => 'Creando orden de pago, por favor espere...',
    'please_select_network' => 'Por favor seleccione una red y moneda',
    'failed_create_order' => 'Error al crear la orden',
    'network_error_retry' => 'Error de red, por favor reintente',
    'no_payment_methods' => 'No hay métodos de pago disponibles, contacte soporte.',
    
    // Payment page
    'crypto_payment' => 'Pago Cripto',
    'time_remaining' => 'Tiempo restante',
    'calculating' => 'calculando...',
    'amount_to_pay' => 'Monto a pagar',
    'scan_qr_to_pay' => 'Escanee el código QR para pagar',
    'payment_qr_code' => 'Código QR de pago',
    'network' => 'Red',
    'payment_address' => 'Dirección de pago',
    'copy' => 'Copiar',
    'copied' => 'Copiado',
    'payment_tips' => 'Consejos de pago:',
    'tip_correct_network' => 'Por favor use la red correcta (%s).',
    'tip_exact_amount' => 'El monto debe coincidir exactamente: %s %s.',
    'tip_complete_before_timer' => 'Complete el pago antes de que termine el temporizador.',
    'tip_auto_redirect' => 'La página redirigirá automáticamente después del pago.',
    'check_status' => 'Verificar estado',
    'checking' => 'Verificando...',
    'refresh_page' => 'Actualizar página',
    'order_expired' => 'Orden expirada',
    
    // Basic payment (no API)
    'basic_payment_title' => 'Pago Cripto PonponPay',
    'setup_reminder' => 'Recordatorio de configuración:',
    'setup_reminder_desc' => 'Configure los siguientes elementos en la configuración de la pasarela para habilitar la funcionalidad completa:',
    'setup_merchant_id' => 'ID de Comerciante',
    'setup_api_key_secret' => 'Clave API y secreto',
    'setup_wallet_address' => 'Configuración de dirección de billetera',
    
    // Error messages
    'payment_system_error' => 'Error del sistema de pago',
    'contact_support' => 'Por favor contacte soporte para asistencia.',
    'order_number_required' => 'Se requiere número de orden o ID de transacción',
    
    // Validation errors
    'invalid_exchange_rate' => 'La tasa de cambio debe ser un número mayor que 0.',
    'invalid_api_key_format' => '⚠️ Formato inválido',
    'api_key_length_error' => 'La longitud de la clave API es incorrecta. Longitud actual: %d.',
    'api_key_fix' => 'La clave API debe tener al menos 32 caracteres. Pegue la clave completa.',
    'activation_failed' => '⚠️ Activación fallida',
    'settings_saved_inactive' => '💡 Nota: La configuración se guardó pero la pasarela está inactiva porque la activación falló. Corrija el problema anterior y guarde nuevamente.',
    'api_connection_error' => '⚠️ Error de conexión API',
    'api_connection_error_desc' => 'No se puede conectar al servidor de la pasarela de pago.',
    'details' => 'Detalles',
    'fix' => 'Solución',
    'api_connection_fix' => 'Verifique la conectividad de red, la URL del servidor API, o contacte soporte.',
    'settings_saved_unverified' => '💡 Nota: La configuración se guardó, pero la pasarela puede no funcionar hasta que se verifiquen las credenciales.',
    
    // Activation/Deactivation
    'gateway_activated' => 'Pasarela PonponPay activada. Las órdenes se registrarán a través de la API del backend.',
    'gateway_deactivated' => 'La pasarela PonponPay ha sido desactivada.',
    
    // Network names
    'network_tron' => 'Tron (TRC20)',
    'network_ethereum' => 'Ethereum (ERC20)',
    'network_polygon' => 'Polygon (MATIC)',
    'network_solana' => 'Solana (SOL)',
    
    // Callback messages
    'gateway_not_configured' => 'Pasarela no configurada',
    'invalid_json_data' => 'Datos JSON inválidos',
    'invalid_signature' => 'Firma inválida',
    'missing_order_number' => 'Falta número de orden',
    'invoice_not_found' => 'Factura no encontrada',
    'signature_verification_failed' => 'Verificación de firma de callback fallida',
    'invoice_already_paid_ignore' => 'Factura ya pagada, ignorando callback duplicado',
    'payment_success' => 'Pago exitoso',
    'payment_processing_failed' => 'Procesamiento de pago fallido',
    'payment_failed_or_expired' => 'Pago fallido o expirado',
    'unknown_payment_status' => 'Estado de pago desconocido',
    'missing_invoice_id' => 'Falta ID de factura',
    'invoice_not_exist' => 'La factura no existe',
    'order_number_not_exist' => 'El número de orden no existe',
    'network_request_failed' => 'Solicitud de red fallida',
    'invalid_response_data' => 'Datos de respuesta inválidos',
    
    // Status texts
    'status_pending' => 'Pago pendiente',
    'status_paid' => 'Pago exitoso',
    'status_failed' => 'Pago fallido',
    'status_expired' => 'Expirado',
    'status_unknown' => 'Estado desconocido',
    
    // Hooks
    'order_paid_log' => 'PonponPay: Orden #%d pago exitoso',
    'order_cancelled_log' => 'PonponPay: Orden #%d cancelada',
    'check_failed' => 'Verificación fallida: ',
];
