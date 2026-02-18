<?php
/**
 * PonponPay WHMCS Payment Gateway - German Language File
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
    'gateway_description' => 'Professionelles Krypto-Zahlungsgateway mit Unterstützung für USDT, BTC, ETH und mehr auf Tron, Ethereum, Polygon, Solana usw. Sichere und zuverlässige Zahlungsabwicklung.',
    'friendly_name' => 'PonponPay - Krypto-Zahlungsgateway',
    
    // Config descriptions
    'config_api_credentials' => '🔐 API-Zugangsdaten',
    'config_api_key_desc' => 'Melden Sie sich in der Händlerkonsole an und kopieren Sie den API-Schlüssel von der Seite <strong>"API-Schlüssel"</strong>.',
    'config_credentials_validated' => 'Die Zugangsdaten werden beim Speichern automatisch validiert.',
    'config_wallet_setup' => '⚙️ Wallet- und Zahlungseinrichtung',
    'config_wallets' => 'Wallets',
    'config_wallets_desc' => 'Empfangsadressen hinzufügen',
    'config_payments' => 'Zahlungen',
    'config_payments_desc' => 'Netzwerke und Währungen aktivieren',
    'config_open_console' => 'ponponpay.com öffnen',
    
    // Payment selection page
    'invoice_already_paid' => 'Rechnung bereits bezahlt',
    'payment_page_opened' => 'Zahlungsseite in neuem Tab geöffnet',
    'choose_payment_method' => 'Wählen Sie eine Krypto-Zahlungsmethode',
    'invoice_amount' => 'Rechnungsbetrag',
    'payable_amount' => 'Zu zahlender Betrag',
    'please_select_method' => 'Bitte wählen Sie eine Methode',
    'select_network_currency' => 'Netzwerk und Währung auswählen:',
    'please_choose_network' => 'Bitte wählen Sie ein Netzwerk und eine Währung...',
    'create_crypto_payment' => 'Krypto-Zahlung erstellen',
    'creating_order' => 'Zahlungsauftrag wird erstellt, bitte warten...',
    'please_select_network' => 'Bitte wählen Sie ein Netzwerk und eine Währung',
    'failed_create_order' => 'Auftrag konnte nicht erstellt werden',
    'network_error_retry' => 'Netzwerkfehler, bitte erneut versuchen',
    'no_payment_methods' => 'Keine Zahlungsmethoden verfügbar, bitte kontaktieren Sie den Support.',
    
    // Payment page
    'crypto_payment' => 'Krypto-Zahlung',
    'time_remaining' => 'Verbleibende Zeit',
    'calculating' => 'wird berechnet...',
    'amount_to_pay' => 'Zu zahlender Betrag',
    'scan_qr_to_pay' => 'QR-Code scannen zum Bezahlen',
    'payment_qr_code' => 'Zahlungs-QR-Code',
    'network' => 'Netzwerk',
    'payment_address' => 'Zahlungsadresse',
    'copy' => 'Kopieren',
    'copied' => 'Kopiert',
    'payment_tips' => 'Zahlungshinweise:',
    'tip_correct_network' => 'Bitte verwenden Sie das richtige Netzwerk (%s).',
    'tip_exact_amount' => 'Der Betrag muss genau übereinstimmen: %s %s.',
    'tip_complete_before_timer' => 'Schließen Sie die Zahlung vor Ablauf des Timers ab.',
    'tip_auto_redirect' => 'Die Seite wird nach der Zahlung automatisch weitergeleitet.',
    'check_status' => 'Status prüfen',
    'checking' => 'Wird geprüft...',
    'refresh_page' => 'Seite aktualisieren',
    'order_expired' => 'Auftrag abgelaufen',
    
    // Basic payment (no API)
    'basic_payment_title' => 'PonponPay Krypto-Zahlung',
    'setup_reminder' => 'Einrichtungserinnerung:',
    'setup_reminder_desc' => 'Bitte konfigurieren Sie die folgenden Elemente in den Gateway-Einstellungen, um die volle Funktionalität zu aktivieren:',
    'setup_merchant_id' => 'Händler-ID',
    'setup_api_key_secret' => 'API-Schlüssel und Geheimnis',
    'setup_wallet_address' => 'Wallet-Adresskonfiguration',
    
    // Error messages
    'payment_system_error' => 'Zahlungssystemfehler',
    'contact_support' => 'Bitte kontaktieren Sie den Support für Hilfe.',
    'order_number_required' => 'Auftragsnummer oder Transaktions-ID erforderlich',
    
    // Validation errors
    'invalid_exchange_rate' => 'Der Wechselkurs muss eine Zahl größer als 0 sein.',
    'invalid_api_key_format' => '⚠️ Ungültiges Format',
    'api_key_length_error' => 'Die Länge des API-Schlüssels ist falsch. Aktuelle Länge: %d.',
    'api_key_fix' => 'Der API-Schlüssel sollte mindestens 32 Zeichen haben. Fügen Sie den vollständigen Schlüssel ein.',
    'activation_failed' => '⚠️ Aktivierung fehlgeschlagen',
    'settings_saved_inactive' => '💡 Hinweis: Die Einstellungen wurden gespeichert, aber das Gateway ist inaktiv, da die Aktivierung fehlgeschlagen ist. Beheben Sie das obige Problem und speichern Sie erneut.',
    'api_connection_error' => '⚠️ API-Verbindungsfehler',
    'api_connection_error_desc' => 'Der Zahlungsgateway-Server ist nicht erreichbar.',
    'details' => 'Details',
    'fix' => 'Lösung',
    'api_connection_fix' => 'Überprüfen Sie die Netzwerkverbindung, die API-Server-URL oder kontaktieren Sie den Support.',
    'settings_saved_unverified' => '💡 Hinweis: Die Einstellungen wurden gespeichert, aber das Gateway funktioniert möglicherweise nicht, bis die Zugangsdaten verifiziert sind.',
    
    // Activation/Deactivation
    'gateway_activated' => 'PonponPay-Gateway aktiviert. Aufträge werden über die Backend-API aufgezeichnet.',
    'gateway_deactivated' => 'Das PonponPay-Gateway wurde deaktiviert.',
    
    // Network names
    'network_tron' => 'Tron (TRC20)',
    'network_ethereum' => 'Ethereum (ERC20)',
    'network_polygon' => 'Polygon (MATIC)',
    'network_solana' => 'Solana (SOL)',
    
    // Callback messages
    'gateway_not_configured' => 'Gateway nicht konfiguriert',
    'invalid_json_data' => 'Ungültige JSON-Daten',
    'invalid_signature' => 'Ungültige Signatur',
    'missing_order_number' => 'Auftragsnummer fehlt',
    'invoice_not_found' => 'Rechnung nicht gefunden',
    'signature_verification_failed' => 'Callback-Signaturverifizierung fehlgeschlagen',
    'invoice_already_paid_ignore' => 'Rechnung bereits bezahlt, doppelter Callback wird ignoriert',
    'payment_success' => 'Zahlung erfolgreich',
    'payment_processing_failed' => 'Zahlungsverarbeitung fehlgeschlagen',
    'payment_failed_or_expired' => 'Zahlung fehlgeschlagen oder abgelaufen',
    'unknown_payment_status' => 'Unbekannter Zahlungsstatus',
    'missing_invoice_id' => 'Rechnungs-ID fehlt',
    'invoice_not_exist' => 'Rechnung existiert nicht',
    'order_number_not_exist' => 'Auftragsnummer existiert nicht',
    'network_request_failed' => 'Netzwerkanfrage fehlgeschlagen',
    'invalid_response_data' => 'Ungültige Antwortdaten',
    
    // Status texts
    'status_pending' => 'Zahlung ausstehend',
    'status_paid' => 'Zahlung erfolgreich',
    'status_failed' => 'Zahlung fehlgeschlagen',
    'status_expired' => 'Abgelaufen',
    'status_unknown' => 'Unbekannter Status',
    
    // Hooks
    'order_paid_log' => 'PonponPay: Auftrag #%d Zahlung erfolgreich',
    'order_cancelled_log' => 'PonponPay: Auftrag #%d storniert',
    'check_failed' => 'Prüfung fehlgeschlagen: ',
];
