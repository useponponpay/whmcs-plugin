<?php
/**
 * PonponPay WHMCS Payment Gateway - Japanese Language File
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
    'gateway_description' => 'USDT、BTC、ETHなど複数の通貨をサポートし、Tron、Ethereum、Polygon、Solanaなど複数のチェーンに対応したプロフェッショナルな暗号通貨決済ゲートウェイ。安全で信頼性の高い決済サービス。',
    'friendly_name' => 'PonponPay - 暗号通貨決済ゲートウェイ',
    
    // Config descriptions
    'config_api_credentials' => '🔐 API認証情報',
    'config_api_key_desc' => 'マーチャントコンソールにログインし、<strong>「APIキー」</strong>ページからAPIキーをコピーしてください。',
    'config_credentials_validated' => '保存時に認証情報が自動的に検証されます。',
    'config_wallet_setup' => '⚙️ ウォレットと決済設定',
    'config_wallets' => 'ウォレット',
    'config_wallets_desc' => '受取アドレスを追加',
    'config_payments' => '決済',
    'config_payments_desc' => 'ネットワークと通貨を有効化',
    'config_open_console' => 'ponponpay.comを開く',
    
    // Payment selection page
    'invoice_already_paid' => '請求書は支払い済みです',
    'payment_page_opened' => '決済ページが新しいタブで開きました',
    'choose_payment_method' => '暗号通貨決済方法を選択',
    'invoice_amount' => '請求金額',
    'payable_amount' => '支払金額',
    'please_select_method' => '方法を選択してください',
    'select_network_currency' => 'ネットワークと通貨を選択：',
    'please_choose_network' => 'ネットワークと通貨を選択してください...',
    'create_crypto_payment' => '暗号通貨決済を作成',
    'creating_order' => '決済注文を作成中、お待ちください...',
    'please_select_network' => 'ネットワークと通貨を選択してください',
    'failed_create_order' => '注文の作成に失敗しました',
    'network_error_retry' => 'ネットワークエラー、再試行してください',
    'no_payment_methods' => '利用可能な決済方法がありません。サポートにお問い合わせください。',
    
    // Payment page
    'crypto_payment' => '暗号通貨決済',
    'time_remaining' => '残り時間',
    'calculating' => '計算中...',
    'amount_to_pay' => '支払金額',
    'scan_qr_to_pay' => 'QRコードをスキャンして支払い',
    'payment_qr_code' => '決済QRコード',
    'network' => 'ネットワーク',
    'payment_address' => '支払アドレス',
    'copy' => 'コピー',
    'copied' => 'コピーしました',
    'payment_tips' => '支払いのヒント：',
    'tip_correct_network' => '正しいネットワーク（%s）を使用してください。',
    'tip_exact_amount' => '金額は正確に一致する必要があります：%s %s。',
    'tip_complete_before_timer' => 'タイマーが終了する前に支払いを完了してください。',
    'tip_auto_redirect' => '支払い後、ページは自動的にリダイレクトされます。',
    'check_status' => 'ステータス確認',
    'checking' => '確認中...',
    'refresh_page' => 'ページを更新',
    'order_expired' => '注文の有効期限が切れました',
    
    // Basic payment (no API)
    'basic_payment_title' => 'PonponPay 暗号通貨決済',
    'setup_reminder' => '設定リマインダー：',
    'setup_reminder_desc' => '完全な機能を有効にするには、ゲートウェイ設定で以下の項目を設定してください：',
    'setup_merchant_id' => 'マーチャントID',
    'setup_api_key_secret' => 'APIキーとシークレット',
    'setup_wallet_address' => 'ウォレットアドレス設定',
    
    // Error messages
    'payment_system_error' => '決済システムエラー',
    'contact_support' => 'サポートにお問い合わせください。',
    'order_number_required' => '注文番号または取引IDが必要です',
    
    // Validation errors
    'invalid_exchange_rate' => '為替レートは0より大きい数値である必要があります。',
    'invalid_api_key_format' => '⚠️ 無効な形式',
    'api_key_length_error' => 'APIキーの長さが正しくありません。現在の長さ：%d。',
    'api_key_fix' => 'APIキーは32文字以上である必要があります。完全なキーを貼り付けてください。',
    'activation_failed' => '⚠️ アクティベーション失敗',
    'settings_saved_inactive' => '💡 注意：設定は保存されましたが、アクティベーションに失敗したためゲートウェイは非アクティブです。上記の問題を修正して再度保存してください。',
    'api_connection_error' => '⚠️ API接続エラー',
    'api_connection_error_desc' => '決済ゲートウェイサーバーに接続できません。',
    'details' => '詳細',
    'fix' => '修正方法',
    'api_connection_fix' => 'ネットワーク接続を確認し、APIサーバーURLを確認するか、サポートにお問い合わせください。',
    'settings_saved_unverified' => '💡 注意：設定は保存されましたが、認証情報が確認されるまでゲートウェイが機能しない可能性があります。',
    
    // Activation/Deactivation
    'gateway_activated' => 'PonponPayゲートウェイがアクティベートされました。注文はバックエンドAPIを通じて記録されます。',
    'gateway_deactivated' => 'PonponPayゲートウェイが無効化されました。',
    
    // Network names
    'network_tron' => 'Tron (TRC20)',
    'network_ethereum' => 'Ethereum (ERC20)',
    'network_polygon' => 'Polygon (MATIC)',
    'network_solana' => 'Solana (SOL)',
    
    // Callback messages
    'gateway_not_configured' => 'ゲートウェイが設定されていません',
    'invalid_json_data' => '無効なJSONデータ',
    'invalid_signature' => '無効な署名',
    'missing_order_number' => '注文番号がありません',
    'invoice_not_found' => '請求書が見つかりません',
    'signature_verification_failed' => 'コールバック署名の検証に失敗しました',
    'invoice_already_paid_ignore' => '請求書は支払い済みです。重複コールバックを無視します',
    'payment_success' => '支払い成功',
    'payment_processing_failed' => '支払い処理に失敗しました',
    'payment_failed_or_expired' => '支払いに失敗したか、有効期限が切れました',
    'unknown_payment_status' => '不明な支払いステータス',
    'missing_invoice_id' => '請求書IDがありません',
    'invoice_not_exist' => '請求書が存在しません',
    'order_number_not_exist' => '注文番号が存在しません',
    'network_request_failed' => 'ネットワークリクエストに失敗しました',
    'invalid_response_data' => '無効なレスポンスデータ',
    
    // Status texts
    'status_pending' => '支払い待ち',
    'status_paid' => '支払い成功',
    'status_failed' => '支払い失敗',
    'status_expired' => '期限切れ',
    'status_unknown' => '不明なステータス',
    
    // Hooks
    'order_paid_log' => 'PonponPay：注文 #%d 支払い成功',
    'order_cancelled_log' => 'PonponPay：注文 #%d キャンセル',
    'check_failed' => '確認失敗：',
];
