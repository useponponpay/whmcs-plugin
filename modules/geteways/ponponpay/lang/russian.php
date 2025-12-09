<?php
/**
 * PonponPay WHMCS Payment Gateway - Russian Language File
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
    'gateway_description' => 'Профессиональный криптовалютный платежный шлюз с поддержкой USDT, BTC, ETH и других валют на Tron, Ethereum, Polygon, Solana и т.д. Безопасный и надежный прием платежей.',
    'friendly_name' => 'PonponPay - Криптовалютный Платежный Шлюз',
    
    // Config descriptions
    'config_api_credentials' => '🔐 API Учетные данные',
    'config_api_key_desc' => 'Войдите в консоль продавца и скопируйте API ключ со страницы <strong>"API Ключи"</strong>.',
    'config_credentials_validated' => 'Учетные данные будут автоматически проверены при сохранении.',
    'config_wallet_setup' => '⚙️ Настройка Кошелька и Платежей',
    'config_wallets' => 'Кошельки',
    'config_wallets_desc' => 'добавить адреса получения',
    'config_payments' => 'Платежи',
    'config_payments_desc' => 'включить сети и валюты',
    'config_open_console' => 'Открыть ponponpay.com',
    
    // Payment selection page
    'invoice_already_paid' => 'Счет уже оплачен',
    'payment_page_opened' => 'Страница оплаты открыта в новой вкладке',
    'choose_payment_method' => 'Выберите способ криптовалютной оплаты',
    'invoice_amount' => 'Сумма Счета',
    'payable_amount' => 'Сумма к Оплате',
    'please_select_method' => 'Пожалуйста, выберите способ',
    'select_network_currency' => 'Выберите сеть и валюту:',
    'please_choose_network' => 'Пожалуйста, выберите сеть и валюту...',
    'create_crypto_payment' => 'Создать криптовалютный платеж',
    'creating_order' => 'Создание платежного заказа, пожалуйста подождите...',
    'please_select_network' => 'Пожалуйста, выберите сеть и валюту',
    'failed_create_order' => 'Не удалось создать заказ',
    'network_error_retry' => 'Ошибка сети, пожалуйста повторите',
    'no_payment_methods' => 'Нет доступных способов оплаты, обратитесь в поддержку.',
    
    // Payment page
    'crypto_payment' => 'Криптовалютный Платеж',
    'time_remaining' => 'Оставшееся время',
    'calculating' => 'вычисление...',
    'amount_to_pay' => 'Сумма к оплате',
    'scan_qr_to_pay' => 'Отсканируйте QR-код для оплаты',
    'payment_qr_code' => 'QR-код платежа',
    'network' => 'Сеть',
    'payment_address' => 'Адрес платежа',
    'copy' => 'Копировать',
    'copied' => 'Скопировано',
    'payment_tips' => 'Советы по оплате:',
    'tip_correct_network' => 'Пожалуйста, используйте правильную сеть (%s).',
    'tip_exact_amount' => 'Сумма должна точно совпадать: %s %s.',
    'tip_complete_before_timer' => 'Завершите оплату до истечения таймера.',
    'tip_auto_redirect' => 'Страница автоматически перенаправится после оплаты.',
    'check_status' => 'Проверить статус',
    'checking' => 'Проверка...',
    'refresh_page' => 'Обновить страницу',
    'order_expired' => 'Заказ истек',
    
    // Basic payment (no API)
    'basic_payment_title' => 'Криптовалютный Платеж PonponPay',
    'setup_reminder' => 'Напоминание о настройке:',
    'setup_reminder_desc' => 'Пожалуйста, настройте следующие элементы в настройках шлюза для включения полной функциональности:',
    'setup_merchant_id' => 'ID Продавца',
    'setup_api_key_secret' => 'API ключ и секрет',
    'setup_wallet_address' => 'Настройка адреса кошелька',
    
    // Error messages
    'payment_system_error' => 'Ошибка платежной системы',
    'contact_support' => 'Пожалуйста, обратитесь в поддержку за помощью.',
    'order_number_required' => 'Требуется номер заказа или ID транзакции',
    
    // Validation errors
    'invalid_exchange_rate' => 'Обменный курс должен быть числом больше 0.',
    'invalid_api_key_format' => '⚠️ Неверный формат',
    'api_key_length_error' => 'Длина API ключа неверна. Текущая длина: %d.',
    'api_key_fix' => 'API ключ должен содержать не менее 32 символов. Вставьте полный ключ.',
    'activation_failed' => '⚠️ Активация не удалась',
    'settings_saved_inactive' => '💡 Примечание: Настройки сохранены, но шлюз неактивен из-за неудачной активации. Исправьте проблему выше и сохраните снова.',
    'api_connection_error' => '⚠️ Ошибка подключения API',
    'api_connection_error_desc' => 'Не удается подключиться к серверу платежного шлюза.',
    'details' => 'Детали',
    'fix' => 'Решение',
    'api_connection_fix' => 'Проверьте сетевое подключение, URL сервера API или обратитесь в поддержку.',
    'settings_saved_unverified' => '💡 Примечание: Настройки сохранены, но шлюз может не работать до проверки учетных данных.',
    
    // Activation/Deactivation
    'gateway_activated' => 'Шлюз PonponPay активирован. Заказы будут записываться через backend API.',
    'gateway_deactivated' => 'Шлюз PonponPay был деактивирован.',
    
    // Network names
    'network_tron' => 'Tron (TRC20)',
    'network_ethereum' => 'Ethereum (ERC20)',
    'network_polygon' => 'Polygon (MATIC)',
    'network_solana' => 'Solana (SOL)',
    
    // Callback messages
    'gateway_not_configured' => 'Шлюз не настроен',
    'invalid_json_data' => 'Неверные JSON данные',
    'invalid_signature' => 'Неверная подпись',
    'missing_order_number' => 'Отсутствует номер заказа',
    'invoice_not_found' => 'Счет не найден',
    'signature_verification_failed' => 'Проверка подписи callback не удалась',
    'invoice_already_paid_ignore' => 'Счет уже оплачен, дублирующий callback игнорируется',
    'payment_success' => 'Платеж успешен',
    'payment_processing_failed' => 'Обработка платежа не удалась',
    'payment_failed_or_expired' => 'Платеж не удался или истек',
    'unknown_payment_status' => 'Неизвестный статус платежа',
    'missing_invoice_id' => 'Отсутствует ID счета',
    'invoice_not_exist' => 'Счет не существует',
    'order_number_not_exist' => 'Номер заказа не существует',
    'network_request_failed' => 'Сетевой запрос не удался',
    'invalid_response_data' => 'Неверные данные ответа',
    
    // Status texts
    'status_pending' => 'Ожидание платежа',
    'status_paid' => 'Платеж успешен',
    'status_failed' => 'Платеж не удался',
    'status_expired' => 'Истек',
    'status_unknown' => 'Неизвестный статус',
    
    // Hooks
    'order_paid_log' => 'PonponPay: Заказ #%d платеж успешен',
    'order_cancelled_log' => 'PonponPay: Заказ #%d отменен',
    'check_failed' => 'Проверка не удалась: ',
];
