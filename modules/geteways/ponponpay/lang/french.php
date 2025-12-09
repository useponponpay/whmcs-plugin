<?php
/**
 * PonponPay WHMCS Payment Gateway - French Language File
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
    'gateway_description' => 'Passerelle de paiement crypto professionnelle prenant en charge USDT, BTC, ETH et plus sur Tron, Ethereum, Polygon, Solana, etc. Encaissements sécurisés et fiables.',
    'friendly_name' => 'PonponPay - Passerelle de Paiement Crypto',
    
    // Config descriptions
    'config_api_credentials' => '🔐 Identifiants API',
    'config_api_key_desc' => 'Connectez-vous à la console marchand et copiez la clé API depuis la page <strong>"Clés API"</strong>.',
    'config_credentials_validated' => 'Les identifiants seront validés automatiquement lors de la sauvegarde.',
    'config_wallet_setup' => '⚙️ Configuration Portefeuille et Paiement',
    'config_wallets' => 'Portefeuilles',
    'config_wallets_desc' => 'ajouter des adresses de réception',
    'config_payments' => 'Paiements',
    'config_payments_desc' => 'activer les réseaux et devises',
    'config_open_console' => 'Ouvrir ponponpay.com',
    
    // Payment selection page
    'invoice_already_paid' => 'Facture déjà payée',
    'payment_page_opened' => 'Page de paiement ouverte dans un nouvel onglet',
    'choose_payment_method' => 'Choisissez un mode de paiement crypto',
    'invoice_amount' => 'Montant de la Facture',
    'payable_amount' => 'Montant à Payer',
    'please_select_method' => 'Veuillez sélectionner une méthode',
    'select_network_currency' => 'Sélectionnez le réseau et la devise:',
    'please_choose_network' => 'Veuillez choisir un réseau et une devise...',
    'create_crypto_payment' => 'Créer un paiement crypto',
    'creating_order' => 'Création de la commande de paiement, veuillez patienter...',
    'please_select_network' => 'Veuillez sélectionner un réseau et une devise',
    'failed_create_order' => 'Échec de la création de la commande',
    'network_error_retry' => 'Erreur réseau, veuillez réessayer',
    'no_payment_methods' => 'Aucune méthode de paiement disponible, contactez le support.',
    
    // Payment page
    'crypto_payment' => 'Paiement Crypto',
    'time_remaining' => 'Temps restant',
    'calculating' => 'calcul en cours...',
    'amount_to_pay' => 'Montant à payer',
    'scan_qr_to_pay' => 'Scannez le code QR pour payer',
    'payment_qr_code' => 'Code QR de paiement',
    'network' => 'Réseau',
    'payment_address' => 'Adresse de paiement',
    'copy' => 'Copier',
    'copied' => 'Copié',
    'payment_tips' => 'Conseils de paiement:',
    'tip_correct_network' => 'Veuillez utiliser le bon réseau (%s).',
    'tip_exact_amount' => 'Le montant doit correspondre exactement: %s %s.',
    'tip_complete_before_timer' => 'Complétez le paiement avant la fin du minuteur.',
    'tip_auto_redirect' => 'La page redirigera automatiquement après le paiement.',
    'check_status' => 'Vérifier le statut',
    'checking' => 'Vérification...',
    'refresh_page' => 'Actualiser la page',
    'order_expired' => 'Commande expirée',
    
    // Basic payment (no API)
    'basic_payment_title' => 'Paiement Crypto PonponPay',
    'setup_reminder' => 'Rappel de configuration:',
    'setup_reminder_desc' => 'Veuillez configurer les éléments suivants dans les paramètres de la passerelle pour activer toutes les fonctionnalités:',
    'setup_merchant_id' => 'ID Marchand',
    'setup_api_key_secret' => 'Clé API et secret',
    'setup_wallet_address' => 'Configuration de l\'adresse du portefeuille',
    
    // Error messages
    'payment_system_error' => 'Erreur du système de paiement',
    'contact_support' => 'Veuillez contacter le support pour obtenir de l\'aide.',
    'order_number_required' => 'Numéro de commande ou ID de transaction requis',
    
    // Validation errors
    'invalid_exchange_rate' => 'Le taux de change doit être un nombre supérieur à 0.',
    'invalid_api_key_format' => '⚠️ Format invalide',
    'api_key_length_error' => 'La longueur de la clé API est incorrecte. Longueur actuelle: %d.',
    'api_key_fix' => 'La clé API doit comporter au moins 32 caractères. Collez la clé complète.',
    'activation_failed' => '⚠️ Échec de l\'activation',
    'settings_saved_inactive' => '💡 Note: Les paramètres ont été sauvegardés mais la passerelle est inactive car l\'activation a échoué. Corrigez le problème ci-dessus et sauvegardez à nouveau.',
    'api_connection_error' => '⚠️ Erreur de connexion API',
    'api_connection_error_desc' => 'Impossible d\'atteindre le serveur de la passerelle de paiement.',
    'details' => 'Détails',
    'fix' => 'Solution',
    'api_connection_fix' => 'Vérifiez la connectivité réseau, l\'URL du serveur API, ou contactez le support.',
    'settings_saved_unverified' => '💡 Note: Les paramètres ont été sauvegardés, mais la passerelle peut ne pas fonctionner tant que les identifiants ne sont pas vérifiés.',
    
    // Activation/Deactivation
    'gateway_activated' => 'Passerelle PonponPay activée. Les commandes seront enregistrées via l\'API backend.',
    'gateway_deactivated' => 'La passerelle PonponPay a été désactivée.',
    
    // Network names
    'network_tron' => 'Tron (TRC20)',
    'network_ethereum' => 'Ethereum (ERC20)',
    'network_polygon' => 'Polygon (MATIC)',
    'network_solana' => 'Solana (SOL)',
    
    // Callback messages
    'gateway_not_configured' => 'Passerelle non configurée',
    'invalid_json_data' => 'Données JSON invalides',
    'invalid_signature' => 'Signature invalide',
    'missing_order_number' => 'Numéro de commande manquant',
    'invoice_not_found' => 'Facture non trouvée',
    'signature_verification_failed' => 'Échec de la vérification de la signature du callback',
    'invoice_already_paid_ignore' => 'Facture déjà payée, callback en double ignoré',
    'payment_success' => 'Paiement réussi',
    'payment_processing_failed' => 'Échec du traitement du paiement',
    'payment_failed_or_expired' => 'Paiement échoué ou expiré',
    'unknown_payment_status' => 'Statut de paiement inconnu',
    'missing_invoice_id' => 'ID de facture manquant',
    'invoice_not_exist' => 'La facture n\'existe pas',
    'order_number_not_exist' => 'Le numéro de commande n\'existe pas',
    'network_request_failed' => 'Échec de la requête réseau',
    'invalid_response_data' => 'Données de réponse invalides',
    
    // Status texts
    'status_pending' => 'Paiement en attente',
    'status_paid' => 'Paiement réussi',
    'status_failed' => 'Paiement échoué',
    'status_expired' => 'Expiré',
    'status_unknown' => 'Statut inconnu',
    
    // Hooks
    'order_paid_log' => 'PonponPay: Commande #%d paiement réussi',
    'order_cancelled_log' => 'PonponPay: Commande #%d annulée',
    'check_failed' => 'Échec de la vérification: ',
];
