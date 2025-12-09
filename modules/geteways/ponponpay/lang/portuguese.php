<?php
/**
 * PonponPay WHMCS Payment Gateway - Portuguese Language File
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
    'gateway_description' => 'Gateway de pagamento cripto profissional com suporte a USDT, BTC, ETH e mais em Tron, Ethereum, Polygon, Solana, etc. Recebimentos seguros e confiáveis.',
    'friendly_name' => 'PonponPay - Gateway de Pagamento Cripto',
    
    // Config descriptions
    'config_api_credentials' => '🔐 Credenciais da API',
    'config_api_key_desc' => 'Faça login no console do comerciante e copie a chave API da página <strong>"Chaves API"</strong>.',
    'config_credentials_validated' => 'As credenciais serão validadas automaticamente ao salvar.',
    'config_wallet_setup' => '⚙️ Configuração de Carteira e Pagamento',
    'config_wallets' => 'Carteiras',
    'config_wallets_desc' => 'adicionar endereços de recebimento',
    'config_payments' => 'Pagamentos',
    'config_payments_desc' => 'habilitar redes e moedas',
    'config_open_console' => 'Abrir ponponpay.com',
    
    // Payment selection page
    'invoice_already_paid' => 'Fatura já paga',
    'payment_page_opened' => 'Página de pagamento aberta em nova aba',
    'choose_payment_method' => 'Escolha um método de pagamento cripto',
    'invoice_amount' => 'Valor da Fatura',
    'payable_amount' => 'Valor a Pagar',
    'please_select_method' => 'Por favor selecione um método',
    'select_network_currency' => 'Selecione rede e moeda:',
    'please_choose_network' => 'Por favor escolha uma rede e moeda...',
    'create_crypto_payment' => 'Criar pagamento cripto',
    'creating_order' => 'Criando pedido de pagamento, aguarde...',
    'please_select_network' => 'Por favor selecione uma rede e moeda',
    'failed_create_order' => 'Falha ao criar pedido',
    'network_error_retry' => 'Erro de rede, tente novamente',
    'no_payment_methods' => 'Nenhum método de pagamento disponível, contate o suporte.',
    
    // Payment page
    'crypto_payment' => 'Pagamento Cripto',
    'time_remaining' => 'Tempo restante',
    'calculating' => 'calculando...',
    'amount_to_pay' => 'Valor a pagar',
    'scan_qr_to_pay' => 'Escaneie o código QR para pagar',
    'payment_qr_code' => 'Código QR de pagamento',
    'network' => 'Rede',
    'payment_address' => 'Endereço de pagamento',
    'copy' => 'Copiar',
    'copied' => 'Copiado',
    'payment_tips' => 'Dicas de pagamento:',
    'tip_correct_network' => 'Por favor use a rede correta (%s).',
    'tip_exact_amount' => 'O valor deve corresponder exatamente: %s %s.',
    'tip_complete_before_timer' => 'Complete o pagamento antes do tempo acabar.',
    'tip_auto_redirect' => 'A página redirecionará automaticamente após o pagamento.',
    'check_status' => 'Verificar status',
    'checking' => 'Verificando...',
    'refresh_page' => 'Atualizar página',
    'order_expired' => 'Pedido expirado',
    
    // Basic payment (no API)
    'basic_payment_title' => 'Pagamento Cripto PonponPay',
    'setup_reminder' => 'Lembrete de configuração:',
    'setup_reminder_desc' => 'Configure os seguintes itens nas configurações do gateway para habilitar a funcionalidade completa:',
    'setup_merchant_id' => 'ID do Comerciante',
    'setup_api_key_secret' => 'Chave API e segredo',
    'setup_wallet_address' => 'Configuração de endereço da carteira',
    
    // Error messages
    'payment_system_error' => 'Erro do sistema de pagamento',
    'contact_support' => 'Por favor contate o suporte para assistência.',
    'order_number_required' => 'Número do pedido ou ID da transação é necessário',
    
    // Validation errors
    'invalid_exchange_rate' => 'A taxa de câmbio deve ser um número maior que 0.',
    'invalid_api_key_format' => '⚠️ Formato inválido',
    'api_key_length_error' => 'O comprimento da chave API está incorreto. Comprimento atual: %d.',
    'api_key_fix' => 'A chave API deve ter pelo menos 32 caracteres. Cole a chave completa.',
    'activation_failed' => '⚠️ Falha na ativação',
    'settings_saved_inactive' => '💡 Nota: As configurações foram salvas mas o gateway está inativo porque a ativação falhou. Corrija o problema acima e salve novamente.',
    'api_connection_error' => '⚠️ Erro de conexão da API',
    'api_connection_error_desc' => 'Não foi possível conectar ao servidor do gateway de pagamento.',
    'details' => 'Detalhes',
    'fix' => 'Solução',
    'api_connection_fix' => 'Verifique a conectividade de rede, a URL do servidor API, ou contate o suporte.',
    'settings_saved_unverified' => '💡 Nota: As configurações foram salvas, mas o gateway pode não funcionar até que as credenciais sejam verificadas.',
    
    // Activation/Deactivation
    'gateway_activated' => 'Gateway PonponPay ativado. Os pedidos serão registrados via API do backend.',
    'gateway_deactivated' => 'O gateway PonponPay foi desativado.',
    
    // Network names
    'network_tron' => 'Tron (TRC20)',
    'network_ethereum' => 'Ethereum (ERC20)',
    'network_polygon' => 'Polygon (MATIC)',
    'network_solana' => 'Solana (SOL)',
    
    // Callback messages
    'gateway_not_configured' => 'Gateway não configurado',
    'invalid_json_data' => 'Dados JSON inválidos',
    'invalid_signature' => 'Assinatura inválida',
    'missing_order_number' => 'Número do pedido ausente',
    'invoice_not_found' => 'Fatura não encontrada',
    'signature_verification_failed' => 'Verificação de assinatura do callback falhou',
    'invoice_already_paid_ignore' => 'Fatura já paga, ignorando callback duplicado',
    'payment_success' => 'Pagamento bem-sucedido',
    'payment_processing_failed' => 'Processamento do pagamento falhou',
    'payment_failed_or_expired' => 'Pagamento falhou ou expirou',
    'unknown_payment_status' => 'Status de pagamento desconhecido',
    'missing_invoice_id' => 'ID da fatura ausente',
    'invoice_not_exist' => 'A fatura não existe',
    'order_number_not_exist' => 'O número do pedido não existe',
    'network_request_failed' => 'Requisição de rede falhou',
    'invalid_response_data' => 'Dados de resposta inválidos',
    
    // Status texts
    'status_pending' => 'Pagamento pendente',
    'status_paid' => 'Pagamento bem-sucedido',
    'status_failed' => 'Pagamento falhou',
    'status_expired' => 'Expirado',
    'status_unknown' => 'Status desconhecido',
    
    // Hooks
    'order_paid_log' => 'PonponPay: Pedido #%d pagamento bem-sucedido',
    'order_cancelled_log' => 'PonponPay: Pedido #%d cancelado',
    'check_failed' => 'Verificação falhou: ',
];
