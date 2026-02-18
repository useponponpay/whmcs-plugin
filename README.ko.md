# PonponPay WHMCS 결제 게이트웨이 플러그인

[PonponPay](https://ponponpay.com)를 통해 WHMCS 결제 시스템에서 암호화폐 결제(USDT, USDC 등)를 받으세요.

지원 네트워크: **Tron (TRC20)** · **Ethereum (ERC20)** · **BSC (BEP20)** · **Polygon** · **Solana**

---

## 사전 요구사항

> **⚠️ 이 플러그인을 설치하기 전에 [ponponpay.com](https://ponponpay.com)에서 다음 단계를 완료하세요:**

1. **계정 등록** — [https://ponponpay.com](https://ponponpay.com)에 방문하여 가입
2. **지갑 주소 추가** — **지갑 관리**에서 수신 지갑을 최소 1개 추가 (예: TRC20 USDT 주소)
3. **지원 통화 활성화** — 지갑에서 수신할 암호화폐 (USDT, USDC 등) 선택
4. **API Key 발급** — **API 키** 페이지에서 WHMCS 연동용 API Key 생성

이 단계를 완료하지 않으면 플러그인에 **"사용 가능한 결제 수단이 없습니다"** 오류가 표시됩니다.

---

## 설치

다음 파일을 WHMCS 루트 디렉토리에 복사하세요:

```
ponponpay-plugin/                        →  YOUR_WHMCS_ROOT/
├── includes/hooks/ponponpay_config.php  →  includes/hooks/ponponpay_config.php
├── modules/gateways/ponponpay.php       →  modules/gateways/ponponpay.php
├── modules/gateways/callback/ponponpay.php → modules/gateways/callback/ponponpay.php
└── modules/gateways/ponponpay/          →  modules/gateways/ponponpay/
```

---

## 설정

1. **WHMCS 관리자 패널**에 로그인
2. **설정 → 결제 → 결제 게이트웨이**로 이동
3. **PonponPay**를 찾아 **활성화** 클릭
4. [ponponpay.com](https://ponponpay.com)에서 발급받은 **API Key** 입력
5. **변경사항 저장** 클릭

---

## 결제 흐름

```
고객 주문 → WHMCS 청구서 생성
    → 청구서 페이지로 자동 리다이렉트
    → 고객이 네트워크 및 통화 선택 (예: Tron - USDT)
    → 플러그인이 PonponPay API로 결제 주문 생성
    → 고객이 암호화폐 결제 완료
    → PonponPay 콜백 전송 → WHMCS 청구서를 결제 완료로 표시
```

---

## 문제 해결

| 문제 | 해결 방법 |
|------|-----------|
| "사용 가능한 결제 수단이 없습니다" | [ponponpay.com](https://ponponpay.com)에서 지갑 추가 및 통화 활성화 확인 |
| 결제 콜백 미수신 | WHMCS 서버가 공개 접근 가능하고 콜백 URL이 올바른지 확인 |
| API Key 검증 실패 | API Key가 정확하고 만료되지 않았는지 확인 |

---

## 링크

- **PonponPay 콘솔**: [https://ponponpay.com](https://ponponpay.com)
- **문서**: [https://docs.ponponpay.com](https://docs.ponponpay.com)
