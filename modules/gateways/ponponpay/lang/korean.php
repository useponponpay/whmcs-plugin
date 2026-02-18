<?php
/**
 * PonponPay WHMCS Payment Gateway - Korean Language File
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
    'gateway_description' => 'USDT, BTC, ETH 등 다양한 암호화폐를 지원하고 Tron, Ethereum, Polygon, Solana 등 여러 체인을 지원하는 전문 암호화폐 결제 게이트웨이. 안전하고 신뢰할 수 있는 결제 서비스.',
    'friendly_name' => 'PonponPay - 암호화폐 결제 게이트웨이',
    
    // Config descriptions
    'config_api_credentials' => '🔐 API 자격 증명',
    'config_api_key_desc' => '판매자 콘솔에 로그인하여 <strong>"API 키"</strong> 페이지에서 API 키를 복사하세요.',
    'config_credentials_validated' => '저장 시 자격 증명이 자동으로 검증됩니다.',
    'config_wallet_setup' => '⚙️ 지갑 및 결제 설정',
    'config_wallets' => '지갑',
    'config_wallets_desc' => '수신 주소 추가',
    'config_payments' => '결제',
    'config_payments_desc' => '네트워크 및 통화 활성화',
    'config_open_console' => 'ponponpay.com 열기',
    
    // Payment selection page
    'invoice_already_paid' => '청구서가 이미 결제되었습니다',
    'payment_page_opened' => '새 탭에서 결제 페이지가 열렸습니다',
    'choose_payment_method' => '암호화폐 결제 방법 선택',
    'invoice_amount' => '청구 금액',
    'payable_amount' => '결제 금액',
    'please_select_method' => '방법을 선택하세요',
    'select_network_currency' => '네트워크 및 통화 선택:',
    'please_choose_network' => '네트워크와 통화를 선택하세요...',
    'create_crypto_payment' => '암호화폐 결제 생성',
    'creating_order' => '결제 주문 생성 중, 잠시 기다려 주세요...',
    'please_select_network' => '네트워크와 통화를 선택하세요',
    'failed_create_order' => '주문 생성 실패',
    'network_error_retry' => '네트워크 오류, 다시 시도하세요',
    'no_payment_methods' => '사용 가능한 결제 방법이 없습니다. 지원팀에 문의하세요.',
    
    // Payment page
    'crypto_payment' => '암호화폐 결제',
    'time_remaining' => '남은 시간',
    'calculating' => '계산 중...',
    'amount_to_pay' => '결제 금액',
    'scan_qr_to_pay' => 'QR 코드를 스캔하여 결제',
    'payment_qr_code' => '결제 QR 코드',
    'network' => '네트워크',
    'payment_address' => '결제 주소',
    'copy' => '복사',
    'copied' => '복사됨',
    'payment_tips' => '결제 팁:',
    'tip_correct_network' => '올바른 네트워크(%s)를 사용하세요.',
    'tip_exact_amount' => '금액이 정확히 일치해야 합니다: %s %s.',
    'tip_complete_before_timer' => '타이머가 끝나기 전에 결제를 완료하세요.',
    'tip_auto_redirect' => '결제 후 페이지가 자동으로 리디렉션됩니다.',
    'check_status' => '상태 확인',
    'checking' => '확인 중...',
    'refresh_page' => '페이지 새로고침',
    'order_expired' => '주문 만료됨',
    
    // Basic payment (no API)
    'basic_payment_title' => 'PonponPay 암호화폐 결제',
    'setup_reminder' => '설정 알림:',
    'setup_reminder_desc' => '전체 기능을 활성화하려면 게이트웨이 설정에서 다음 항목을 구성하세요:',
    'setup_merchant_id' => '판매자 ID',
    'setup_api_key_secret' => 'API 키 및 시크릿',
    'setup_wallet_address' => '지갑 주소 구성',
    
    // Error messages
    'payment_system_error' => '결제 시스템 오류',
    'contact_support' => '도움이 필요하시면 지원팀에 문의하세요.',
    'order_number_required' => '주문 번호 또는 거래 ID가 필요합니다',
    
    // Validation errors
    'invalid_exchange_rate' => '환율은 0보다 큰 숫자여야 합니다.',
    'invalid_api_key_format' => '⚠️ 잘못된 형식',
    'api_key_length_error' => 'API 키 길이가 올바르지 않습니다. 현재 길이: %d.',
    'api_key_fix' => 'API 키는 최소 32자 이상이어야 합니다. 전체 키를 붙여넣으세요.',
    'activation_failed' => '⚠️ 활성화 실패',
    'settings_saved_inactive' => '💡 참고: 설정이 저장되었지만 활성화 실패로 게이트웨이가 비활성 상태입니다. 위의 문제를 해결하고 다시 저장하세요.',
    'api_connection_error' => '⚠️ API 연결 오류',
    'api_connection_error_desc' => '결제 게이트웨이 서버에 연결할 수 없습니다.',
    'details' => '세부 정보',
    'fix' => '해결 방법',
    'api_connection_fix' => '네트워크 연결을 확인하고 API 서버 URL을 확인하거나 지원팀에 문의하세요.',
    'settings_saved_unverified' => '💡 참고: 설정이 저장되었지만 자격 증명이 확인될 때까지 게이트웨이가 작동하지 않을 수 있습니다.',
    
    // Activation/Deactivation
    'gateway_activated' => 'PonponPay 게이트웨이가 활성화되었습니다. 주문은 백엔드 API를 통해 기록됩니다.',
    'gateway_deactivated' => 'PonponPay 게이트웨이가 비활성화되었습니다.',
    
    // Network names
    'network_tron' => 'Tron (TRC20)',
    'network_ethereum' => 'Ethereum (ERC20)',
    'network_polygon' => 'Polygon (MATIC)',
    'network_solana' => 'Solana (SOL)',
    
    // Callback messages
    'gateway_not_configured' => '게이트웨이가 구성되지 않았습니다',
    'invalid_json_data' => '잘못된 JSON 데이터',
    'invalid_signature' => '잘못된 서명',
    'missing_order_number' => '주문 번호 누락',
    'invoice_not_found' => '청구서를 찾을 수 없습니다',
    'signature_verification_failed' => '콜백 서명 확인 실패',
    'invoice_already_paid_ignore' => '청구서가 이미 결제되었습니다. 중복 콜백 무시',
    'payment_success' => '결제 성공',
    'payment_processing_failed' => '결제 처리 실패',
    'payment_failed_or_expired' => '결제 실패 또는 만료',
    'unknown_payment_status' => '알 수 없는 결제 상태',
    'missing_invoice_id' => '청구서 ID 누락',
    'invoice_not_exist' => '청구서가 존재하지 않습니다',
    'order_number_not_exist' => '주문 번호가 존재하지 않습니다',
    'network_request_failed' => '네트워크 요청 실패',
    'invalid_response_data' => '잘못된 응답 데이터',
    
    // Status texts
    'status_pending' => '결제 대기 중',
    'status_paid' => '결제 성공',
    'status_failed' => '결제 실패',
    'status_expired' => '만료됨',
    'status_unknown' => '알 수 없는 상태',
    
    // Hooks
    'order_paid_log' => 'PonponPay: 주문 #%d 결제 성공',
    'order_cancelled_log' => 'PonponPay: 주문 #%d 취소됨',
    'check_failed' => '확인 실패: ',
];
